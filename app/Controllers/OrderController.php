<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\CartModel;
use App\Models\ProductModel;

class OrderController extends ResourceController
{
    use ResponseTrait;

    protected OrderModel $orderModel;
    protected OrderItemModel $orderItemModel;
    protected CartModel $cartModel;
    protected ProductModel $productModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->cartModel = new CartModel();
        $this->productModel = new ProductModel();
    }

    /**
     * POST /checkout - Proses checkout dan pembuatan transaksi
     */
    public function checkout()
    {
        try {
            // Debug: log request headers dan user info
            $request = service('request');
            log_message('info', 'Checkout request headers: ' . json_encode($request->getHeaders()));
            log_message('info', 'User from request: ' . json_encode($request->user ?? 'null'));
            
            $userId = $this->getUserId();
            
            log_message('info', 'User ID from getUserId(): ' . ($userId ?? 'null'));
            
            if (!$userId) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Unauthorized - User ID not found'
                ], 401);
            }

            helper(['form']);
            
            $rules = [
                'shipping_address' => 'required|max_length[500]',
                'phone'            => 'required|max_length[20]',
                'notes'            => 'permit_empty|max_length[500]'
            ];
            
            if (!$this->validate($rules)) {
                return $this->fail($this->validator->getErrors(), 400);
            }

            // Get cart items
            $cartItems = $this->cartModel->getCartWithProducts($userId);
            
            if (empty($cartItems)) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Cart is empty'
                ], 400);
            }

            // Calculate total
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }

            // Create order with custom hash ID
            $customOrderId = $this->orderModel->generateOrderId();
            $orderData = [
                'id'               => $customOrderId,  // Custom hash ID
                'user_id'          => $userId,
                'order_number'     => $this->orderModel->generateOrderNumber(),
                'total_amount'     => $totalAmount,
                'status'           => 'pending',
                'shipping_address' => $this->request->getVar('shipping_address'),
                'phone'            => $this->request->getVar('phone'),
                'notes'            => $this->request->getVar('notes')
            ];

            $orderId = $this->orderModel->insert($orderData, false); // false = don't use auto increment
            
            if (!$orderId) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Failed to create order'
                ], 400);
            }

            // Use the custom order ID for further operations
            $finalOrderId = $customOrderId;

            // Create order items
            foreach ($cartItems as $item) {
                $orderItemData = [
                    'order_id'   => $finalOrderId,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                    'subtotal'   => $item['price'] * $item['quantity']
                ];
                
                // Debug: Log the order item data being inserted
                log_message('info', 'Inserting order item: ' . json_encode($orderItemData));
                
                $insertResult = $this->orderItemModel->insert($orderItemData);
                
                // Debug: Log any errors
                if (!$insertResult) {
                    $errors = $this->orderItemModel->errors();
                    log_message('error', 'Order item insert failed: ' . json_encode($errors));
                }
            }

            // Clear cart after successful checkout
            $this->cartModel->clearCart($userId);

            // Get created order with details
            $order = $this->orderModel->find($finalOrderId);
            $orderItems = $this->orderItemModel->getOrderItemsWithProducts($finalOrderId);

            return $this->respondCreated([
                'status' => 201,
                'message' => 'Order created successfully',
                'data' => [
                    'order' => $order,
                    'items' => $orderItems
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error during checkout: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * GET /orders - History transaksi user
     */
    public function index()
    {
        try {
            $userId = $this->getUserId();
            $userRole = $this->getUserRole();
            
            if (!$userId) {
                return $this->respond([
                    'status' => 401,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Admin can see all orders, user can only see their own
            if ($userRole === 'admin') {
                $orders = $this->orderModel->getOrdersWithUser();
            } else {
                $orders = $this->orderModel->getOrdersWithUser($userId);
            }

            // Get order items for each order
            foreach ($orders as &$order) {
                $order['items'] = $this->orderItemModel->getOrderItemsWithProducts($order['id']);
            }

            return $this->respond([
                'status' => 200,
                'message' => 'Orders retrieved successfully',
                'data' => $orders
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error getting orders: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * GET /orders/{id} - Get specific order details
     */
    public function show($id = null)
    {
        try {
            $userId = $this->getUserId();
            $userRole = $this->getUserRole();
            
            if (!$userId || !$id) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Invalid request'
                ], 400);
            }

            $order = $this->orderModel->find($id);
            
            if (!$order) {
                return $this->respond([
                    'status' => 404,
                    'message' => 'Order not found'
                ], 404);
            }

            // User can only see their own orders, admin can see all
            if ($userRole !== 'admin' && $order['user_id'] != $userId) {
                return $this->respond([
                    'status' => 403,
                    'message' => 'Access denied'
                ], 403);
            }

            $orderItems = $this->orderItemModel->getOrderItemsWithProducts($id);
            $order['items'] = $orderItems;

            return $this->respond([
                'status' => 200,
                'message' => 'Order retrieved successfully',
                'data' => $order
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error getting order: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * PATCH /orders/{id} - Update status pesanan (Admin only)
     */
    public function updateStatus($id = null)
    {
        try {
            $userRole = $this->getUserRole();
            
            if ($userRole !== 'admin') {
                return $this->respond([
                    'status' => 403,
                    'message' => 'Access denied. Admin role required'
                ], 403);
            }

            if (!$id) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Order ID is required'
                ], 400);
            }

            helper(['form']);
            
            $rules = [
                'status' => 'required|in_list[pending,confirmed,processing,shipped,delivered,cancelled]'
            ];
            
            if (!$this->validate($rules)) {
                return $this->fail($this->validator->getErrors(), 400);
            }

            $order = $this->orderModel->find($id);
            
            if (!$order) {
                return $this->respond([
                    'status' => 404,
                    'message' => 'Order not found'
                ], 404);
            }

            $newStatus = $this->request->getVar('status');
            $updated = $this->orderModel->update($id, ['status' => $newStatus]);

            if (!$updated) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Failed to update order status'
                ], 400);
            }

            $updatedOrder = $this->orderModel->find($id);
            
            return $this->respond([
                'status' => 200,
                'message' => 'Order status updated successfully',
                'data' => $updatedOrder
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error updating order status: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * GET /orders/stats - Get order statistics (Admin only)
     */
    public function getStats()
    {
        try {
            $userRole = $this->getUserRole();
            
            if ($userRole !== 'admin') {
                return $this->respond([
                    'status' => 403,
                    'message' => 'Access denied. Admin role required'
                ], 403);
            }

            $stats = $this->orderModel->getOrderStats();

            return $this->respond([
                'status' => 200,
                'message' => 'Order statistics retrieved successfully',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error getting order stats: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Helper method to get user ID from JWT token
     */
    private function getUserId()
    {
        $request = service('request');
        return $request->user->id ?? null;
    }

    /**
     * Helper method to get user role from JWT token
     */
    private function getUserRole()
    {
        $request = service('request');
        return $request->user->role ?? null;
    }
}

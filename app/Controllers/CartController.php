<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\CartModel;
use App\Models\ProductModel;

class CartController extends ResourceController
{
    use ResponseTrait;

    protected CartModel $cartModel;
    protected ProductModel $productModel;

    public function __construct()
    {
        $this->cartModel = new CartModel();
        $this->productModel = new ProductModel();
    }

    /**
     * GET /cart - Melihat isi keranjang belanja
     */
    public function index()
    {
        try {
            // Get user ID from JWT token (set by Auth filter)
            $userId = $this->getUserId();
            
            if (!$userId) {
                return $this->respond([
                    'status' => 401,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $cartItems = $this->cartModel->getCartWithProducts($userId);
            $cartTotal = $this->cartModel->getCartTotal($userId);

            return $this->respond([
                'status' => 200,
                'message' => 'Cart retrieved successfully',
                'data' => [
                    'items' => $cartItems,
                    'total' => $cartTotal,
                    'item_count' => count($cartItems)
                ]
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error getting cart: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * POST /cart - Add item to cart
     */
    public function create()
    {
        try {
            $userId = $this->getUserId();
            
            log_message('debug', 'Cart create - User ID: ' . $userId);
            
            if (!$userId) {
                return $this->respond([
                    'status' => 401,
                    'message' => 'Unauthorized'
                ], 401);
            }

            helper(['form']);
            
            $rules = [
                'product_id' => 'required|integer',
                'quantity'   => 'required|integer|greater_than[0]'
            ];
            
            if (!$this->validate($rules)) {
                return $this->fail($this->validator->getErrors(), 400);
            }

            $productId = $this->request->getVar('product_id');
            $quantity = $this->request->getVar('quantity');
            
            log_message('debug', 'Cart create - Product ID: ' . $productId . ', Quantity: ' . $quantity);

            // Check if product exists
            $product = $this->productModel->find($productId);
            if (!$product) {
                return $this->respond([
                    'status' => 404,
                    'message' => 'Product not found'
                ], 404);
            }

            log_message('debug', 'Cart create - Product found: ' . json_encode($product));

            // Check if item already in cart
            $existingItem = $this->cartModel->where([
                'user_id' => $userId,
                'product_id' => $productId
            ])->first();

            if ($existingItem) {
                // Update quantity
                $newQuantity = $existingItem['quantity'] + $quantity;
                $updated = $this->cartModel->update($existingItem['id'], [
                    'quantity' => $newQuantity
                ]);
                
                if (!$updated) {
                    return $this->respond([
                        'status' => 400,
                        'message' => 'Failed to update cart item'
                    ], 400);
                }
                
                $message = 'Cart item updated successfully';
            } else {
                // Add new item
                $data = [
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $product['price']
                ];

                log_message('debug', 'Cart create - Insert data: ' . json_encode($data));

                $cartItemId = $this->cartModel->insert($data);
                
                if (!$cartItemId) {
                    $errors = $this->cartModel->errors();
                    log_message('error', 'Cart insert failed: ' . json_encode($errors));
                    return $this->respond([
                        'status' => 400,
                        'message' => 'Failed to add item to cart',
                        'errors' => $errors
                    ], 400);
                }
                
                $message = 'Item added to cart successfully';
            }

            // Get updated cart - simple version without join for now
            $cartItems = $this->cartModel->where('user_id', $userId)->findAll();
            $cartTotal = $this->cartModel->getCartTotal($userId);

            return $this->respond([
                'status' => 200,
                'message' => $message,
                'data' => [
                    'items' => $cartItems,
                    'total' => $cartTotal,
                    'item_count' => count($cartItems)
                ]
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error adding to cart: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /cart/{id} - Update cart item quantity
     */
    public function update($id = null)
    {
        try {
            $userId = $this->getUserId();
            
            if (!$userId || !$id) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Invalid request'
                ], 400);
            }

            $rules = [
                'quantity' => 'required|integer|greater_than[0]'
            ];
            
            if (!$this->validate($rules)) {
                return $this->fail($this->validator->getErrors(), 400);
            }

            $cartItem = $this->cartModel->where([
                'id' => $id,
                'user_id' => $userId
            ])->first();

            if (!$cartItem) {
                return $this->respond([
                    'status' => 404,
                    'message' => 'Cart item not found'
                ], 404);
            }

            $quantity = $this->request->getVar('quantity');
            $updated = $this->cartModel->update($id, ['quantity' => $quantity]);

            if (!$updated) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Failed to update cart item'
                ], 400);
            }

            // Get updated cart
            $cartItems = $this->cartModel->getCartWithProducts($userId);
            $cartTotal = $this->cartModel->getCartTotal($userId);

            return $this->respond([
                'status' => 200,
                'message' => 'Cart item updated successfully',
                'data' => [
                    'items' => $cartItems,
                    'total' => $cartTotal,
                    'item_count' => count($cartItems)
                ]
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error updating cart: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * DELETE /cart/{id} - Remove item from cart
     */
    public function delete($id = null)
    {
        try {
            $userId = $this->getUserId();
            
            if (!$userId || !$id) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Invalid request'
                ], 400);
            }

            $cartItem = $this->cartModel->where([
                'id' => $id,
                'user_id' => $userId
            ])->first();

            if (!$cartItem) {
                return $this->respond([
                    'status' => 404,
                    'message' => 'Cart item not found'
                ], 404);
            }

            $deleted = $this->cartModel->delete($id);

            if (!$deleted) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Failed to remove cart item'
                ], 400);
            }

            // Get updated cart
            $cartItems = $this->cartModel->getCartWithProducts($userId);
            $cartTotal = $this->cartModel->getCartTotal($userId);

            return $this->respond([
                'status' => 200,
                'message' => 'Cart item removed successfully',
                'data' => [
                    'items' => $cartItems,
                    'total' => $cartTotal,
                    'item_count' => count($cartItems)
                ]
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error removing from cart: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * DELETE /cart/clear - Clear all items from cart
     */
    public function clear()
    {
        try {
            $userId = $this->getUserId();
            
            if (!$userId) {
                return $this->respond([
                    'status' => 401,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $cleared = $this->cartModel->clearCart($userId);

            return $this->respond([
                'status' => 200,
                'message' => 'Cart cleared successfully',
                'data' => [
                    'items' => [],
                    'total' => 0,
                    'item_count' => 0
                ]
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error clearing cart: ' . $e->getMessage());
            
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
        // This will be set by Auth filter from JWT token
        $request = service('request');
        return $request->user->id ?? null;
    }
}

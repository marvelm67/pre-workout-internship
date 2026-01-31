<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;  // Custom ID, bukan auto increment
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'order_number',
        'total_amount',
        'status',
        'shipping_address',
        'phone',
        'notes'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'user_id'          => 'required|integer',
        'order_number'     => 'required|max_length[50]|is_unique[orders.order_number]',
        'total_amount'     => 'required|decimal',
        'status'           => 'required|in_list[pending,confirmed,processing,shipped,delivered,cancelled]',
        'shipping_address' => 'required|max_length[500]',
        'phone'            => 'required|max_length[20]'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID is required',
            'integer'  => 'User ID must be an integer'
        ],
        'order_number' => [
            'required'   => 'Order number is required',
            'max_length' => 'Order number cannot exceed 50 characters',
            'is_unique'  => 'Order number already exists'
        ],
        'total_amount' => [
            'required' => 'Total amount is required',
            'decimal'  => 'Total amount must be a valid decimal number'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list'  => 'Status must be one of: pending, confirmed, processing, shipped, delivered, cancelled'
        ],
        'shipping_address' => [
            'required'   => 'Shipping address is required',
            'max_length' => 'Shipping address cannot exceed 500 characters'
        ],
        'phone' => [
            'required'   => 'Phone number is required',
            'max_length' => 'Phone number cannot exceed 20 characters'
        ]
    ];

    /**
     * Get orders for specific user with user details
     */
    public function getOrdersWithUser($userId = null)
    {
        $query = $this->select('orders.*, users.username, users.email')
                     ->join('users', 'users.id = orders.user_id');
        
        if ($userId) {
            $query->where('orders.user_id', $userId);
        }
        
        return $query->orderBy('orders.created_at', 'DESC')->findAll();
    }

    /**
     * Generate unique order number with hash
     */
    public function generateOrderNumber()
    {
        do {
            $timestamp = time();
            $randomString = bin2hex(random_bytes(6)); // 12 char hex
            $hash = substr(hash('sha256', $timestamp . $randomString . uniqid()), 0, 8);
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper($hash . $randomString);
        } while ($this->where('order_number', $orderNumber)->first());
        
        return $orderNumber;
    }
    
    /**
     * Generate unique order ID (primary key replacement)
     */
    public function generateOrderId()
    {
        do {
            $timestamp = time();
            $randomBytes = random_bytes(8);
            $hash = hash('sha256', $timestamp . $randomBytes . uniqid());
            $orderId = substr(strtoupper($hash), 0, 12); // 12 karakter
            
            // Format: AB12CD34EF56 (kombinasi huruf dan angka)
            $orderId = preg_replace('/[^A-Z0-9]/', '', $orderId);
            
            if (strlen($orderId) < 12) {
                $orderId = str_pad($orderId, 12, '0');
            }
            
            $orderId = substr($orderId, 0, 12);
        } while ($this->where('id', $orderId)->first());
        
        return $orderId;
    }

    /**
     * Get order statistics
     */
    public function getOrderStats()
    {
        $totalOrders = $this->countAll();
        $pendingOrders = $this->where('status', 'pending')->countAllResults(false);
        $completedOrders = $this->where('status', 'delivered')->countAllResults(false);
        $cancelledOrders = $this->where('status', 'cancelled')->countAllResults(false);
        
        $totalRevenue = $this->selectSum('total_amount', 'revenue')
                            ->where('status !=', 'cancelled')
                            ->first();
        
        return [
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'completed_orders' => $completedOrders,
            'cancelled_orders' => $cancelledOrders,
            'total_revenue' => $totalRevenue['revenue'] ?? 0
        ];
    }
}
<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class OrderItemModel extends Model
{
    protected $table = 'order_items';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'subtotal'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'order_id'   => 'required|alpha_numeric',
        'product_id' => 'required|integer',
        'quantity'   => 'required|integer|greater_than[0]',
        'price'      => 'required|decimal',
        'subtotal'   => 'required|decimal'
    ];

    protected $validationMessages = [
        'order_id' => [
            'required' => 'Order ID is required',
            'alpha_numeric'  => 'Order ID must contain only letters and numbers'
        ],
        'product_id' => [
            'required' => 'Product ID is required',
            'integer'  => 'Product ID must be an integer'
        ],
        'quantity' => [
            'required'     => 'Quantity is required',
            'integer'      => 'Quantity must be an integer',
            'greater_than' => 'Quantity must be greater than 0'
        ],
        'price' => [
            'required' => 'Price is required',
            'decimal'  => 'Price must be a valid decimal number'
        ],
        'subtotal' => [
            'required' => 'Subtotal is required',
            'decimal'  => 'Subtotal must be a valid decimal number'
        ]
    ];

    /**
     * Get order items with product details
     */
    public function getOrderItemsWithProducts($orderId)
    {
        return $this->select('order_items.*, products.name as product_name, products.description, products.image_url')
                   ->join('products', 'products.id = order_items.product_id')
                   ->where('order_items.order_id', $orderId)
                   ->findAll();
    }
}

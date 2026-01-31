<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class CartModel extends Model
{
    protected $table = 'carts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'product_id',
        'quantity',
        'price'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'user_id'    => 'required|integer',
        'product_id' => 'required|integer',
        'quantity'   => 'required|integer|greater_than[0]',
        'price'      => 'required|decimal'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID is required',
            'integer'  => 'User ID must be an integer'
        ],
        'product_id' => [
            'required' => 'Product ID is required',
            'integer'  => 'Product ID must be an integer'
        ],
        'quantity' => [
            'required'      => 'Quantity is required',
            'integer'       => 'Quantity must be an integer',
            'greater_than'  => 'Quantity must be greater than 0'
        ],
        'price' => [
            'required' => 'Price is required',
            'decimal'  => 'Price must be a valid decimal number'
        ]
    ];

    /**
     * Get cart items for specific user with product details
     */
    public function getCartWithProducts($userId)
    {
        return $this->select('carts.*, products.name as product_name, products.description, products.image_url')
                   ->join('products', 'products.id = carts.product_id')
                   ->where('carts.user_id', $userId)
                   ->findAll();
    }

    /**
     * Get total cart value for user
     */
    public function getCartTotal($userId)
    {
        $result = $this->select('SUM(carts.price * carts.quantity) as total')
                      ->where('user_id', $userId)
                      ->first();
        
        return $result['total'] ?? 0;
    }

    /**
     * Clear cart for user
     */
    public function clearCart($userId)
    {
        return $this->where('user_id', $userId)->delete();
    }
}

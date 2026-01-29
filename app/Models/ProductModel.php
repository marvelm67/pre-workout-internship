<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Product Model with validation rules
 */
class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'category_id', 'name', 'description', 'price', 'stock', 'image_url'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation Rules
    protected $validationRules = [
        'category_id' => [
            'label' => 'Category',
            'rules' => 'required|integer|greater_than[0]|is_not_unique[categories.id]',
        ],
        'name' => [
            'label' => 'Product Name',
            'rules' => 'required|max_length[255]',
        ],
        'description' => [
            'label' => 'Description',
            'rules' => 'permit_empty|string',
        ],
        'price' => [
            'label' => 'Price',
            'rules' => 'required|decimal|greater_than_equal_to[0]',
        ],
        'stock' => [
            'label' => 'Stock',
            'rules' => 'required|integer|greater_than_equal_to[0]',
        ],
        'image_url' => [
            'label' => 'Image URL',
            'rules' => 'permit_empty|max_length[500]|valid_url',
        ],
    ];

    protected $validationMessages = [
        'category_id' => [
            'required' => 'Category is required',
            'integer' => 'Category must be a valid number',
            'greater_than' => 'Category must be greater than 0',
            'is_not_unique' => 'Selected category does not exist',
        ],
        'name' => [
            'required' => 'Product name is required',
            'max_length' => 'Product name cannot exceed 255 characters',
        ],
        'price' => [
            'required' => 'Price is required',
            'decimal' => 'Price must be a valid decimal number',
            'greater_than_equal_to' => 'Price must be 0 or greater',
        ],
        'stock' => [
            'required' => 'Stock is required',
            'integer' => 'Stock must be a valid integer',
            'greater_than_equal_to' => 'Stock must be 0 or greater',
        ],
        'image_url' => [
            'max_length' => 'Image URL cannot exceed 500 characters',
            'valid_url' => 'Image URL must be a valid URL',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get products with category information using join
     */
    public function getProductsWithCategory(?int $limit = null, int $offset = 0): array
    {
        $builder = $this->builder();
        $builder->select('products.*, categories.name as category_name')
                ->join('categories', 'categories.id = products.category_id', 'left')
                ->orderBy('products.id', 'DESC');
        
        if ($limit !== null) {
            $builder->limit($limit, $offset);
        }
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get single product with category information
     */
    public function getProductWithCategory(int $id): ?array
    {
        $builder = $this->builder();
        $result = $builder->select('products.*, categories.name as category_name')
                          ->join('categories', 'categories.id = products.category_id', 'left')
                          ->where('products.id', $id)
                          ->get()
                          ->getRowArray();
        
        return $result ?: null;
    }

    /**
     * Update product stock
     */
    public function updateStock(int $id, int $stock): bool
    {
        return $this->update($id, ['stock' => $stock]);
    }

    /**
     * Update product price
     */
    public function updatePrice(int $id, float $price): bool
    {
        return $this->update($id, ['price' => $price]);
    }
}

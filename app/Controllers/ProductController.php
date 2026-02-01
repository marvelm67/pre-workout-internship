<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\CategoryModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Product Controller - handles CRUD operations for products
 */
class ProductController extends BaseController
{
    use ResponseTrait;

    protected ProductModel $productModel;
    protected CategoryModel $categoryModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
    }

    /**
     * Get list of products with category information (join)
     */
    public function index(): ResponseInterface
    {
        try {
            $limit = $this->request->getGet('limit');
            $offset = (int) ($this->request->getGet('offset') ?? 0);
            
            $products = $this->productModel->getProductsWithCategory(
                $limit ? (int) $limit : null, 
                $offset
            );

            return $this->respond([
                'status' => 200,
                'message' => 'Products retrieved successfully',
                'data' => $products
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error getting products: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error',
                'data' => null
            ], 500);
        }
    }

    /**
     * Get single product details with category information
     */
    public function show(?string $id = null): ResponseInterface
    {
        try {
            if (!$id || !is_numeric($id)) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Invalid product ID',
                    'data' => null
                ], 400);
            }

            $product = $this->productModel->getProductWithCategory((int) $id);

            if (!$product) {
                return $this->respond([
                    'status' => 404,
                    'message' => 'Product not found',
                    'data' => null
                ], 404);
            }

            return $this->respond([
                'status' => 200,
                'message' => 'Product retrieved successfully',
                'data' => $product
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error getting product: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error',
                'data' => null
            ], 500);
        }
    }

    /**
     * Create new product (Admin only)
     */
    public function create(): ResponseInterface
    {
        try {
            if (!$this->isAdmin()) {
                return $this->respond(['status' => 403, 'message' => 'Access denied'], 403);
            }

            $data = $this->request->getJSON(true) ?? $this->request->getPost();

            if (empty($data)) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'No data provided',
                    'data' => null
                ], 400);
            }

            // Validate input data
            if (!$this->productModel->validate($data)) {
                return $this->respond([
                    'status' => 422,
                    'message' => 'Validation failed',
                    'data' => [
                        'errors' => $this->productModel->errors()
                    ]
                ], 422);
            }

            // Check if category exists
            $category = $this->categoryModel->find($data['category_id']);
            if (!$category) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Invalid category',
                    'data' => null
                ], 400);
            }

            $productId = $this->productModel->insert($data);

            if (!$productId) {
                return $this->respond([
                    'status' => 500,
                    'message' => 'Failed to create product',
                    'data' => null
                ], 500);
            }

            $newProduct = $this->productModel->getProductWithCategory($productId);

            return $this->respond([
                'status' => 201,
                'message' => 'Product created successfully',
                'data' => $newProduct
            ], 201);

        } catch (\Exception $e) {
            log_message('error', 'Error creating product: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error',
                'data' => null
            ], 500);
        }
    }

    /**
     * Update product (stock or price) - Admin only
     */
    public function update(?string $id = null): ResponseInterface
    {
        try {
            if (!$this->isAdmin()) {
                return $this->respond(['status' => 403, 'message' => 'Access denied'], 403);
            }

            if (!$id || !is_numeric($id)) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Invalid product ID',
                    'data' => null
                ], 400);
            }

            $productId = (int) $id;
            $existingProduct = $this->productModel->find($productId);

            if (!$existingProduct) {
                return $this->respond([
                    'status' => 404,
                    'message' => 'Product not found',
                    'data' => null
                ], 404);
            }

            $data = $this->request->getJSON(true) ?? $this->request->getPut();

            if (empty($data)) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'No data provided',
                    'data' => null
                ], 400);
            }

            // Allow partial updates (only stock and price for admin operations)
            $allowedFields = ['stock', 'price', 'name', 'description', 'category_id', 'image_url'];
            $updateData = array_intersect_key($data, array_flip($allowedFields));

            if (empty($updateData)) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'No valid fields to update',
                    'data' => null
                ], 400);
            }

            // Validate only the fields being updated
            $rules = [];
            foreach ($updateData as $field => $value) {
                if (isset($this->productModel->validationRules[$field])) {
                    $rules[$field] = $this->productModel->validationRules[$field];
                }
            }

            if (!empty($rules) && !$this->validate($rules, $updateData)) {
                return $this->respond([
                    'status' => 422,
                    'message' => 'Validation failed',
                    'data' => [
                        'errors' => $this->validator->getErrors()
                    ]
                ], 422);
            }

            $success = $this->productModel->update($productId, $updateData);

            if (!$success) {
                return $this->respond([
                    'status' => 500,
                    'message' => 'Failed to update product',
                    'data' => null
                ], 500);
            }

            $updatedProduct = $this->productModel->getProductWithCategory($productId);

            return $this->respond([
                'status' => 200,
                'message' => 'Product updated successfully',
                'data' => $updatedProduct
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error updating product: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error',
                'data' => null
            ], 500);
        }
    }

    /**
     * Delete product (Admin only)
     */
    public function delete(?string $id = null): ResponseInterface
    {
        try {
            if (!$this->isAdmin()) {
                return $this->respond(['status' => 403, 'message' => 'Access denied'], 403);
            }

            if (!$id || !is_numeric($id)) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Invalid product ID',
                    'data' => null
                ], 400);
            }

            $productId = (int) $id;
            $existingProduct = $this->productModel->find($productId);

            if (!$existingProduct) {
                return $this->respond([
                    'status' => 404,
                    'message' => 'Product not found',
                    'data' => null
                ], 404);
            }

            $success = $this->productModel->delete($productId);

            if (!$success) {
                return $this->respond([
                    'status' => 500,
                    'message' => 'Failed to delete product',
                    'data' => null
                ], 500);
            }

            return $this->respond([
                'status' => 200,
                'message' => 'Product deleted successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error deleting product: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error',
                'data' => null
            ], 500);
        }
    }

    /**
     * Helper method to check admin authentication
     */
    private function isAdmin(): bool
    {
        try {
            helper('jwt');
            $payload = validateJWTFromRequest($this->request);
            
            if ($payload && isset($payload->data->role)) {
                return $payload->data->role === 'admin';
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}

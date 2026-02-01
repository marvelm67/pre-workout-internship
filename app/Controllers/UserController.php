<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;

class UserController extends ResourceController
{
    use ResponseTrait;

    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Get all users (admin only)
     */
    public function index()
    {
        try {
            $users = $this->userModel->findAll();
            
            // Remove password from response
            foreach ($users as &$user) {
                unset($user['password']);
            }

            return $this->respond([
                'status' => 200,
                'message' => 'Users retrieved successfully',
                'data' => $users
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error getting users: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error',
                'data' => null
            ], 500);
        }
    }

    /**
     * Delete user (admin only)
     */
    public function delete($id = null)
    {
        try {
            $user = $this->userModel->find($id);
            
            if (!$user) {
                return $this->respond([
                    'status' => 404,
                    'message' => 'User not found',
                    'data' => null
                ], 404);
            }

            // Cegah penghapusan admin utama
            if ($user['role'] === 'admin' && $user['email'] === 'admin@opticmodern.com') {
                return $this->respond([
                    'status' => 403,
                    'message' => 'Cannot delete main admin user',
                    'data' => null
                ], 403);
            }

            $this->userModel->delete($id);

            return $this->respond([
                'status' => 200,
                'message' => 'User deleted successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error deleting user: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error',
                'data' => null
            ], 500);
        }
    }
}
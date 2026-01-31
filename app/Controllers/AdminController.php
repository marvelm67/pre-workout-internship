<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Libraries\Hash;

class AdminController extends ResourceController
{
    use ResponseTrait;

    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Get all users (Admin only)
     */
    public function getUsers()
    {
        try {
            $users = $this->userModel->select('id, username, email, role, created_at, updated_at')->findAll();
            
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
     * Get user by ID (Admin only)
     */
    public function getUser($id = null)
    {
        try {
            if (!$id) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'User ID is required'
                ], 400);
            }

            $user = $this->userModel->select('id, username, email, role, created_at, updated_at')->find($id);
            
            if (!$user) {
                return $this->respond([
                    'status' => 404,
                    'message' => 'User not found'
                ], 404);
            }

            return $this->respond([
                'status' => 200,
                'message' => 'User retrieved successfully',
                'data' => $user
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error getting user: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update user (Admin only)
     */
    public function updateUser($id = null)
    {
        try {
            if (!$id) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'User ID is required'
                ], 400);
            }

            $user = $this->userModel->find($id);
            if (!$user) {
                return $this->respond([
                    'status' => 404,
                    'message' => 'User not found'
                ], 404);
            }

            helper(['form']);
            
            $rules = [
                'username' => "permit_empty|min_length[3]|max_length[100]|is_unique[users.username,id,{$id}]",
                'email'    => "permit_empty|valid_email|is_unique[users.email,id,{$id}]",
                'password' => 'permit_empty|min_length[6]',
                'role'     => 'permit_empty|in_list[user,admin]'
            ];
            
            if (!$this->validate($rules)) {
                return $this->fail($this->validator->getErrors(), 400);
            }
            
            $data = [];
            
            if ($this->request->getVar('username')) {
                $data['username'] = $this->request->getVar('username');
            }
            
            if ($this->request->getVar('email')) {
                $data['email'] = $this->request->getVar('email');
            }
            
            if ($this->request->getVar('password')) {
                $data['password'] = Hash::make($this->request->getVar('password'));
            }
            
            if ($this->request->getVar('role')) {
                $data['role'] = $this->request->getVar('role');
            }
            
            if (empty($data)) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'No data provided for update'
                ], 400);
            }
            
            $updated = $this->userModel->update($id, $data);
            
            if (!$updated) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Failed to update user'
                ], 400);
            }
            
            $updatedUser = $this->userModel->select('id, username, email, role, updated_at')->find($id);
            
            return $this->respond([
                'status' => 200,
                'message' => 'User updated successfully',
                'data' => $updatedUser
            ], 200);
            
        } catch (\Exception $e) {
            log_message('error', 'Error updating user: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Delete user (Admin only)
     */
    public function deleteUser($id = null)
    {
        try {
            if (!$id) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'User ID is required'
                ], 400);
            }

            $user = $this->userModel->find($id);
            if (!$user) {
                return $this->respond([
                    'status' => 404,
                    'message' => 'User not found'
                ], 404);
            }

            $deleted = $this->userModel->delete($id);
            
            if (!$deleted) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Failed to delete user'
                ], 400);
            }
            
            return $this->respond([
                'status' => 200,
                'message' => 'User deleted successfully',
                'data' => [
                    'deleted_user_id' => $id,
                    'deleted_username' => $user['username']
                ]
            ], 200);
            
        } catch (\Exception $e) {
            log_message('error', 'Error deleting user: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get dashboard statistics (Admin only)
     */
    public function getDashboardStats()
    {
        try {
            $totalUsers = $this->userModel->countAll();
            $totalAdmins = $this->userModel->where('role', 'admin')->countAllResults();
            $totalRegularUsers = $this->userModel->where('role', 'user')->countAllResults();
            
            // Recent users (last 7 days)
            $recentUsers = $this->userModel
                ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))
                ->countAllResults();
            
            return $this->respond([
                'status' => 200,
                'message' => 'Dashboard statistics retrieved successfully',
                'data' => [
                    'total_users' => $totalUsers,
                    'total_admins' => $totalAdmins,
                    'total_regular_users' => $totalRegularUsers,
                    'recent_users_7_days' => $recentUsers
                ]
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Error getting dashboard stats: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
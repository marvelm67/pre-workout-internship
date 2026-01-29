<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class AuthController extends ResourceController
{
    use ResponseTrait;

    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        // helper('jwt'); // Comment ini dulu untuk test
    }

    public function register()
    {
        try {
            $rules = [
                'username' => 'required|min_length[3]|max_length[100]|is_unique[users.username]',
                'email'    => 'required|valid_email|is_unique[users.email]',
                'password' => 'required|min_length[6]',
                'role'     => 'permit_empty|in_list[user,admin]'
            ];

            if (!$this->validate($rules)) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Validation failed',
                    'data' => $this->validator->getErrors()
                ], 400);
            }

            $data = [
                'username' => $this->request->getPost('username'),
                'email'    => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
                'role'     => $this->request->getPost('role') ?? 'user'
            ];

            $userId = $this->userModel->insert($data);

            if (!$userId) {
                return $this->respond([
                    'status' => 500,
                    'message' => 'Failed to create user',
                    'data' => null
                ], 500);
            }

            return $this->respond([
                'status' => 201,
                'message' => 'User created successfully',
                'data' => ['user_id' => $userId]
            ], 201);

        } catch (\Exception $e) {
            return $this->respond([
                'status' => 500,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
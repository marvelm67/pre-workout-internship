<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use Firebase\JWT\JWT;
use App\Libraries\Hash;

class AuthController extends ResourceController
{
    use ResponseTrait;

    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        // helper('jwt'); // Comment ini dulu untuk test
    }

    private function getJwtSecret(): string
    {
        return env('JWT_SECRET') ?: 'fallback-secret-key-change-this';
    }

        public function register()
    {
        try {
            helper(['form']);
            
            $rules = [
                'username'    => 'required|min_length[3]|max_length[100]|is_unique[users.username]',
                'email'       => 'required|valid_email|is_unique[users.email]', 
                'password'    => 'required|min_length[6]',
                'confpassword' => 'matches[password]',
                'role'        => 'permit_empty|in_list[user,admin]'
            ];
            
            if (!$this->validate($rules)) {
                return $this->fail($this->validator->getErrors(), 400);
            }
            
            $data = [
                'username' => $this->request->getVar('username'),
                'email'    => $this->request->getVar('email'), 
                'password' => Hash::make($this->request->getVar('password')),
                'role'     => $this->request->getVar('role') ?? 'user'
            ];
            
            $model = new UserModel();
            $registered = $model->save($data);
            
            if (!$registered) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Registration failed',
                    'data' => null
                ], 400);
            }
            
            return $this->respondCreated([
                'status' => 201,
                'message' => 'User registered successfully',
                'data' => [
                    'user_id' => $model->getInsertID(),
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'role' => $data['role']
                ]
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Registration error: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login()
    {
        try {
            helper(['form']);
            
            $rules = [
                'email'    => 'required|valid_email',
                'password' => 'required|min_length[6]'
            ];
            
            if (!$this->validate($rules)) {
                return $this->fail($this->validator->getErrors(), 400);
            }
            
            $email = $this->request->getVar('email');
            $password = $this->request->getVar('password');

            // Get user by email
            $user = $this->userModel->where('email', $email)->first();

            if (!$user) {
                return $this->respond([
                    'status' => 401,
                    'message' => 'Invalid email or password',
                    'data' => null
                ], 401);
            }

            // Verify password using Hash library
            if (!Hash::check($password, $user['password'])) {
                return $this->respond([
                    'status' => 401,
                    'message' => 'Invalid email or password',
                    'data' => null
                ], 401);
            }

            // Generate JWT token using JWT_SECRET from .env
            $secret_key = $this->getJwtSecret();
            $issuedat_claim = time();
            $expire_claim = $issuedat_claim + (int)(env('JWT_TIME_TO_LIVE') ?: 3600);

            $token_data = [
                "iss" => base_url(),
                "aud" => base_url(),
                "iat" => $issuedat_claim,
                "exp" => $expire_claim,
                "data" => [
                    "id" => $user['id'],
                    "username" => $user['username'],
                    "email" => $user['email'],
                    "role" => $user['role']
                ]
            ];

            $token = JWT::encode($token_data, $secret_key, 'HS256');

            return $this->respond([
                'status' => 200,
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ],
                    'expires_at' => date('Y-m-d H:i:s', $expire_claim),
                    'expires_in' => $expire_claim - $issuedat_claim
                ]
            ], 200);

        } catch (\Exception $e) {
            log_message('error', 'Login error: ' . $e->getMessage());
            
            return $this->respond([
                'status' => 500,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
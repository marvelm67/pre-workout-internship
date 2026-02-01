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

    public function login()
    {
        try {
            helper(['form']);
            
            $rules = [
                'email'    => 'required|valid_email',
                'password' => 'required|min_length[6]'
            ];
            
            if (!$this->validate($rules)) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ], 400);
            }
            
            $email = $this->request->getVar('email');
            $password = $this->request->getVar('password');
            
            // Cari user berdasarkan email
            $user = $this->userModel->where('email', $email)->first();
            
            if (!$user) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Email atau password tidak valid'
                ], 401);
            }
            
            // Verifikasi password
            if (!Hash::check($password, $user['password'])) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Email atau password tidak valid'
                ], 401);
            }
            
            // Generate JWT Token
            $payload = [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'username' => $user['username'],
                'role' => $user['role'],
                'iat' => time(),
                'exp' => time() + (24 * 60 * 60) // Token berlaku 24 jam
            ];
            
            $token = JWT::encode($payload, $this->getJwtSecret(), 'HS256');
            
            // Siapkan data user untuk response (tanpa password)
            $userData = [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'created_at' => $user['created_at'],
                'updated_at' => $user['updated_at']
            ];
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Login berhasil',
                'data' => [
                    'token' => $token,
                    'user' => $userData,
                    'role' => $user['role'],
                    'expires_in' => 24 * 60 * 60 // dalam detik
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
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
            $customId = $model->insert($data, true); // Will return custom ID
            
            if (!$customId) {
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
                    'user_id' => $customId,
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

    public function logout()
    {
        try {
            return $this->respond([
                'status' => 'success',
                'message' => 'Logout berhasil'
            ], 200);
            
        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

    public function me()
    {
        try {
            $token = $this->request->getHeaderLine('Authorization');
            $token = str_replace('Bearer ', '', $token);
            
            if (!$token) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'Token tidak ditemukan'
                ], 401);
            }
            
            $key = new \Firebase\JWT\Key($this->getJwtSecret(), 'HS256');
            $decoded = JWT::decode($token, $key);
            $userId = $decoded->user_id;
            
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                return $this->fail([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ], 404);
            }
            
            // Remove password dari response
            unset($user['password']);
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Data user berhasil diambil',
                'data' => $user
            ], 200);
            
        } catch (\Exception $e) {
            return $this->fail([
                'status' => 'error',
                'message' => 'Token tidak valid: ' . $e->getMessage()
            ], 401);
        }
    }
}
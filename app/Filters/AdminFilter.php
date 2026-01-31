<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Config\Services;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        try {
            $authHeader = $request->getHeaderLine('Authorization');
            
            if (!$authHeader) {
                $response = Services::response();
                return $response->setJSON([
                    'status' => 401,
                    'message' => 'Authorization header missing'
                ])->setStatusCode(401);
            }

            if (!str_starts_with($authHeader, 'Bearer ')) {
                $response = Services::response();
                return $response->setJSON([
                    'status' => 401,
                    'message' => 'Invalid token format'
                ])->setStatusCode(401);
            }

            $token = substr($authHeader, 7);
            $secret_key = env('JWT_SECRET') ?: 'fallback-secret-key-change-this';
            
            $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
            
            // Check if user is admin
            if (!isset($decoded->data->role) || $decoded->data->role !== 'admin') {
                $response = Services::response();
                return $response->setJSON([
                    'status' => 403,
                    'message' => 'Access denied. Admin role required'
                ])->setStatusCode(403);
            }

            // Store user data in request for later use
            $request->user = $decoded->data;
            
        } catch (\Exception $e) {
            $response = Services::response();
            return $response->setJSON([
                'status' => 401,
                'message' => 'Invalid or expired token',
                'error' => $e->getMessage()
            ])->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
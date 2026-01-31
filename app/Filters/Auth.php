<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        try {
            $authHeader = $request->getHeaderLine('Authorization');
            
            if (!$authHeader) {
                return Services::response()
                    ->setJSON([
                        'status' => 401,
                        'message' => 'Authorization header missing'
                    ])
                    ->setStatusCode(401);
            }

            if (!str_starts_with($authHeader, 'Bearer ')) {
                return Services::response()
                    ->setJSON([
                        'status' => 401,
                        'message' => 'Invalid token format'
                    ])
                    ->setStatusCode(401);
            }

            $token = substr($authHeader, 7);
            $secret_key = env('JWT_SECRET') ?: 'fallback-secret-key-change-this';
            
            $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
            
            // Store user data in request for later use
            $request->user = $decoded->data;
            
        } catch (\Exception $e) {
            return Services::response()
                ->setJSON([
                    'status' => 401,
                    'message' => 'Invalid or expired token',
                    'error' => $e->getMessage()
                ])
                ->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}


<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Me extends ResourceController
{
    use ResponseTrait;
    
    public function index()
    {
        $key = getenv('JWT_SECRET');
        $header = $this->request->getServer('HTTP_AUTHORIZATION');
        if (!$header) return $this->failUnauthorized('Token Required');
        $token = explode(' ', $header)[1];
        
        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            $response = [
                'id' => $decoded->data->id,
                'email' => $decoded->data->email
            ];
            return $this->respond($response);
        } catch (\Throwable $th) {
            return $this->fail('Invalid Token');
        }
    }
}

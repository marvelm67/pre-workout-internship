<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (!function_exists('getJWTFromRequest')) {
    function getJWTFromRequest($request)
    {
        $header = $request->getHeaderLine('Authorization');
        
        if (!empty($header)) {
            if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
}

if (!function_exists('getSignedJWTForUser')) {
    function getSignedJWTForUser($user)
    {
        $issuedAtTime = time();
        $tokenTimeToLive = getenv('JWT_TIME_TO_LIVE');
        $tokenExpiration = $issuedAtTime + $tokenTimeToLive;
        
        $payload = [
            'iss' => 'pre-workout-app',
            'aud' => 'pre-workout-users',
            'iat' => $issuedAtTime,
            'exp' => $tokenExpiration,
            'uid' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        
        return JWT::encode($payload, getenv('JWT_SECRET'), 'HS256');
    }
}

if (!function_exists('validateJWTFromRequest')) {
    function validateJWTFromRequest($request)
    {
        $token = getJWTFromRequest($request);
        
        if (is_null($token)) {
            return null;
        }
        
        try {
            return JWT::decode($token, new Key(getenv('JWT_SECRET'), 'HS256'));
        } catch (Exception $ex) {
            return null;
        }
    }
}
<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;  // Custom ID generation
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id', 'username', 'email', 'password', 'role'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation Rules
    protected $validationRules = [];

    protected $validationMessages = [];

    protected $skipValidation = true;

    /**
     * Generate custom user ID based on role
     */
    public function generateCustomId(string $role): string
    {
        $prefix = ($role === 'admin') ? 'A' : 'U';
        
        // Get the highest existing ID for this role
        $lastUser = $this->where('role', $role)
                        ->where('id LIKE', $prefix . '%')
                        ->orderBy('id', 'DESC')
                        ->first();
        
        if (!$lastUser) {
            // First user of this role
            return $prefix . '001';
        }
        
        // Extract number from last ID and increment
        $lastNumber = (int) substr($lastUser['id'], 1);
        $newNumber = $lastNumber + 1;
        
        return $prefix . sprintf('%03d', $newNumber);
    }

    /**
     * Override insert to generate custom ID
     */
    public function insert($data = null, bool $returnID = true)
    {
        if (is_array($data) && !isset($data['id'])) {
            $role = $data['role'] ?? 'user';
            $data['id'] = $this->generateCustomId($role);
        }
        
        $result = parent::insert($data, false);
        
        if ($returnID && $result) {
            return $data['id'];
        }
        
        return $result;
    }

}
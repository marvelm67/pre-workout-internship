<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class QuickSeeder extends Seeder
{
    public function run()
    {
        $now = Time::now();

        // 1. Insert Categories (Kategori Kacamata)
        $categories = [
            ['name' => 'Kacamata Hitam (Sunglasses)', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kacamata Anti Radiasi',        'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Frame Optik',                  'created_at' => $now, 'updated_at' => $now],
        ];
        
        $this->db->table('categories')->insertBatch($categories);

        // 2. Insert Products (Produk Kacamata)
        $products = [
            [
                'category_id' => 1,
                'name'        => 'Aviator Classic Gold',
                'description' => 'Kacamata hitam gaya klasik dengan perlindungan UV400',
                'price'       => 1250000,
                'stock'       => 15,
                'image_url'   => 'https://images.ray-ban.com/is/image/RayBan/8056597259835_shad_qt.png?impolicy=SEO_4x3',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'category_id' => 1,
                'name'        => 'Wayfarer Matte Black',
                'description' => 'Kacamata hitam ikonik dengan bahan frame asetat premium',
                'price'       => 950000,
                'stock'       => 20,
                'image_url'   => 'https://images.ray-ban.com/is/image/RayBan/8053672060553_shad_qt.png?impolicy=SEO_1x1',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'category_id' => 2,
                'name'        => 'Blue Light Shield Pro',
                'description' => 'Lensa khusus untuk melindungi mata dari paparan layar digital',
                'price'       => 450000,
                'stock'       => 50,
                'image_url'   => 'https://www.iplretail.com.au/assets/full/BRG250.png?20250121120848',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'category_id' => 3,
                'name'        => 'Titanium Round Frame',
                'description' => 'Frame kacamata bulat bahan titanium yang ringan dan kuat',
                'price'       => 850000,
                'stock'       => 12,
                'image_url'   => 'https://assets2.glasses.com/cdn-record-files-pi/afcac925-647a-4bdf-8e6f-ac850005386d/34ac3f30-9083-47bb-ac67-ac8501347a10/0RX8247V__1223__STD__shad__qt.png?impolicy=GL_parameters_transp_clone1440',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'category_id' => 2,
                'name'        => 'Cyberpunk Gaming Glasses',
                'description' => 'Kacamata anti radiasi dengan desain sporty untuk gamer',
                'price'       => 600000,
                'stock'       => 30,
                'image_url'   => 'https://cdn.shopify.com/s/files/1/0762/8760/7065/files/P6X_01.jpg?v=1765351083',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];
        
        $this->db->table('products')->insertBatch($products);
    }
}
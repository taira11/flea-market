<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seller1 = DB::table('users')->where('email', 'seller1@example.com')->first();
        $seller2 = DB::table('users')->where('email', 'seller2@example.com')->first();

        $products = [
            [
                'name' => '腕時計',
                'price' => 15000,
                'brand' => 'Rolax',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'image' => 'products/watch.jpg',
                'status' => 4,
                'seller_id' => $seller1->id,
                'category_ids' => [1, 5],
            ],
            [
                'name' => 'HDD',
                'price' => 5000,
                'brand' => '西芝',
                'description' => '高速で信頼性の高いハードディスク',
                'image' => 'products/hdd.jpg',
                'status' => 3,
                'seller_id' => $seller1->id,
                'category_ids' => [2],
            ],
            [
                'name' => '玉ねぎ3束',
                'price' => 300,
                'brand' => null,
                'description' => '新鮮な玉ねぎ3束のセット',
                'image' => 'products/onion.jpg',
                'status' => 2,
                'seller_id' => $seller1->id,
                'category_ids' => [10],
            ],
            [
                'name' => '革靴',
                'price' => 4000,
                'brand' => null,
                'description' => 'クラシックなデザインの革靴',
                'image' => 'products/shoes.jpg',
                'status' => 1,
                'seller_id' => $seller1->id,
                'category_ids' => [1, 5],
            ],
            [
                'name' => 'ノートPC',
                'price' => 45000,
                'brand' => null,
                'description' => '高性能なノートパソコン',
                'image' => 'products/laptop.jpg',
                'status' => 4,
                'seller_id' => $seller1->id,
                'category_ids' => [2],
            ],
            [
                'name' => 'マイク',
                'price' => 8000,
                'brand' => null,
                'description' => '高音質のレコーディング用マイク',
                'image' => 'products/mic.jpg',
                'status' => 3,
                'seller_id' => $seller2->id,
                'category_ids' => [2],
            ],
            [
                'name' => 'ショルダーバッグ',
                'price' => 3500,
                'brand' => null,
                'description' => 'おしゃれなショルダーバッグ',
                'image' => 'products/bag.jpg',
                'status' => 2,
                'seller_id' => $seller2->id,
                'category_ids' => [1, 4],
            ],
            [
                'name' => 'タンブラー',
                'price' => 500,
                'brand' => null,
                'description' => '使いやすいタンブラー',
                'image' => 'products/tumbler.jpg',
                'status' => 1,
                'seller_id' => $seller2->id,
                'category_ids' => [10],
            ],
            [
                'name' => 'コーヒーミル',
                'price' => 4000,
                'brand' => 'Starbacks',
                'description' => '手動のコーヒーミル',
                'image' => 'products/coffee_mill.jpg',
                'status' => 4,
                'seller_id' => $seller2->id,
                'category_ids' => [10],
            ],
            [
                'name' => 'メイクセット',
                'price' => 2500,
                'brand' => null,
                'description' => '便利なメイクアップセット',
                'image' => 'products/makeup.jpg',
                'status' => 3,
                'seller_id' => $seller2->id,
                'category_ids' => [6],
            ],
        ];

        foreach ($products as $product) {
            $categoryIds = $product['category_ids'];
            unset($product['category_ids']);

            $productId = DB::table('products')->insertGetId([
                'seller_id' => $product['seller_id'],
                'name' => $product['name'],
                'brand' => $product['brand'],
                'description' => $product['description'],
                'price' => $product['price'],
                'image' => $product['image'],
                'status' => $product['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($categoryIds as $categoryId) {
                DB::table('product_categories')->updateOrInsert(
                    [
                        'product_id' => $productId,
                        'category_id' => $categoryId,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'seller1@example.com'],
            [
                'name' => '出品ユーザー1',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'seller2@example.com'],
            [
                'name' => '出品ユーザー2',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'user@example.com'],
            [
                'name' => '一般ユーザー',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $users = DB::table('users')
            ->whereIn('email', [
                'seller1@example.com',
                'seller2@example.com',
                'user@example.com',
            ])
            ->get()
            ->keyBy('email');

        DB::table('profiles')->updateOrInsert(
            ['user_id' => $users['seller1@example.com']->id],
            [
                'nickname' => '出品ユーザー1',
                'postal_code' => '111-1111',
                'address' => '東京都渋谷区',
                'building' => 'テストビル101',
                'profile_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('profiles')->updateOrInsert(
            ['user_id' => $users['seller2@example.com']->id],
            [
                'nickname' => '出品ユーザー2',
                'postal_code' => '222-2222',
                'address' => '東京都新宿区',
                'building' => 'サンプルマンション202',
                'profile_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('profiles')->updateOrInsert(
            ['user_id' => $users['user@example.com']->id],
            [
                'nickname' => '一般ユーザー',
                'postal_code' => '333-3333',
                'address' => '東京都品川区',
                'building' => 'デモハイツ303',
                'profile_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}

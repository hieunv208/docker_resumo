<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'HieuNguyen',
            'email' => 'hieuhanufit@gmail.com',
            'password' => bcrypt('password'),
            'code' => '123456',
            'active' => 1,
            'phone_number' => '0335141096',
            "active" =>  1,
            'user_type' => 0,
            'api_token' => Str::random(60),
        ]);
    }
}

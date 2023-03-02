<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class
        ]);

        $this->call([
            CategorySeeder::class
        ]);

        $this->call([
            EntrepreneurshipSeeder::class
        ]);

        $this->call([
            CommentsSeeder::class
        ]);

        $this->call([
            OrderSeeder::class
        ]);

        $this->call([
            OrderDetailSeeder::class
        ]);

        $this->call([
            PaymentMethodsSeeder::class
        ]);

        $this->call([
            RoleAndPermissionSeeder::class
        ]);


    }
}

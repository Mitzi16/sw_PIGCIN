<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CatalogSeeder::class,
            LocationSeeder::class,
            InstitutionSeeder::class,
            CallSeeder::class,
            KnowledgeAreaSeeder::class,
            UserSeeder::class,
            ModuleSeeder::class,
            PermissionSeeder::class,
            AdminSeeder::class,
            PaymentVoucherSeeder::class,
        ]);
    }
}

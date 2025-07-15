<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentVoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payment_voucher_statuses')->insert([
            [
                'name' => 'Comprobante enviado',
                'class' => 'border border-yellow-400 dark:bg-yellow-800 dark:text-yellow-200 bg-yellow-50 text-yellow-500 rounded-xl px-2 dark:opacity-95 text-center w-max',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Comprobante rechazado',
                'class' => 'dark:bg-red-800 dark:text-red-200 bg-red-100 text-red-500 rounded-xl px-2 dark:opacity-95 opacity-85 text-center w-max',
                'created_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Comprobante aceptado',
                'class' => 'border border-red-600 border border-green-600 dark:bg-green-800 dark:text-green-200 bg-green-100 text-green-500 rounded-xl px-2 dark:opacity-95 text-center w-max',
                'created_at' => date('Y-m-d H:m:s')
            ],
        ]);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->boolean('requires_invoice')->default(false)->after('comments');
            $table->string('rfc')->nullable()->after('requires_invoice');
            $table->string('direccion_fiscal')->nullable()->after('rfc');
            $table->string('regimen_fiscal')->nullable()->after('direccion_fiscal');
            $table->string('uso_cfdi')->nullable()->after('regimen_fiscal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->dropColumn([
                'requires_invoice',
                'rfc',
                'direccion_fiscal',
                'regimen_fiscal',
                'uso_cfdi',
            ]);
        });
    }
};
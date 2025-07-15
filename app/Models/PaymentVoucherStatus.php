<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentVoucherStatus extends Model
{
    use HasFactory;
    protected $table = 'payment_voucher_statuses';

    protected $fillable = [
        'name',
        'class'
    ];

    public function paymentVouchers()
    {
        return $this->hasMany(PaymentVoucher::class);
    }
}

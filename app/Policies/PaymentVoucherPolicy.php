<?php

namespace App\Policies;

use App\Models\PaymentVoucher;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentVoucherPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PaymentVoucher $paymentVoucher): bool
    {
        return $user->canPermission('paymentVoucher.validate') || $paymentVoucher->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PaymentVoucher $paymentVoucher): bool
    {
        return $paymentVoucher->payment_voucher_status_id === 2 && $paymentVoucher->user_id === $user->id; // comprobante rechazado
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PaymentVoucher $paymentVoucher): bool
    {
        return $paymentVoucher->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PaymentVoucher $paymentVoucher): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PaymentVoucher $paymentVoucher): bool
    {
        //
    }

    public function handleValidate(User $user, PaymentVoucher $paymentVoucher): bool
    {
        return $paymentVoucher->payment_voucher_status_id !== 3; // no cambiar estatus comprobantes aprobados
    }
}

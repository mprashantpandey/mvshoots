<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Partner;
use App\Models\Payment;
use App\Models\User;
use App\Support\AdminCityScope;
use Illuminate\Support\Facades\Auth;

trait AuthorizesAdminCity
{
    protected function admin(): Admin
    {
        return Auth::guard('admin')->user();
    }

    protected function abortUnlessBookingInScope(Booking $booking): void
    {
        abort_unless(AdminCityScope::adminCanAccessBooking($this->admin(), $booking), 403);
    }

    protected function abortUnlessPartnerInScope(Partner $partner): void
    {
        abort_unless(AdminCityScope::adminCanAccessPartner($this->admin(), $partner), 403);
    }

    protected function abortUnlessPaymentInScope(Payment $payment): void
    {
        $payment->loadMissing('booking');
        if ($payment->booking) {
            $this->abortUnlessBookingInScope($payment->booking);
        }
    }

    protected function abortUnlessUserInScope(User $user): void
    {
        $admin = $this->admin();
        if ($admin->isSuperAdmin()) {
            return;
        }
        $ok = $user->bookings()->where('city_id', $admin->city_id)->exists();
        abort_unless($ok, 403);
    }
}

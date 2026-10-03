<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use App\Modules\Lab\Models\LabOrder;
use App\Support\Module;
use Illuminate\Support\Facades\DB;

class GuestRecordLinker
{
    /**
     * Backfill guest bookings (user_id=null) to a user matched by email.
     * Called after register / login / Google link so guests who later
     * create an account see their appointment history.
     */
    public static function link(User $user): void
    {
        if (empty($user->email)) {
            return;
        }

        DB::transaction(function () use ($user) {
            Appointment::whereNull('user_id')
                ->where('email', $user->email)
                ->update(['user_id' => $user->id]);

            if (Module::enabled('lab')) {
                LabOrder::whereNull('user_id')
                    ->where('email', $user->email)
                    ->update(['user_id' => $user->id]);
            }
        });
    }
}

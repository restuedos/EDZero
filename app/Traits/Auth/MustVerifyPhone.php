<?php

namespace App\Traits\Auth;

use App\Notifications\VerifyPhone;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Tzsk\Otp\Facades\Otp;

trait MustVerifyPhone
{
    protected Repository $store;

    public function __construct()
    {
        parent::boot();

        $this->store = Cache::store();
    }

    /**
     * Determine if the user has verified their phone number.
     *
     * @return bool
     */
    public function hasVerifiedPhone()
    {
        return ! is_null($this->phone_verified_at);
    }

    /**
     * Mark the given user's phone number as verified.
     *
     * @return bool
     */
    public function markPhoneAsVerified()
    {
        return $this->forceFill([
            'phone_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Send the phone number verification notification.
     *
     * @return void
     */
    public function sendPhoneVerificationNotification()
    {
        $this->notify(new VerifyPhone($this->store));
    }

    /**
     * Verify the phone number verification OTP.
     *
     * @param  mixed  $otp
     * @return void
     */
    public function verifyPhoneVerificationOTP($otp)
    {
        if (Otp::match($otp, ((string) $this->phone) . '-code') || (App::environment('local') && $otp == '123456')) {
            Cache::flush();

            return $this->markPhoneAsVerified();
        }

        return false;
    }

    /**
     * Check if verification OTP has too many attempts
     *
     * @param  mixed  $otp
     * @return void
     */
    public function verificationOTPTooManyAttempts()
    {
        $attempt = $this->store->get($this->keyFor((string) $this->phone) . '-attempt');

        return $attempt > 3;
    }

    /**
     * Verify the verification OTP expired.
     * Check if verification OTP is expired

    /**
     * Check if verification OTP is expired
     *
     * @param  mixed  $otp
     * @return void
     */
    public function verifyVerificationOTPExpired()
    {
        $expiryTime = $this->store->get($this->keyFor((string) $this->phone) . '-expiryTime');

        return Carbon::parse($expiryTime)->isPast();
    }

    /**
     * Get the phone number that should be used for verification.
     *
     * @return string
     */
    public function getPhoneForVerification()
    {
        return $this->phone;
    }

    /**
     * Get the key for otp cache.
     *
     * @param  string  $key
     */
    protected function keyFor($key): string
    {
        return md5(sprintf('%s-%s', 'tzsk-otp', $key));
    }
}

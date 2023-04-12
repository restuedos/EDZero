<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\VonageMessage;
use Illuminate\Notifications\Notification;

class VerifyPhone extends Notification
{
    /**
     * The callback that should be used to create the OTP.
     *
     * @var \Closure|null
     */
    public static $createOTPCallback;

    /**
     * The callback that should be used to build the SMS message.
     *
     * @var \Closure|null
     */
    public static $toSMSCallback;

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['sms'];
    }

    /**
     * Build the SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \App\Notifications\Messages\SMSMessage
     */
    public function toSMS($notifiable)
    {
        $otp = $this->verificationOTP($notifiable);

        if (static::$toSMSCallback) {
            return call_user_func(static::$toSMSCallback, $notifiable, $otp);
        }

        return $this->buildSMSMessage($notifiable, $otp);
    }

    /**
     * Get the verify phone notification SMS message for the given URL.
     *
     * @param  string  $url
     * @return \Illuminate\Notifications\Messages\VonageMessage
     */
    protected function buildSMSMessage($notifiable, $otp)
    {
        return (new VonageMessage)
            ->clientReference((string) $notifiable->id)
            ->content(`Thank you for using EDZero. Please use the code below to verify your phone number: $otp`);
    }

    /**
     * Get the verification OTP for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationOTP($notifiable)
    {
        if (static::$createOTPCallback) {
            return call_user_func(static::$createOTPCallback, $notifiable);
        }

        return rand(100000, 999999);
    }

    /**
     * Set a callback that should be used when creating the OTP.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function createOTPUsing($callback)
    {
        static::$createOTPCallback = $callback;
    }

    /**
     * Set a callback that should be used when building the notification SMS message.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function toSMSUsing($callback)
    {
        static::$toSMSCallback = $callback;
    }
}

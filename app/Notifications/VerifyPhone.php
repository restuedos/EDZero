<?php

namespace App\Notifications;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Closure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Notifications\Channels\VonageSmsChannel;
use Illuminate\Notifications\Messages\VonageMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Log;
use Tzsk\Otp\Facades\Otp;

class VerifyPhone extends Notification
{
    use Queueable;

    /**
     * The callback that should be used to create the OTP.
     *
     * @var Closure|null
     */
    public static $createOTPCallback;

    /**
     * The callback that should be used to build the Vonage message.
     *
     * @var Closure|null
     */
    public static $toVonageCallback;

    protected Repository $store;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        $this->store = Cache::store();
    }

    /**
     * Set a callback that should be used when creating the OTP.
     *
     * @param  Closure  $callback
     * @return void
     */
    public static function createOTPUsing($callback)
    {
        static::$createOTPCallback = $callback;
    }

    /**
     * Set a callback that should be used when building the notification Vonage message.
     *
     * @param  Closure  $callback
     * @return void
     */
    public static function toVonageUsing($callback)
    {
        static::$toVonageCallback = $callback;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [VonageSmsChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toVonage(object $notifiable): VonageMessage
    {
        $otp = $this->verificationOTP($notifiable);

        if (static::$toVonageCallback) {
            return call_user_func(static::$toVonageCallback, $notifiable, $otp);
        }
        $this->getAttempt($notifiable);

        Log::debug(print_r(json_encode($otp), true));

        return $this->buildVonageMessage($notifiable, $otp);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
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

    protected function getExpiry($notifiable)
    {
        $attempt = $this->store->get($this->keyFor((string) $notifiable->phone) . '-attempt');
        if (empty($attempt)) {
            $attempt = 1;
        }

        switch ($attempt) {
            case 1:
                $expiry = 1;
                break;
            case 2:
                $expiry = 2;
                break;
            case 3:
                $expiry = 5;
                break;
            default:
                $expiry = 0;
                break;
        }
        $this->store->put($this->keyFor((string) $notifiable->phone) . '-expiryTime', Carbon::now()->addMinutes($expiry)->toDateTimeString());

        return $expiry;
    }

    protected function getExpiryTime($notifiable)
    {
        $expiryTime = $this->store->get($this->keyFor((string) $notifiable->phone) . '-expiryTime');

        return $expiryTime;
    }

    protected function getAttempt($notifiable)
    {
        $attempt = $this->store->get($this->keyFor((string) $notifiable->phone) . '-attempt');
        if (empty($attempt) || $attempt > 3) {
            $attempt = 0;
        }

        $attempt++;

        $this->store->put($this->keyFor((string) $notifiable->phone) . '-attempt', $attempt, CarbonInterval::hour()->totalSeconds);

        return $attempt;
    }

    /**
     * Get the verify phone notification Vonage message for the given URL.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\VonageMessage
     */
    protected function buildVonageMessage($notifiable, $otp)
    {
        return (new VonageMessage)
            ->clientReference((string) $notifiable->id)
            ->content('Thank you for using EDZero. Please use the code below to verify your phone number: $otp');
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

        $expiry = $this->getExpiry($notifiable);

        return Otp::expiry($expiry)->make(((string) $notifiable->phone) . '-code');
    }
}

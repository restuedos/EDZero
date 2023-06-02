<?php

namespace App\Fortify\Http\Controllers;

use App\Fortify\Fortifier as Fortify;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PhoneVerificationNotificationController extends Controller
{
    /**
     * Send a new phone verification notification.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->user()->hasVerifiedPhone()) {
            return $request->wantsJson()
                        ? new JsonResponse('', 204)
                        : redirect()->intended(Fortify::redirects('phone-verification'));
        }

        if ($request->user()->verificationOTPTooManyAttempts()) {
            return $request->wantsJson()
                    ? new JsonResponse('Too many attempts, please try again later.', 429)
                    : back()->withErrors(['TOO_MANY_REQUESTS' => 'Too many attempts, please try again later.']);
        }
        $request->user()->sendPhoneVerificationNotification();

        return $request->wantsJson()
                    ? new JsonResponse('', 202)
                    : back()->with('status', Fortify::VERIFICATION_LINK_SENT);
    }
}

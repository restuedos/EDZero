<?php

namespace App\Fortify\Http\Controllers;

use App\Fortify\Contracts\VerifyPhoneViewResponse;
use App\Fortify\Fortifier as Fortify;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PhoneVerificationPromptController extends Controller
{
    /**
     * Display the phone verification prompt.
     *
     * @return \App\Fortify\Contracts\VerifyPhoneViewResponse
     */
    public function __invoke(Request $request)
    {
        return $request->user()->hasVerifiedPhone()
                    ? redirect()->intended(Fortify::redirects('phone-verification'))
                    : app(VerifyPhoneViewResponse::class);
    }
}

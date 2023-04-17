<?php

namespace App\Fortify;

use Laravel\Fortify\Features;

class Featurify extends Features
{
    /**
     * Enable the phone verification feature.
     *
     * @return string
     */
    public static function phoneVerification()
    {
        return 'phone-verification';
    }
}

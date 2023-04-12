<?php

namespace App\Fortify;

use Laravel\Fortify\Fortify;

class Fortifier extends Fortify {

    public static function phone() {
        return config('fortify.phone', 'phone');
    }

    public static function identity() {
        return config('fortify.identity', 'username');
    }

}
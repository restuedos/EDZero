<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Fortify\Fortifier as Fortify;

class AuthenticateUser
{
    public function __invoke(Request $request)
    {
        $user = User::where($this->identity($request), $request->username)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            return $user;
        }
    }

    private function identity($request) {
        if ($this->validEmail($request->username)) {
            $identity = Fortify::email();
        } else {
            $identity = Fortify::username();
        }
        return $identity;
    }

    private function validEmail($email) {
        return Validator::make(['email' => $email], [
            'email' => 'required|email|max:255'
        ])->passes();
    }
}

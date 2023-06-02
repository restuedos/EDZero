<?php

namespace App\Actions\Fortify;

use App\Fortify\Fortifier as Fortify;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthenticateUser
{
    protected $identity;

    public function __construct()
    {
        $this->identity = Fortify::identity();
    }

    public function __invoke(Request $request)
    {
        $user = User::where($this->identity($request), $this->format($request))->first();
        if ($user && Hash::check($request->password, $user->password)) {
            return $user;
        }
    }

    private function identity($request)
    {
        if ($this->validEmail($request->{$this->identity})) {
            $identity = Fortify::email();
        } elseif ($this->validPhone($request->{$this->identity})) {
            $identity = Fortify::phone();
        } else {
            $identity = Fortify::username();
        }

        return $identity;
    }

    private function validEmail($email)
    {
        return Validator::make(['email' => $email], [
            'email' => 'required|email|max:255',
        ])->passes();
    }

    private function validPhone($phone)
    {
        return Validator::make(['phone' => $phone], [
            'phone' => ['required', 'regex:/\+?([ -]?\d+)+|\(\d+\)([ -]\d+)/', 'starts_with:8,08,+62,62'],
        ])->passes();
    }

    private function format($request)
    {
        return $this->identity($request) == 'phone' ? $this->formatPhone($request->{$this->identity}) : $request->{$this->identity};
    }

    private function formatPhone($phone)
    {
        $phone = Str::remove([' ', '-', '(', ')'], $phone);

        return substr($phone, strpos($phone, '8'));
    }
}

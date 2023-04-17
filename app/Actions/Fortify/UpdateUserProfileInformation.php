<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Illuminate\Http\UploadedFile;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        $validator = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'digits_between:11,12', 'starts_with:8', Rule::unique('users')->ignore($user->id)],
            'otp' => ['nullable', 'digits:6'],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ]);
        $validator->validateWithBag('updateProfileInformation');

        if ($input['phone'] !== $user->phone) {
            $this->updateVerifiedPhoneUser($user, $input);
        }

        if (isset($input['otp'])) {
            $validator->after(function ($validator) use ($user, $input) {
                if (!($user->verifyPhoneVerificationOTP($input['otp']))) {
                    $validator->errors()->add(
                        'otp',
                        __('OTP Code is Invalid.')
                    );
                }
                if (!($user->verifyVerificationOTPExpired())) {
                    $validator->errors()->add(
                        'otp',
                        __('OTP Code is Expired.')
                    );
                }
            });
            $validator->validateWithBag('updateProfileInformation');
        }

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedEmailUser($user, $input);
        }

        $user->forceFill([
            'name' => $input['name'],
            'username' => $input['username'],
        ])->save();
    }

    /**
     * Update the given verified phone user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedPhoneUser(User $user, array $input): void
    {
        $user->forceFill([
            'phone' => $input['phone'],
            'phone_verified_at' => null,
        ])->save();
    }

    /**
     * Update the given verified email user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedEmailUser(User $user, array $input): void
    {
        $user->forceFill([
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Email;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => ['required'],
//            'password' => ['required', 'confirmed', Rules\Password::defaults()], TODO: define requirements
            'password' => ['required', 'confirmed'],
        ]);

        $passwordReset = \App\Models\PasswordReset::all()->first(
            fn($pr) => Hash::check($request->token, $pr->pr_token)
        );

        if (empty($passwordReset)) {
            return back()->withErrors(__(Password::INVALID_TOKEN));
        }

        $email = Email::whereEmailId($passwordReset->pr_email_id)->first()->email_address;

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            ['email' => $email, ...$request->only('password', 'password_confirmation', 'token')],
            function ($user) use ($request) {
                $user->forceFill([
                    'user_password' => Hash::make($request->password),
                    'user_lastpw' =>time(),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withInput($request->only('email'))
                            ->withErrors(['email' => __($status)]);
    }
}

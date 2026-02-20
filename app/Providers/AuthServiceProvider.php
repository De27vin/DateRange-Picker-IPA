<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Providers\CustomUserProvider;
use Illuminate\Http\Request;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPolicies();

        Auth::provider('custom_user_provider', function ($app, array $config) {
            return new CustomUserProvider($app['hash'], $config['model'], $config['method_to_email_model']);
        });

        Auth::viaRequest('custom-token', function (Request $request) {
            \Log::info('check token');
            \Log::info($request->token);
            return $request->token == 'Bm0uD0r1U1qmN3csPJod8qCYwjxoAj8kNMwLouOgpKLp23x6MOs5ksQEzmsl0vxp';
            // return User::where('token', $request->token)->first();
        });

        Auth::viaRequest('custom-login', function (Request $request) {
            \Log::info('check custom-login');
            \Log::info($request->username);
            return User::query()->where('email','=',$request->email)->first();
            // return $request->token == 'Bm0uD0r1U1qmN3csPJod8qCYwjxoAj8kNMwLouOgpKLp23x6MOs5ksQEzmsl0vxp';
            // return User::where('token', $request->token)->first();
        });
    }

}

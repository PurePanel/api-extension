<?php namespace Visiosoft\ApiExtension\Http\Controllers;

use Anomaly\UsersModule\User\Contract\UserRepositoryInterface;
use Anomaly\UsersModule\User\UserAuthenticator;
use Firebase\JWT\JWT;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request, UserAuthenticator $authenticator)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        if (!$user = $authenticator->attempt($request->all())) {
            return response()->json([
                'success' => false,
                'message' => trans('visiosoft.extension.api::message.invalid_login'),
                'errors' => [trans('visiosoft.extension.api::message.invalid_login_message')]
            ], 401);
        }

        $user->setAttribute('apikey', Str::random(48));
        $user->setAttribute('jwt', JWT::encode(['iat' => time(), 'exp' => time() + config('visiosoft.extension.api::api.jwt_refresh')], config('visiosoft.extension.api::api.jwt_secret') . '-Rfs'));
        $user->save();

        return response()->json([
            'success' => true,
            'apikey' => $user->getAttribute('apikey'),
            'refresh_token' => $user->getAttribute('jwt'),
            'access_token' => JWT::encode(['iat' => time(), 'exp' => time() + config('visiosoft.extension.api::api.jwt_access')], config('visiosoft.extension.api::api.jwt_secret') . '-Acs'),
        ]);
    }

    /**
     * JWT Auth refresh token
     *
     */
    public function refresh(Request $request, UserRepositoryInterface $users)
    {
        $request->validate([
            'email' => 'required',
            'refresh_token' => 'required',
        ]);

        $user = $users->newQuery()->where('email', $request->email)
            ->where('jwt', $request->refresh_token)
            ->first();

        if ($user) {
            $user->setAttribute('apikey', Str::random(48));
            $user->setAttribute('jwt', JWT::encode(['iat' => time(), 'exp' => time() + config('visiosoft.extension.api::api.jwt_refresh')], config('visiosoft.extension.api::api.jwt_secret') . '-Rfs'));
            $user->save();

            return response()->json([
                'success' => true,
                'access_token' => JWT::encode(['iat' => time(), 'exp' => time() + config('visiosoft.extension.api::api.jwt_access')], config('visiosoft.extension.api::api.jwt_secret') . '-Acs'),
                'apikey' => $user->getAttribute('apikey'),
                'refresh_token' => $user->getAttribute('jwt'),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => trans('visiosoft.extension.api::message.invalid_token_message'),
                'errors' => [trans('visiosoft.extension.api::message.invalid_token')]
            ], 401);
        }
    }

    /**
     * JWT Auth sign out
     *
     */
    public function logout(Request $request, UserRepositoryInterface $users)
    {
        $request->validate([
            'email' => 'required',
            'refresh_token' => 'required',
        ]);

        $user = $users->newQuery()->where('email', $request->email)
            ->where('jwt', $request->refresh_token)
            ->first();

        if ($user) {
            $user->setAttribute('jwt', null);
            $user->save();

            return response()->json([
                'success' => true,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => trans('visiosoft.extension.api::message.invalid_token_message'),
                'errors' => [trans('visiosoft.extension.api::message.invalid_token')]
            ], 401);
        }
    }
}
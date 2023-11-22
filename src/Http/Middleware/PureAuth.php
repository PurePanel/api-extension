<?php namespace Visiosoft\ApiExtension\Http\Middleware;

use Anomaly\UsersModule\User\UserModel;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Firebase\JWT\ExpiredException;

class PureAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $auth   = $request->header('Authorization');
        $token  = null;
        $apikey = null;

        if (Str::startsWith($auth, 'Bearer ')) {
            $token = Str::substr($auth, 7);
        }

        if (Str::startsWith($auth, 'Apikey ')) {
            $apikey = Str::substr($auth, 7);
        }

        if (!$token && !$apikey) {
            return response()->json([
                'message' => 'Authorization header missed in payload.',
                'errors' => 'Missing Authorization.'
            ], 422);
        }

        if ($token) {
            try {
                JWT::decode($token, config('visiosoft.extension.api::api.jwt_secret').'-Acs', ['HS256']);
            } catch (ExpiredException $e) {
                return response()->json([
                    'message' => 'Given token is expired.',
                    'errors' => 'Expired token.'
                ], 401);
            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Given token is invalid.',
                    'errors' => 'Invalid token.'
                ], 401);
            }
        }

        if ($apikey) {
            if (!UserModel::where('apikey', $apikey)->first()) {
                return response()->json([
                    'message' => 'Given API Key is invalid.',
                    'errors' => 'Invalid API Key.'
                ], 401);
            }
        }

        return $next($request);
    }
}
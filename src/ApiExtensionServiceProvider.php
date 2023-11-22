<?php namespace Visiosoft\ApiExtension;

use Anomaly\Streams\Platform\Addon\AddonServiceProvider;
use Illuminate\Routing\Router;
use Visiosoft\ApiExtension\Http\Controllers\AuthController;
use Visiosoft\ApiExtension\Http\Middleware\PureAuth;

class ApiExtensionServiceProvider extends AddonServiceProvider
{
    /**
     * Map additional addon routes and middlewares.
     *
     * @param Router $router
     */
    public function map(Router $router)
    {
        $this->mapRouters($router);

        $middlewares = [
            'throttle:api',
            PureAuth::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ];

        foreach ($middlewares as $middleware) {
            $router->pushMiddlewareToGroup('apikey', $middleware);
        }
    }

    /**
     * @param Router $router
     * @return void
     */
    public function mapRouters(Router $router)
    {
        $router->post('api/login', [AuthController::class, 'login']);
        $router->post('api/refresh', [AuthController::class, 'refresh']);
        $router->post('api/logout', [AuthController::class, 'logout']);
    }
}

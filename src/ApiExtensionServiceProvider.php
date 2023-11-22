<?php namespace Visiosoft\ApiExtension;

use Anomaly\Streams\Platform\Addon\AddonServiceProvider;
use Illuminate\Routing\Router;
use Visiosoft\ApiExtension\Http\Controllers\AuthController;
use Visiosoft\ApiExtension\Http\Middleware\PureAuth;

class ApiExtensionServiceProvider extends AddonServiceProvider
{
    protected $groupMiddleware = [
        'api' => [
            'throttle:api',
            PureAuth::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * Map additional addon routes.
     *
     * @param Router $router
     */
    public function map(Router $router)
    {
        $this->mapRouters($router);
    }

    public function mapRouters(Router $router)
    {
        $router->post('api/login', [AuthController::class, 'login']);
        $router->post('api/refresh', [AuthController::class, 'refresh']);
        $router->post('api/logout', [AuthController::class, 'logout']);
    }
}

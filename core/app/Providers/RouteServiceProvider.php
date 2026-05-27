<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;

class
RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        parent::boot();
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api-sensitive', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
        $this->mapChatbotRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }

    /**
     * Chatbot data routes — token-protected, no rate limiting.
     * Registered outside the 'api' middleware group so the default
     * 60 req/min throttle never applies to Node.js server-to-server calls.
     */
    protected function mapChatbotRoutes()
    {
        Route::prefix('api/chatbot')
            ->middleware(\App\Http\Middleware\ChatbotApiToken::class)
            ->group(function () {
                Route::get('/products',               [\App\Http\Controllers\Api\ChatbotDataController::class, 'products']);
                Route::get('/products/category/{id}', [\App\Http\Controllers\Api\ChatbotDataController::class, 'categoryProducts']);
                Route::get('/categories',             [\App\Http\Controllers\Api\ChatbotDataController::class, 'categories']);
                Route::get('/orders/count',           [\App\Http\Controllers\Api\ChatbotDataController::class, 'ordersCount']);
                Route::get('/orders/{number}',        [\App\Http\Controllers\Api\ChatbotDataController::class, 'orderByNumber']);
                Route::get('/orders',                 [\App\Http\Controllers\Api\ChatbotDataController::class, 'ordersByEmail']);
                Route::get('/customers/count',        [\App\Http\Controllers\Api\ChatbotDataController::class, 'customersCount']);
                Route::get('/customers',              [\App\Http\Controllers\Api\ChatbotDataController::class, 'customerByEmail']);
                Route::get('/stats',                  [\App\Http\Controllers\Api\ChatbotDataController::class, 'stats']);
            });
    }
}
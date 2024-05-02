<?php

// eval(testserviceProvider('installationprovider'));

namespace laravelLara\lskusd\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\Facades\Route;

class InstallationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Kernel $kernel)
    {
        Route::group(['middleware' => 'web', 'namespace' => 'laravelLara\lskusd\App\Http'], function () {
            $this->loadRoutesFrom(realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . '/routes/web.php'));
        });
        $this->loadViewsFrom(realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . '/resources/views'), 'Installation');
        $this->loadCustomHelper();
        $this->loadMiddleware();
    }

    public function loadCustomHelper()
    {
        require realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . '/customhelper.php');
        require realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . '/utils/Function.php');
    }
    protected function loadMiddleware()
    {
        $this->app['router']->aliasMiddleware('caninstall', \laravelLara\lskusd\Http\Middleware\CanInstall::class);
    }
}


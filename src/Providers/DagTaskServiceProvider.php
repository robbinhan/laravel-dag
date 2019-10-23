<?php

namespace App\Providers;

use App\Extensions\Dag\DagManager;
use Illuminate\Support\ServiceProvider;

/**
 * Class DagTaskServiceProvider
 * @package App\Providers
 */
class DagTaskServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('dag', function ($app) {
            $config = $app->make('config')->get('task', []);

            return new DagManager($config);
        });
    }
}

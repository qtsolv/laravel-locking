<?php

namespace Quarks\Laravel\Locking;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class LockingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        Blueprint::macro('lockVersion', function ($columnName = 'lock_version') {
            /** @var Blueprint $this */
            $this->unsignedInteger($columnName)->nullable();
            return $this;
        });
        Blueprint::macro('dropLockVersion', function ($columnName = 'lock_version') {
            /** @var Blueprint $this */
            $this->dropColumn($columnName);
            return $this;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('lockInput', function (Model $model, $columnName = 'lock_version') {
            return sprintf('<input type="hidden" name="%s" value="%s">', $columnName, $model->{$columnName});
        });
    }
}

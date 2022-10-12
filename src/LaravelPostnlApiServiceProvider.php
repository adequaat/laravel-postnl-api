<?php

namespace Adequaat\LaravelPostnlApi;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Adequaat\LaravelPostnlApi\Commands\LaravelPostnlApiCommand;

class LaravelPostnlApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-postnl-api')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-postnl-api_table')
            ->hasCommand(LaravelPostnlApiCommand::class);
    }
}

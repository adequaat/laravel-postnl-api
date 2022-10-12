<?php

namespace Adequaat\LaravelPostnlApi;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Adequaat\LaravelPostnlApi\Commands\LaravelPostnlApiCommand;

class LaravelPostnlApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-postnl-api')
            ->hasConfigFile();
    }
}

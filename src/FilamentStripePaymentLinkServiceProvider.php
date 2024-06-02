<?php

namespace Andreia\FilamentStripePaymentLink;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentStripePaymentLinkServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-stripe-payment-link';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile();
            });
    }

    public function packageBooted(): void
    {
        //
    }
}

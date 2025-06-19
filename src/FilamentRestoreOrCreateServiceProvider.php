<?php

declare(strict_types=1);

namespace MartinPetricko\FilamentRestoreOrCreate;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentRestoreOrCreateServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-restore-or-create';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasTranslations();
    }

    public function packageRegistered(): void
    {
        //
    }

    public function packageBooted(): void
    {
        //
    }
}

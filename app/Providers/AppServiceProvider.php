<?php

namespace App\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
        //     $switch
        //         ->locales(['en', 'sv'])
        //         ->flags([
        //             'en' => asset('translation-logo/english.svg'),
        //             'sv' => asset('translation-logo/sweden.svg'),
        //         ])
        //         ->circular();
        // });
        FilamentAsset::register([
            Css::make('custom-css-hooks', asset('css/design-hooks.css')),
        ]);
    }
}

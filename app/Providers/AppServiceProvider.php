<?php

namespace App\Providers;

use App\Support\SiteContent;
use App\Support\SmartClassroom;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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
        if (env('RENDER')) {
            config([
                'session.driver' => 'file',
                'cache.default' => 'file',
            ]);
        }

        if ($this->app->environment('production')) {
            URL::forceRootUrl(config('app.url'));
            URL::forceScheme('https');
        }

        View::composer(['layouts.sidebar', 'layouts.app', 'components.system-diagram'], function ($view) {
            $user = auth()->user();

            $view->with([
                'smartClassroomLayers' => SmartClassroom::layers(),
                'smartClassroomModules' => SmartClassroom::modulesByLayer($user),
            ]);
        });

        View::composer(['front.*', 'layouts.front'], function ($view) {
            $view->with('site', SiteContent::all());
        });
    }
}

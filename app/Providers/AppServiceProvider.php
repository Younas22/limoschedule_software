<?php

namespace App\Providers;

use App\Models\EmailSetting;
use App\Services\DatabaseTranslationLoader;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Both "translation.loader" and "translator" must be bound as resolved
        // instances (not singleton factories) here. Laravel's TranslationServiceProvider
        // is deferred and only checks $app->instances (not $app->bindings) before
        // re-registering itself — if either key is still "unresolved" the first time
        // it's requested, the deferred provider loads and silently overwrites this
        // binding with its own FileLoader. Pre-resolving both closes that gap.
        $frameworkLangPath = dirname((new \ReflectionClass(Translator::class))->getFileName()).'/lang';

        $fileLoader = new FileLoader($this->app['files'], [$frameworkLangPath, $this->app['path.lang']]);
        $loader = new DatabaseTranslationLoader($fileLoader);

        $this->app->instance('translation.loader', $loader);

        $translator = new Translator($loader, $this->app->getLocale());
        $translator->setFallback($this->app->getFallbackLocale());

        $this->app->instance('translator', $translator);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::if('permission', function (string $slug) {
            return Auth::guard('admin')->check() && Auth::guard('admin')->user()->hasPermission($slug);
        });

        Blade::if('role', function (string $slug) {
            return Auth::guard('admin')->check() && Auth::guard('admin')->user()->hasRole($slug);
        });

        // The admin panel must stay reachable while the public site is in
        // maintenance mode, otherwise nobody could turn maintenance mode
        // back off again.
        PreventRequestsDuringMaintenance::except(['admin/*']);

        // Let the admin-configured mail settings override .env for every
        // mailer resolved after this point. Guarded so a missing table
        // (fresh install, before migrations run) never breaks booting.
        try {
            EmailSetting::current()->applyToRuntimeConfig();
        } catch (\Throwable) {
            //
        }
    }
}

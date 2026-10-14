<?php

namespace Mcamara\LaravelLocalization\Commands;

use Mcamara\LaravelLocalization\LaravelLocalization;
use Mcamara\LaravelLocalization\Traits\TranslatedRouteCommandContext;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Console\RouteListCommand;

class RouteTranslationsListCommand extends RouteListCommand
{
    use TranslatedRouteCommandContext;

    /**
     * The name and signature of the console command.
     *
     * Declared as a signature because Laravel 13's parent command defines one,
     * which would otherwise take precedence over $name and register this
     * command as "route:list". The options mirror the parent's route:list.
     *
     * @var string
     */
    protected $signature = 'route:trans:list
                    {locale : The locale to list routes for}
                    {--json : Output the route list as JSON}
                    {--method= : Filter the routes by method}
                    {--action= : Filter the routes by action}
                    {--name= : Filter the routes by name}
                    {--domain= : Filter the routes by domain}
                    {--middleware= : Filter the routes by middleware}
                    {--path= : Only show routes matching the given path pattern}
                    {--except-path= : Do not display the routes matching the given path pattern}
                    {--r|reverse : Reverse the ordering of the routes}
                    {--sort=uri : The column (domain, method, uri, name, action, middleware, definition) to sort by}
                    {--except-vendor : Do not display routes defined by vendor packages}
                    {--only-vendor : Only display routes defined by vendor packages}';

    /**
     * @var string
     */
    protected $description = 'List all registered routes for specific locales';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $locale = $this->argument('locale');

        if ( ! $this->isSupportedLocale($locale)) {
            $this->error("Unsupported locale: '{$locale}'.");
            return;
        }

        $this->loadFreshApplicationRoutes($locale);

        parent::handle();
    }

    /**
     * Boot a fresh copy of the application and replace the router/routes.
     *
     * @param string $locale
     * @return void
     */
    protected function loadFreshApplicationRoutes($locale)
    {
        $app = require $this->getBootstrapPath() . '/app.php';

        $key = LaravelLocalization::ENV_ROUTE_KEY;

        putenv("{$key}={$locale}");

        $app->make(Kernel::class)->bootstrap();

        putenv("{$key}=");

        $this->router = $app['router'];
    }
}

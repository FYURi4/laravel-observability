<?php

declare(strict_types=1);

namespace Furushka\Observability;

use Furushka\Observability\Console\PruneCommand;
use Furushka\Observability\Context\TraceContext;
use Furushka\Observability\Storage\DatabaseStorage;
use Furushka\Observability\Storage\StorageInterface;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class ObservabilityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/observability.php', 'observability');

        $this->app->singleton(TraceContext::class);

        $this->app->singleton(StorageInterface::class, function () {
            return new DatabaseStorage;
        });
    }

    public function boot(): void
    {
        if (! config('observability.enabled')) {
            return;
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/observability.php' => config_path('observability.php'),
            ], 'observability-config');

            $this->publishesMigrations([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'observability-migrations');

            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
            $this->commands([PruneCommand::class]);
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'observability');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->registerTracers();

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('observability:prune')->daily();
        });
    }

    private function registerTracers(): void
    {
        $context = $this->app->make(TraceContext::class);
        $storage = $this->app->make(StorageInterface::class);

        $this->app['events']->listen(RouteMatched::class, function ($event) use ($context) {
            if ($context->getTraceId()) {
                return;
            }

            $request = $event->request;

            foreach ((array) config('observability.ignore_paths', []) as $pattern) {
                if ($request->is($pattern)) {
                    return;
                }
            }

            $this->safe(fn () => $context->startTrace($request), 'startTrace');
        });

        $this->app['events']->listen(
            'Illuminate\Foundation\Http\Events\RequestHandled',
            fn ($event) => $this->safe(
                fn () => $context->endTrace($event->request, $event->response, $storage),
                'endTrace',
            ),
        );

        DB::listen(function ($query) use ($context, $storage) {
            if (str_contains($query->sql, 'observability_')) {
                return;
            }

            $this->safe(fn () => $context->recordQuerySpan($query, $storage), 'recordQuerySpan');
        });

        $this->app['events']->listen(
            'Illuminate\Log\Events\MessageLogged',
            function ($event) use ($context, $storage) {
                try {
                    $context->recordException($event, $storage);
                } catch (\Throwable) {
                    //
                }
            },
        );
    }

    private function safe(callable $callback, string $action): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            logger()->error("Observability {$action} failed", [
                'message' => $e->getMessage(),
            ]);
        }
    }
}

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

        $this->app->singleton(StorageInterface::class, function ($app) {
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
            $this->commands([
                PruneCommand::class,
            ]);
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
        /** @var TraceContext $context */
        $context = $this->app->make(TraceContext::class);
        /** @var StorageInterface $storage */
        $storage = $this->app->make(StorageInterface::class);

        // Старт трейса. RouteMatched есть во всех актуальных версиях Laravel.
        $this->app['events']->listen(
            RouteMatched::class,
            function ($event) use ($context) {
                if ($context->getTraceId()) {
                    return; // уже стартовали на этом запросе
                }

                $request = $event->request;

                foreach ((array) config('observability.ignore_paths', []) as $pattern) {
                    if ($request->is($pattern)) {
                        return;
                    }
                }

                try {
                    $context->startTrace($request);
                } catch (\Throwable $e) {
                    logger()->error('Observability startTrace failed', [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);
                }
            }
        );

        // Завершение трейса
        $this->app['events']->listen(
            'Illuminate\Foundation\Http\Events\RequestHandled',
            function ($event) use ($context, $storage) {
                try {
                    $context->endTrace($event->request, $event->response, $storage);
                } catch (\Throwable $e) {
                    logger()->error('Observability endTrace failed', [
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        );

        // SQL-спаны. Свои же таблицы игнорируем, чтобы не зациклиться.
        DB::listen(function ($query) use ($context, $storage) {
            if (str_contains($query->sql, 'observability_')) {
                return;
            }
            try {
                $context->recordQuerySpan($query, $storage);
            } catch (\Throwable $e) {
                logger()->error('Observability recordQuerySpan failed', [
                    'message' => $e->getMessage(),
                ]);
            }
        });

        // Ошибки из лога
        $this->app['events']->listen(
            'Illuminate\Log\Events\MessageLogged',
            function ($event) use ($context, $storage) {
                try {
                    $context->recordException($event, $storage);
                } catch (\Throwable $e) {
                    // Молча: логировать тут нельзя — будет рекурсия.
                }
            }
        );
    }
}

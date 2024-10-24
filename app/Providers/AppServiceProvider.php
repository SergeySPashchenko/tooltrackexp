<?php

namespace App\Providers;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\Entry;
use Filament\Support\Components\Component;
use Filament\Support\Concerns\Configurable;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Table;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Throwable;

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

    /*
     * If you would like add different configurations to the components you can separate them into different methods.
     * This way you can keep the code clean and organized.
     */

    protected function translatableComponents(): void
    {
        foreach ([Field::class, BaseFilter::class, Placeholder::class, Column::class, Entry::class] as $component) {
            /* @var Configurable $component */
            $component::configureUsing(function (Component $translatable): void {
                /** @phpstan-ignore method.notFound */
                $translatable->translateLabel();
            });
        }
    }

    public function boot(): void
    {
        Table::configureUsing(function (Table $table): void {
            $table
                ->emptyStateHeading('No data yet')
                ->striped()
                ->defaultPaginationPageOption(10)
                ->paginated([10, 25, 50, 100])
                ->extremePaginationLinks()
                ->defaultSort('created_at', 'desc');
        });

        $this->translatableComponents();
        // Rate limiting
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(500)
                ->by(optional($request->user())->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    logger()->channel('discord')
                        ->critical('Rate limiter exceeded for IP: '.$request->ip(), [
                            'user' => $request->user() ? $request->user()->toArray() : 'guest',
                        ]);
                    logger()
                        ->critical('Rate limiter exceeded for IP: '.$request->ip(), [
                            'user' => $request->user() ? $request->user()->toArray() : 'guest',
                        ]);

                    return response('Rate limit exceeded', Response::HTTP_TOO_MANY_REQUESTS, $headers);
                });
        });

        // Eloquent strict mode
        Model::shouldBeStrict(! app()->isProduction());

        if (! app()->isProduction()) {
            // Handle Lazy Loading Violations
            Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
                logger()->channel('discord')
                    ->info("Lazy load detected on relation [{$relation}] for model [".get_class($model).']');
                logger()
                    ->info("Lazy load detected on relation [{$relation}] for model [".get_class($model).']');
            });

            // Handle Discarded Attribute Violations
            Model::handleDiscardedAttributeViolationUsing(function ($model, $key) {
                logger()->channel('discord')
                    ->info('Discarded attributes ['.implode($key).'] for model ['.get_class($model).']');
                logger()
                    ->info("Discarded attributes [{$key}] for model [".get_class($model).']');
            });

            // Handle Missing Attribute Violations
            Model::handleMissingAttributeViolationUsing(function ($model, $key) {
                logger()->channel('discord')
                    ->notice("Missing attribute [{$key}] on model [".get_class($model).']');
                logger()
                    ->notice("Missing attribute [{$key}] on model [".get_class($model).']');
            });

            // Log slow commands in console
            $this->app[ConsoleKernel::class]->whenCommandLifecycleIsLongerThan(5000, function ($startedAt, $input, $status) {
                logger()->channel('discord')
                    ->warning("Command took longer than 5 seconds. Started at {$startedAt}. Input: {$input}. Status: {$status}");
                logger()
                    ->warning("Command took longer than 5 seconds. Started at {$startedAt}. Input: {$input}. Status: {$status}");
            });

            // Log slow HTTP requests
            $this->app[HttpKernel::class]->whenRequestLifecycleIsLongerThan(5000, function ($startedAt, $request, $response) {
                logger()->channel('discord')
                    ->warning("HTTP request took longer than 5 seconds. Request started at {$startedAt}. Request: {$request->fullUrl()}. Response status: {$response->status()}");
                logger()
                    ->warning("HTTP request took longer than 5 seconds. Request started at {$startedAt}. Request: {$request->fullUrl()}. Response status: {$response->status()}");
            });

            // Log slow database queries
            DB::whenQueryingForLongerThan(2000, function (Connection $connection) {
                logger()->channel('discord')
                    ->warning("Long database query on [{$connection->getName()}] exceeded 2 seconds. Duration: {$connection->totalQueryDuration()}ms.");
                logger()
                    ->warning("Long database query on [{$connection->getName()}] exceeded 2 seconds. Duration: {$connection->totalQueryDuration()}ms.");
            });

            DB::listen(function ($query) {
                if ($query->time > 1000) {
                    logger()->channel('discord')
                        ->warning('Database query took longer than 1 second.', [
                            'sql' => $query->sql,
                            'bindings' => $query->bindings,
                            'time' => $query->time,
                        ]);
                    logger()
                        ->warning('Database query took longer than 1 second.', [
                            'sql' => $query->sql,
                            'bindings' => $query->bindings,
                            'time' => $query->time,
                        ]);
                }
            });

            // Log cache hits and misses
            $this->app['events']->listen(\Illuminate\Cache\Events\CacheHit::class, function ($event) {
                logger()->info('Cache hit', ['key' => $event->key]);
            });

            $this->app['events']->listen(\Illuminate\Cache\Events\CacheMissed::class, function ($event) {
                logger()->warning('Cache missed', ['key' => $event->key]);
            });
        }

        // Queue monitoring
        $this->app['queue']->after(function (JobProcessed $event) {
            if ($event->job->hasFailed()) {
                logger()->channel('discord')->error('Queue job failed', [
                    'connectionName' => $event->connectionName,
                    'job' => $event->job->getName(),
                ]);
                logger()->error('Queue job failed', [
                    'connectionName' => $event->connectionName,
                    'job' => $event->job->getName(),
                ]);
            }
        });

        // Log unhandled exceptions
        app(ExceptionHandler::class)->reportable(function (Throwable $e) {
            if (app()->isProduction()) {
                logger()->channel('discord')->critical('Unhandled Exception', [
                    'exception' => $e->getMessage(),
                    'stackTrace' => $e->getTraceAsString(),
                ]);
                logger()->critical('Unhandled Exception', [
                    'exception' => $e->getMessage(),
                    'stackTrace' => $e->getTraceAsString(),
                ]);
            }
        });

        // Cache and route optimizations for production
        if ($this->app->isProduction()) {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
        }

        //
    }
}

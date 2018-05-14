<?php

namespace App\Providers;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Jaeger\Config;
use Ramsey\Uuid\Uuid;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws \Exception
     */
    public function boot()
    {
        // Setup a unique ID for each request. This will allow us to find
        // the request trace in the jaeger ui later
        $this->app->instance('context.uuid', Uuid::uuid1());

        // Get the base config object
        $config = Config::getInstance();

        // If in development or testing, you can use this to change
        // the tracer to a mocked one (NoopTracer)
        //
        // if (!app()->environment('production')) {
        //     $config->setDisabled(true);
        // }

        // Start the tracer with a service name and the jaeger address
        $tracer = $config->initTrace('my-app', 'jaeger:6831');

        // Set the tracer as a singleton in the IOC container
        $this->app->instance('context.tracer', $tracer);

        // Start the global span, it'll wrap the request/console lifecycle
        $globalSpan = $tracer->startSpan('app');
        // Set the uuid as a tag for this trace
        $globalSpan->setTags(['uuid' => app('context.uuid')->toString()]);

        // If running in console (a.k.a a job or a command) set the
        // type tag accordingly
        $type = 'http';
        if (app()->runningInConsole()) {
            $type = 'console';
        }
        $globalSpan->setTags(['type' => $type]);

        // Save the global span as a singleton too
        $this->app->instance('context.tracer.globalSpan', $globalSpan);

        // When the app terminates we must finish the global span
        // and send the trace to the jaeger agent.
        app()->terminating(function () {
            app('context.tracer.globalSpan')->finish();
            app('context.tracer')->flush();
        });

        // Listen for each logged message and attach it to the global span
        Event::listen(MessageLogged::class, function (MessageLogged $e) {
            app('context.tracer.globalSpan')->log((array) $e);
        });

        // Listen for the request handled event and set more tags for the trace
        Event::listen(RequestHandled::class, function (RequestHandled $e) {
            app('context.tracer.globalSpan')->setTags([
                'user_id' => auth()->user()->id ?? "-",
                'company_id' => auth()->user()->company_id ?? "-",

                'request_host' => $e->request->getHost(),
                'request_path' => $path = $e->request->path(),
                'request_method' => $e->request->method(),

                'api' => str_contains($path, 'api'),
                'response_status' => $e->response->getStatusCode(),
                'error' => !$e->response->isSuccessful(),
            ]);
        });

        // Also listen for queries and log then,
        // it also receives the log in the MessageLogged event above
        DB::listen(function ($query) {
            Log::debug("[DB Query] {$query->connection->getName()}", [
                'query' => str_replace('"', "'", $query->sql),
                'time' => $query->time.'ms',
            ]);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

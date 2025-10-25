<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    public function boot(): void
    {
        // Listen when cron job starts
        Event::listen(ScheduledTaskStarting::class, function ($event) {
            $this->postStatus([
                'command' => $event->task->command ?? $event->task->description,
                'status' => 'running',
                'started_at' => now()->toDateTimeString(),
                'meta' => [
                    'expression' => $event->task->expression ?? null,
                    'timezone' => $event->task->timezone ?? null,
                ],
            ]);
        });

        // Listen when cron job finishes
        Event::listen(ScheduledTaskFinished::class, function ($event) {
            $this->postStatus([
                'command' => $event->task->command ?? $event->task->description,
                'status' => 'success',
                'finished_at' => now()->toDateTimeString(),
                'runtime' => $event->runtime ?? null,
            ]);
        });

        // Listen when cron job fails
        Event::listen(ScheduledTaskFailed::class, function ($event) {
            $this->postStatus([
                'command' => $event->task->command ?? $event->task->description,
                'status' => 'failed',
                'finished_at' => now()->toDateTimeString(),
                'error' => $event->exception->getMessage(),
            ]);
        });
    }

    private function postStatus(array $payload)
    {
        try {
            $url = env('TASKMANAGER_URL');
            $key = env('TASKMANAGER_API_KEY');

            if (!$url || !$key) {
                Log::warning('TaskManager URL or API key missing from .env');
                return;
            }

            $response = Http::withHeaders([
                'X-TASKMANAGER-KEY' => $key,
                'Accept' => 'application/json',
            ])->timeout(10)
              ->retry(2, 1000) // retry twice with 1-second delay
              ->post($url, $payload);

            if (!$response->successful()) {
                Log::warning("TaskManager response ({$response->status()}): " . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error("Failed to post scheduler status: {$e->getMessage()}");
        }
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

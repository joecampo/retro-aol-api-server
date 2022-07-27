<?php

namespace App\Providers;

use App\Events\LoggedOff;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Queue::after(function (JobProcessed $event) {
            if ($event->job->resolveName() === 'App\Jobs\Client') {
                $job = unserialize($event->job->payload()['data']['command']);

                if ($job->session->online) {
                    $job->session->update(['online' => false]);
                    cache()->tags($job->session->id)->flush();
                    LoggedOff::dispatch($job->session);
                }
            }
        });
    }
}

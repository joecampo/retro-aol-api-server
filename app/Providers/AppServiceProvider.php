<?php

namespace App\Providers;

use App\Events\LoggedOff;
use BackedEnum;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Stringable;
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
        Stringable::macro('matchFromPacket', function (BackedEnum $enum, int $number): ?Stringable {
            $regex = '/'.str_replace('*', '(.*)', $enum->value).'/';

            return with(preg_match_all($regex, $this->value, $output), function () use ($output, $number) {
                return $output[$number][0] ? str($output[$number][0]) : null;
            });
        });

        Queue::after(function (JobProcessed $event) {
            if ($event->job->resolveName() === 'App\Jobs\Client') {
                $job = unserialize($event->job->payload()['data']['command']);

                if ($job->session->online) {
                    $job->session->update(['online' => false]);
                    LoggedOff::dispatch($job->session);
                }
            }
        });
    }
}

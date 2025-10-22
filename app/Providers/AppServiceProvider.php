<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\EventAB;
use App\Models\EventABOpt;
use App\Models\EventAdd;
use App\Models\EventAddOpt;
use App\Models\EventHall;
use App\Models\EventHallOpt;
use App\Models\EventHotel;
use App\Models\EventHotelOpt;
use App\Models\EventTransport;
use App\Models\EventTransportOpt;
use App\Observers\GenericHistoryObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        Schema::defaultStringLength(191);
        Event::observe(GenericHistoryObserver::class);
        EventAB::observe(GenericHistoryObserver::class);
        EventABOpt::observe(GenericHistoryObserver::class);
        EventAdd::observe(GenericHistoryObserver::class);
        EventAddOpt::observe(GenericHistoryObserver::class);
        EventHall::observe(GenericHistoryObserver::class);
        EventHallOpt::observe(GenericHistoryObserver::class);
        EventHotel::observe(GenericHistoryObserver::class);
        EventHotelOpt::observe(GenericHistoryObserver::class);
        EventTransport::observe(GenericHistoryObserver::class);
        EventTransportOpt::observe(GenericHistoryObserver::class);
    }
}

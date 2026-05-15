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
use App\Domains\Events\Repositories\EventRepositoryInterface;
use App\Domains\Events\Repositories\EloquentEventRepository;
use App\Domains\Budgets\Repositories\ProviderBudgetRepositoryInterface;
use App\Domains\Budgets\Repositories\EloquentProviderBudgetRepository;
use App\Domains\Dashboard\Repositories\DashboardRepositoryInterface;
use App\Domains\Dashboard\Repositories\EloquentDashboardRepository;
use App\Domains\Hotels\Repositories\EventHotelRepositoryInterface;
use App\Domains\Hotels\Repositories\EloquentEventHotelRepository;
use App\Domains\FoodBeverage\Repositories\EventABRepositoryInterface;
use App\Domains\FoodBeverage\Repositories\EloquentEventABRepository;
use App\Domains\FoodBeverage\Repositories\EventABOptRepositoryInterface;
use App\Domains\FoodBeverage\Repositories\EloquentEventABOptRepository;
use App\Domains\Halls\Repositories\EventHallRepositoryInterface;
use App\Domains\Halls\Repositories\EloquentEventHallRepository;
use App\Domains\Halls\Repositories\EventHallOptRepositoryInterface;
use App\Domains\Halls\Repositories\EloquentEventHallOptRepository;
use App\Domains\Additions\Repositories\EventAddRepositoryInterface;
use App\Domains\Additions\Repositories\EloquentEventAddRepository;
use App\Domains\Additions\Repositories\EventAddOptRepositoryInterface;
use App\Domains\Additions\Repositories\EloquentEventAddOptRepository;
use App\Domains\Transports\Repositories\EventTransportRepositoryInterface;
use App\Domains\Transports\Repositories\EloquentEventTransportRepository;
use App\Domains\Transports\Repositories\EventTransportOptRepositoryInterface;
use App\Domains\Transports\Repositories\EloquentEventTransportOptRepository;
use App\Domains\Customers\Repositories\CustomerRepositoryInterface;
use App\Domains\Customers\Repositories\EloquentCustomerRepository;
use App\Domains\Providers\Repositories\ProviderRepositoryInterface;
use App\Domains\Providers\Repositories\EloquentProviderRepository;
use App\Domains\Hotels\Repositories\EventHotelOptRepositoryInterface;
use App\Domains\Hotels\Repositories\EloquentEventHotelOptRepository;
use App\Domains\Hotels\Repositories\AptoRepositoryInterface;
use App\Domains\Hotels\Repositories\EloquentAptoRepository;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use App\Domains\Auth\Repositories\EloquentUserRepository;
use App\Domains\Auth\Repositories\RoleRepositoryInterface;
use App\Domains\Auth\Repositories\EloquentRoleRepository;
use App\Domains\Shared\Repositories\LookupRepositoryInterface;
use App\Domains\Shared\Repositories\EloquentLookupRepository;
use App\Domains\Shared\Repositories\CityRepositoryInterface;
use App\Domains\Shared\Repositories\EloquentCityRepository;
use App\Domains\Shared\Repositories\StatusHistoryRepositoryInterface;
use App\Domains\Shared\Repositories\EloquentStatusHistoryRepository;
use App\Domains\Events\Services\EventServiceInterface;
use App\Domains\Events\Services\DefaultEventService;
use App\Domains\Budgets\Services\BudgetServiceInterface;
use App\Domains\Budgets\Services\DefaultBudgetService;
use App\Domains\Shared\Services\NotificationServiceInterface;
use App\Domains\Shared\Services\DefaultNotificationService;
use App\Domains\Providers\Services\ProviderServiceInterface;
use App\Domains\Providers\Services\DefaultProviderService;
use App\Domains\Auth\Services\AuthServiceInterface;
use App\Domains\Auth\Services\DefaultAuthService;
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
        $this->app->bind(EventRepositoryInterface::class, EloquentEventRepository::class);
        $this->app->bind(ProviderBudgetRepositoryInterface::class, EloquentProviderBudgetRepository::class);
        $this->app->bind(StatusHistoryRepositoryInterface::class, EloquentStatusHistoryRepository::class);
        $this->app->bind(ProposalHistoryRepositoryInterface::class, EloquentProposalHistoryRepository::class);
        $this->app->bind(EventHotelRepositoryInterface::class, EloquentEventHotelRepository::class);
        $this->app->bind(EventABRepositoryInterface::class, EloquentEventABRepository::class);
        $this->app->bind(EventABOptRepositoryInterface::class, EloquentEventABOptRepository::class);
        $this->app->bind(EventHallRepositoryInterface::class, EloquentEventHallRepository::class);
        $this->app->bind(EventHallOptRepositoryInterface::class, EloquentEventHallOptRepository::class);
        $this->app->bind(EventAddRepositoryInterface::class, EloquentEventAddRepository::class);
        $this->app->bind(EventAddOptRepositoryInterface::class, EloquentEventAddOptRepository::class);
        $this->app->bind(EventTransportRepositoryInterface::class, EloquentEventTransportRepository::class);
        $this->app->bind(EventTransportOptRepositoryInterface::class, EloquentEventTransportOptRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, EloquentCustomerRepository::class);
        $this->app->bind(ProviderRepositoryInterface::class, EloquentProviderRepository::class);
        $this->app->bind(LookupRepositoryInterface::class, EloquentLookupRepository::class);
        $this->app->bind(EventHotelOptRepositoryInterface::class, EloquentEventHotelOptRepository::class);
        $this->app->bind(AptoRepositoryInterface::class, EloquentAptoRepository::class);
        
        // Services
        $this->app->bind(EventServiceInterface::class, DefaultEventService::class);
        $this->app->bind(BudgetServiceInterface::class, DefaultBudgetService::class);
        $this->app->bind(NotificationServiceInterface::class, DefaultNotificationService::class);
        $this->app->bind(ProviderServiceInterface::class, DefaultProviderService::class);
        $this->app->bind(AuthServiceInterface::class, DefaultAuthService::class);

        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, EloquentRoleRepository::class);
        $this->app->bind(EventRepositoryInterface::class, EloquentEventRepository::class);
        $this->app->bind(StatusHistoryRepositoryInterface::class, EloquentStatusHistoryRepository::class);
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

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

// New Repositories and Services
use App\Domains\Shared\Repositories\BrokerRepositoryInterface;
use App\Domains\Shared\Repositories\EloquentBrokerRepository;
use App\Domains\Shared\Services\BrokerServiceInterface;
use App\Domains\Shared\Services\DefaultBrokerService;

use App\Domains\Shared\Repositories\ProposalHistoryRepositoryInterface;
use App\Domains\Shared\Repositories\EloquentProposalHistoryRepository;

use App\Domains\Shared\Repositories\CategoryRepositoryInterface;
use App\Domains\Shared\Repositories\EloquentCategoryRepository;
use App\Domains\Shared\Services\CategoryServiceInterface;
use App\Domains\Shared\Services\DefaultCategoryService;

use App\Domains\Shared\Repositories\PurposeRepositoryInterface;
use App\Domains\Shared\Repositories\EloquentPurposeRepository;
use App\Domains\Shared\Services\PurposeServiceInterface;
use App\Domains\Shared\Services\DefaultPurposeService;

use App\Domains\Shared\Repositories\ServiceTypeRepositoryInterface;
use App\Domains\Shared\Repositories\EloquentServiceTypeRepository;
use App\Domains\Shared\Services\ServiceTypeServiceInterface;
use App\Domains\Shared\Services\DefaultServiceTypeService;

use App\Domains\Shared\Repositories\RegimeRepositoryInterface;
use App\Domains\Shared\Repositories\EloquentRegimeRepository;
use App\Domains\Shared\Services\RegimeServiceInterface;
use App\Domains\Shared\Services\DefaultRegimeService;

use App\Domains\Shared\Services\CityServiceInterface;
use App\Domains\Shared\Services\DefaultCityService;

use App\Domains\Hotels\Services\AptoServiceInterface;
use App\Domains\Hotels\Services\DefaultAptoService;

use App\Domains\Shared\Repositories\ServiceRepositoryInterface;
use App\Domains\Shared\Repositories\EloquentServiceRepository;
use App\Domains\Shared\Services\ServiceServiceInterface;
use App\Domains\Shared\Services\DefaultServiceService;

use App\Domains\Shared\Repositories\LocalRepositoryInterface;
use App\Domains\Shared\Repositories\EloquentLocalRepository;
use App\Domains\Shared\Services\LocalServiceInterface;
use App\Domains\Shared\Services\DefaultLocalService;

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
        $this->app->bind(DashboardRepositoryInterface::class, EloquentDashboardRepository::class);

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

        // New Repositories
        $this->app->bind(BrokerRepositoryInterface::class, EloquentBrokerRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(PurposeRepositoryInterface::class, EloquentPurposeRepository::class);
        $this->app->bind(ServiceTypeRepositoryInterface::class, EloquentServiceTypeRepository::class);
        $this->app->bind(RegimeRepositoryInterface::class, EloquentRegimeRepository::class);
        $this->app->bind(CityRepositoryInterface::class, EloquentCityRepository::class);
        $this->app->bind(ProposalHistoryRepositoryInterface::class, EloquentProposalHistoryRepository::class);
        $this->app->bind(ServiceRepositoryInterface::class, EloquentServiceRepository::class);
        $this->app->bind(LocalRepositoryInterface::class, EloquentLocalRepository::class);

        // New Services
        $this->app->bind(BrokerServiceInterface::class, DefaultBrokerService::class);
        $this->app->bind(CategoryServiceInterface::class, DefaultCategoryService::class);
        $this->app->bind(PurposeServiceInterface::class, DefaultPurposeService::class);
        $this->app->bind(ServiceTypeServiceInterface::class, DefaultServiceTypeService::class);
        $this->app->bind(RegimeServiceInterface::class, DefaultRegimeService::class);
        $this->app->bind(CityServiceInterface::class, DefaultCityService::class);
        $this->app->bind(AptoServiceInterface::class, DefaultAptoService::class);
        $this->app->bind(ServiceServiceInterface::class, DefaultServiceService::class);
        $this->app->bind(LocalServiceInterface::class, DefaultLocalService::class);
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

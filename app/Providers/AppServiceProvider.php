<?php

namespace App\Providers;

use App\Actions\ValidateCartStock;
use App\Contract\CartServiceInterface;
use App\Services\PaymentMethodQueryService;
use App\Services\RegionQueryService;
use App\Services\SessionCartService;
use App\Services\ShippingMethodService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CartServiceInterface::class, SessionCartService::class);
        $this->app->bind(RegionQueryService::class, RegionQueryService::class);
        $this->app->bind(ShippingMethodService::class, ShippingMethodService::class);
        $this->app->bind(PaymentMethodQueryService::class, PaymentMethodQueryService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();
        Number::useCurrency('IDR');

        Gate::define('is_stock_available', function(User $user = null){
            try{
                ValidateCartStock::run();
                return true;
            }catch(ValidationException $e){
                session()->flash('error', $e->getMessage());
                return false;
            }
        });
    }
}

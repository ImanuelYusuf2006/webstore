<?php

declare(strict_types=1);

namespace App\Drivers\Shipping;

use App\Contract\ShippingDriverInterface;
use App\Data\CartData;
use App\Data\RegionData;
use App\Data\ShippingData;
use App\Data\ShippingServiceData;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\DataCollection;

class APIKurirShippingDriver implements ShippingDriverInterface
{
    
    public readonly string $driver;

    public function __construct()
    {
        $this->driver = 'apikurir';
    }

    /** @return DataCollection<ShippingServiceData> */

    public function getServices() : DataCollection
    {
        return ShippingServiceData::collect([
            [
                'driver' => $this->driver,
                'code' => 'jne-reguler',
                'courier' => 'JNE',
                'service' => 'Regular'
            ],
            [
                'driver' => $this->driver,
                'code' => 'jne-same-day',
                'courier' => 'JNE',
                'service' => 'Same Day'
            ],
            [
                'driver' => $this->driver,
                'code' => 'ninja-ecpress-reguler',
                'courier' => 'Ninja Xpress',
                'service' => 'Regular'
            ],
        ], DataCollection::class);
    }

    public function getRate(
        RegionData $origin,
        RegionData $destination,
        CartData $cart,
        ShippingServiceData $shipping_service,
    ) : ?ShippingData
    {
        try{
            $response = Http::timeout(7)
                ->withoutVerifying()
                ->withBasicAuth(config('shipping.api_kurir.username'),config('shipping.api_kurir.password'))
                ->post('https://sandbox.apikurir.id/shipments/v1/open-api/rates', [
                'isUseInsurance' => true,
                'isPickUp' => true,
                'isCod' => false,
                'weight' => $cart->total_weight,
                'packagePrice' => $cart->total,
                'origin' => ['postalCode' => $origin->postal_code],
                'destination' => ['postalCode' => $destination->postal_code],
                'logistics' => [$shipping_service->courier],
                'service' => [$shipping_service->service],
            ]);

            if($response->failed()) return null;

            $results = $response->json('data');


            $data = is_array($results) ? collect($results)->first() : null;

            if(! $data){
                return null;
            }

            $est = data_get($data, 'minDuration') . ' - ' . data_get($data, 'maxDuration') . ' ' . data_get($data, 'durationType');
            return new ShippingData(
                driver: $this->driver,
                courier: $shipping_service->courier,
                service: $shipping_service->service,
                estimated_delivery: $est,
                cost: (float) data_get($data, 'price'),
                weight: (int) data_get($data, 'weight'),
                origin: $origin,
                destination: $destination,
                logo_url: data_get($data, 'logoUrl')
            );
        } catch (\Exception $e){
            return null;
        }
    }
}
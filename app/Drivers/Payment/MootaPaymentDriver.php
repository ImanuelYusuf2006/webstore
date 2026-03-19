<?php

declare(strict_types=1);

namespace App\Drivers\Payment;

use App\Contract\PaymentDriverInterface;
use App\Data\PaymentData;
use App\Data\SalesOrderData;
use App\Data\SalesOrderItemData;
use App\Services\SalesOrderService;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\DataCollection;

class MootaPaymentDriver implements PaymentDriverInterface
{
    public readonly string $driver;

    public function __construct()
    {
        $this->driver = 'moota';
    }

    /** @return DataCollection<PaymentData> */
    public function getMethods() : DataCollection
    {
        $accounts = config('services.moota.accounts');

        if (empty($accounts)) {
            return PaymentData::collect([], DataCollection::class);
        }

        if (isset($accounts['driver'])) {
            $accounts = [$accounts];
        }

        return PaymentData::collect($accounts, DataCollection::class);
    }

    public function process(SalesOrderData $sales_order)
    {
        $response = Http::withToken(config('services.moota.access_token'))
            ->post('https://api.moota.co/api/v2/create-transaction', [
                'order_id'   => $sales_order->trx_id,
                'account_id' => data_get($sales_order->payment->payload, 'account_id'),
                'customer'   => [
                    'name'  => $sales_order->customer->full_name,
                    'email' => $sales_order->customer->email,
                    'phone' => $sales_order->customer->phone,
                ],
                'items' => $sales_order->items->toCollection()->map(function(SalesOrderItemData $item) {
                    return [
                        'name' => $item->name,
                        'qty'  => $item->quantity,
                        'price'=> $item->price
                    ];
                })->merge([
                    [
                        'name'  => $sales_order->shipping->courier,
                        'qty'   => 1,
                        'price' => $sales_order->shipping_cost
                    ]
                ])->toArray(),
                'redirect' => route('order-confirmed', $sales_order->trx_id),
                'total'    => $sales_order->total,
            ]);

        $currentPayload = (array) $sales_order->payment->payload;
        
        $newPayload = array_merge($currentPayload, [
            'moota_payload' => $response->json('data')
        ]);

        return app(SalesOrderService::class)->updatePaymentPayload($sales_order, $newPayload);
    }

    public function shouldShowPayNowButton(SalesOrderData $sales_order) : bool
    {
        // Tombol muncul jika belum ada data pembayaran dari Moota
        return true;
    }

    public function getRedirectUrl(SalesOrderData $sales_order) : ?string
    {
        // Mengambil link pembayaran yang sudah disimpan di database
        return data_get($sales_order->payment->payload, 'moota_payload.payment_url');
    }
}
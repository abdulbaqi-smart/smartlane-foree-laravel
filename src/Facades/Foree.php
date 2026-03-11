<?php

namespace smartlane\Foree\Facades;

use Illuminate\Support\Facades\Facade;
use smartlane\Foree\Services\ForeeBillService;

/**
 * @method static \smartlane\Foree\Models\ForeeBill createOrGetBill(\smartlane\Foree\Services\DTO\CreateBillRequest $req, ?string $idempotencyExtra = null)
 * @method static \smartlane\Foree\Models\ForeeBill refreshStatus(\smartlane\Foree\Models\ForeeBill $bill)
 *
 * @see ForeeBillService
 */
class Foree extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'foree';
    }
}

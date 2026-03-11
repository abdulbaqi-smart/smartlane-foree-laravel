<?php

namespace abdulbaqi-smart\Foree\Facades;

use Illuminate\Support\Facades\Facade;
use abdulbaqi-smart\Foree\Services\ForeeBillService;

/**
 * @method static \abdulbaqi-smart\Foree\Models\ForeeBill createOrGetBill(\abdulbaqi-smart\Foree\Services\DTO\CreateBillRequest $req, ?string $idempotencyExtra = null)
 * @method static \abdulbaqi-smart\Foree\Models\ForeeBill refreshStatus(\abdulbaqi-smart\Foree\Models\ForeeBill $bill)
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

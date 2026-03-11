<?php

namespace Yourvendor\Foree\Facades;

use Illuminate\Support\Facades\Facade;
use Yourvendor\Foree\Services\ForeeBillService;

/**
 * @method static \Yourvendor\Foree\Models\ForeeBill createOrGetBill(\Yourvendor\Foree\Services\DTO\CreateBillRequest $req, ?string $idempotencyExtra = null)
 * @method static \Yourvendor\Foree\Models\ForeeBill refreshStatus(\Yourvendor\Foree\Models\ForeeBill $bill)
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

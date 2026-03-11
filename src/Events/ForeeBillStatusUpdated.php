<?php

namespace abdulbaqi-smart\Foree\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use abdulbaqi-smart\Foree\Models\ForeeBill;

class ForeeBillStatusUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public ForeeBill $bill) {}
}

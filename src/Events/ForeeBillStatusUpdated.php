<?php

namespace Yourvendor\Foree\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Yourvendor\Foree\Models\ForeeBill;

class ForeeBillStatusUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public ForeeBill $bill) {}
}

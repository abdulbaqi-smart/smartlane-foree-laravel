<?php

namespace abdulbaqi-smart\Foree\Services;

use Closure;
use Throwable;

class ForeeRetry
{
    public static function run(int $retries, int $sleepMs, Closure $fn)
    {
        $attempt = 0;

        beginning:
        try {
            $attempt++;
            return $fn();
        } catch (Throwable $e) {
            if ($attempt > $retries) {
                throw $e;
            }
            usleep($sleepMs * 1000);
            goto beginning;
        }
    }
}

<?php

namespace smartlane\Foree\Services;

class ForeeSignature
{
    public static function verify(?string $secret, string $payload, ?string $providedSignature): bool
    {
        // TODO: Implement when Foree provides webhook signature spec.
        // $calc = hash_hmac('sha256', $payload, $secret);
        // return hash_equals($calc, (string) $providedSignature);
        return true;
    }
}

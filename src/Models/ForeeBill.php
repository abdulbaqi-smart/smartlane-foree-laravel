<?php

namespace smartlane\Foree\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ForeeBill extends Model
{
    use HasUuids;

    protected $table = 'foree_bills';

    protected $fillable = [
        'idempotency_key',
        'crn',
        'sub_biller_name',
        'consumer_name',
        'customer_email_address',
        'customer_phone_number',
        'amount',
        'late_amount',
        'due_at',
        'expiry_at',
        'crn_type',

        'foree_reference_number',
        'payment_link',
        'qr',
        'bill_status',
        'paid_amount',
        'transaction_ref_id',
        'transaction_at',
        'instrument_type',
        'instrument_institution',
        'instrument_number',
        'payment_channel',
        'initiator',
        'business_crn',

        'last_foree_response_code',
        'last_foree_raw',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'late_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_at'      => 'datetime',
        'expiry_at'   => 'datetime',
        'transaction_at'   => 'datetime',
        'last_foree_raw'   => 'array',
    ];

    public function isPaid(): bool
    {
        return strtolower((string) $this->bill_status) === 'paid';
    }
}

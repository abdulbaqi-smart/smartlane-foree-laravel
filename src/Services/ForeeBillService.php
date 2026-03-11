<?php

namespace smartlane\Foree\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use smartlane\Foree\Events\ForeeBillStatusUpdated;
use smartlane\Foree\Models\ForeeBill;
use smartlane\Foree\Services\DTO\CreateBillRequest;

class ForeeBillService
{
    public function __construct(private readonly ForeeClient $client) {}

    public function makeIdempotencyKey(
        string $crn,
        float $amount,
        Carbon $dueAt,
        Carbon $expiryAt,
        ?string $extra = null
    ): string {
        $base = implode('|', [
            trim($crn),
            number_format($amount, 2, '.', ''),
            $dueAt->toIso8601String(),
            $expiryAt->toIso8601String(),
            $extra ? trim($extra) : '',
        ]);

        return hash('sha256', $base);
    }

    /**
     * Create or return existing bill for same idempotency key.
     * Safe to call multiple times — will never create duplicates.
     */
    public function createOrGetBill(CreateBillRequest $req, ?string $idempotencyExtra = null): ForeeBill
    {
        $dueAt    = Carbon::createFromTimestamp($req->dueDate->getTimestamp());
        $expiryAt = Carbon::createFromTimestamp($req->expiryDate->getTimestamp());

        $idempotencyKey = $this->makeIdempotencyKey(
            $req->crn, $req->amount, $dueAt, $expiryAt, $idempotencyExtra
        );

        return DB::transaction(function () use ($req, $idempotencyKey, $dueAt, $expiryAt) {
            $existing = ForeeBill::where('idempotency_key', $idempotencyKey)->lockForUpdate()->first();

            if ($existing && $existing->foree_reference_number) {
                return $existing;
            }

            $bill = $existing ?? new ForeeBill([
                'id'                      => (string) Str::uuid(),
                'idempotency_key'         => $idempotencyKey,
                'crn'                     => $req->crn,
                'sub_biller_name'         => $req->subBillerName,
                'consumer_name'           => $req->consumerName,
                'customer_email_address'  => $req->customerEmailAddress,
                'customer_phone_number'   => $req->customerPhoneNumber,
                'amount'                  => $req->amount,
                'late_amount'             => $req->lateAmount,
                'due_at'                  => $dueAt,
                'expiry_at'               => $expiryAt,
                'crn_type'                => $req->crnType,
            ]);

            $bill->save();

            $res = $this->client->createBill($req);

            $bill->last_foree_response_code = $res->responseCode;
            $bill->last_foree_raw           = $res->raw;

            if ($res->ok()) {
                $data = $res->responseData;
                $bill->foree_reference_number = $data['reference_number'] ?? $data['PSID'] ?? $bill->foree_reference_number;
                $bill->payment_link           = $data['payment_link']     ?? $bill->payment_link;
                $bill->qr                     = $data['qr']               ?? $bill->qr;
                $bill->bill_status            = $data['bill_status']       ?? $bill->bill_status;
            }

            $bill->save();

            event(new ForeeBillStatusUpdated($bill));

            return $bill;
        });
    }

    /**
     * Pull latest status from Foree (manual refresh / reconciliation).
     */
    public function refreshStatus(ForeeBill $bill): ForeeBill
    {
        if (!$bill->foree_reference_number) {
            return $bill;
        }

        $res = $this->client->billStatus($bill->foree_reference_number);

        $bill->last_foree_response_code = $res->responseCode;
        $bill->last_foree_raw           = $res->raw;

        if ($res->ok()) {
            $b = $res->bill();

            $bill->bill_status          = $b['bill_status']                       ?? $bill->bill_status;
            $bill->payment_link         = $b['payment_link']                      ?? $bill->payment_link;
            $bill->business_crn         = $b['business_crn'] ?? $b['buisness_crn'] ?? $bill->business_crn;

            if (isset($b['paid_amount']))        $bill->paid_amount        = (float) $b['paid_amount'];
            if (!empty($b['transaction_ref_id'])) $bill->transaction_ref_id = $b['transaction_ref_id'];
            if (!empty($b['transaction_date_time'])) {
                $bill->transaction_at = Carbon::parse($b['transaction_date_time']);
            }

            $bill->instrument_type        = $b['instrument_type']        ?? $bill->instrument_type;
            $bill->instrument_institution = $b['instrument_institution'] ?? $bill->instrument_institution;
            $bill->instrument_number      = $b['instrument_number']      ?? $bill->instrument_number;
            $bill->payment_channel        = $b['payment_channel']        ?? $bill->payment_channel;
            $bill->initiator              = $b['initiator']              ?? $bill->initiator;
        }

        $bill->save();

        event(new ForeeBillStatusUpdated($bill));

        return $bill;
    }
}

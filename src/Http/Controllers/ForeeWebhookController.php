<?php

namespace smartlane\Foree\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use smartlane\Foree\Events\ForeeBillPaid;
use smartlane\Foree\Events\ForeeBillStatusUpdated;
use smartlane\Foree\Http\Requests\ForeeWebhookRequest;
use smartlane\Foree\Models\ForeeBill;

class ForeeWebhookController extends Controller
{
    public function handle(ForeeWebhookRequest $request): JsonResponse
    {
        $payload   = $request->validated();
        $b         = $payload['response_data']['bill'];
        $reference = $b['reference_number'];
        $newStatus = $b['bill_status'];

        DB::transaction(function () use ($b, $reference, $newStatus) {
            /** @var ForeeBill|null $bill */
            $bill = ForeeBill::where('foree_reference_number', $reference)->lockForUpdate()->first();

            if (!$bill) {
                Log::warning('Foree webhook: bill not found', [
                    'reference_number' => $reference,
                    'payload'          => $b,
                ]);
                return;
            }

            $oldStatus  = $bill->bill_status;
            $incomingTxn = $b['transaction_ref_id'] ?? null;

            // Idempotency — skip duplicate webhook deliveries
            if ($incomingTxn && $bill->transaction_ref_id === $incomingTxn && $oldStatus === $newStatus) {
                return;
            }

            $bill->bill_status   = $newStatus;
            $bill->payment_link  = $b['payment_link']  ?? $bill->payment_link;
            $bill->business_crn  = $b['buisness_crn']  ?? $bill->business_crn;

            if (isset($b['paid_amount']))         $bill->paid_amount        = (float) $b['paid_amount'];
            if (!empty($incomingTxn))             $bill->transaction_ref_id = $incomingTxn;
            if (!empty($b['transaction_date_time'])) {
                $bill->transaction_at = Carbon::parse($b['transaction_date_time']);
            }

            $bill->instrument_type        = $b['instrument_type']        ?? $bill->instrument_type;
            $bill->instrument_institution = $b['instrument_institution'] ?? $bill->instrument_institution;
            $bill->instrument_number      = $b['instrument_number']      ?? $bill->instrument_number;
            $bill->payment_channel        = $b['payment_channel']        ?? $bill->payment_channel;
            $bill->initiator              = $b['initiator']              ?? $bill->initiator;

            $bill->save();

            event(new ForeeBillStatusUpdated($bill));

            if (strtolower((string) $oldStatus) !== 'paid' && strtolower((string) $newStatus) === 'paid') {
                event(new ForeeBillPaid($bill));
            }
        });

        return response()->json([
            'code'     => '00',
            'status'   => true,
            'order_id' => $reference,
            'message'  => 'Payment Status Updated',
        ]);
    }
}

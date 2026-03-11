<?php

namespace Yourvendor\Foree\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForeeWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        $allow = config('foree.webhook_ip_allowlist', []);

        if (!empty($allow)) {
            return in_array($this->ip(), $allow, true);
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'response_data'                          => ['required', 'array'],
            'response_data.bill'                     => ['required', 'array'],
            'response_data.bill.reference_number'    => ['required', 'string'],
            'response_data.bill.bill_status'         => ['required', 'string'],
            'response_data.bill.buisness_crn'        => ['nullable', 'string'], // intentional Foree typo
            'response_data.bill.paid_amount'         => ['nullable'],
            'response_data.bill.transaction_ref_id'  => ['nullable', 'string'],
            'response_data.bill.transaction_date_time' => ['nullable', 'string'],
            'response_data.bill.instrument_type'     => ['nullable', 'string'],
            'response_data.bill.instrument_institution' => ['nullable', 'string'],
            'response_data.bill.instrument_number'   => ['nullable', 'string'],
            'response_data.bill.payment_channel'     => ['nullable', 'string'],
            'response_data.bill.payment_link'        => ['nullable', 'string'],
            'response_data.bill.initiator'           => ['nullable', 'string'],
        ];
    }
}

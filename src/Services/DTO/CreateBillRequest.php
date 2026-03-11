<?php

namespace Yourvendor\Foree\Services\DTO;

use Carbon\CarbonInterface;

class CreateBillRequest
{
    public function __construct(
        public string $crn,
        public ?string $subBillerName,
        public string $consumerName,
        public ?string $customerEmailAddress,
        public ?string $customerPhoneNumber,
        public float $amount,
        public float $lateAmount,
        public CarbonInterface $dueDate,
        public CarbonInterface $expiryDate,
        public ?string $crnType = null, // e.g. "Fixed CRN - Topup"
    ) {}

    public function toArray(): array
    {
        $payload = [
            'crn'                    => $this->crn,
            'sub_biller_name'        => $this->subBillerName,
            'consumer_name'          => $this->consumerName,
            'customer_email_address' => $this->customerEmailAddress,
            'customer_phone_number'  => $this->customerPhoneNumber,
            'amount'                 => round($this->amount, 2),
            'late_amount'            => round($this->lateAmount, 2),
            'due_date'               => (int) ($this->dueDate->getTimestamp() * 1000),    // epoch ms
            'expiry_date'            => (int) ($this->expiryDate->getTimestamp() * 1000), // epoch ms
        ];

        if (!empty($this->crnType)) {
            $payload['crn_type'] = $this->crnType;
        }

        // Remove nulls — Foree API rejects null fields
        return array_filter($payload, fn($v) => $v !== null && $v !== '');
    }
}

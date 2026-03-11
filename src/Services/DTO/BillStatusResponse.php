<?php

namespace smartlane\Foree\Services\DTO;

class BillStatusResponse extends ForeeResponse
{
    public function bill(): array
    {
        return $this->responseData['bill'] ?? $this->responseData;
    }

    public function billStatus(): ?string
    {
        return $this->bill()['bill_status'] ?? null;
    }

    public function referenceNumber(): ?string
    {
        return $this->bill()['reference_number'] ?? null;
    }

    public function paymentLink(): ?string
    {
        return $this->bill()['payment_link'] ?? null;
    }

    public function businessCrn(): ?string
    {
        $b = $this->bill();
        return $b['business_crn'] ?? $b['buisness_crn'] ?? null;
    }
}

<?php

namespace smartlane\Foree\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use smartlane\Foree\Services\DTO\BillStatusResponse;
use smartlane\Foree\Services\DTO\CreateBillRequest;
use smartlane\Foree\Services\DTO\ForeeResponse;

class ForeeClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
        private readonly int $timeoutSeconds,
        private readonly int $retries,
        private readonly int $retryMs,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            baseUrl:        rtrim((string) config('foree.base_url'), '/'),
            apiKey:         (string) config('foree.api_key'),
            timeoutSeconds: (int) config('foree.timeout', 20),
            retries:        (int) config('foree.retries', 2),
            retryMs:        (int) config('foree.retry_ms', 300),
        );
    }

    private function http()
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeoutSeconds)
            ->acceptJson()
            ->asJson()
            ->withHeaders(['X-API-KEY' => $this->apiKey]);
    }

    /**
     * Create Bill — POST /business/import_bill
     */
    public function createBill(CreateBillRequest $req): ForeeResponse
    {
        try {
            $resp = ForeeRetry::run($this->retries, $this->retryMs, function () use ($req) {
                return $this->http()->post('/business/import_bill', $req->toArray())->throw();
            });

            $json = $resp->json() ?? [];

            return new ForeeResponse(
                responseCode: (int) ($json['response_code'] ?? -1),
                responseData: (array) ($json['response_data'] ?? []),
                raw: $json
            );
        } catch (ConnectionException $e) {
            return new ForeeResponse(-1, ['error' => 'connection_error', 'message' => $e->getMessage()]);
        } catch (RequestException $e) {
            $json = $e->response?->json() ?? [];
            return new ForeeResponse(
                (int) ($json['response_code'] ?? -1),
                (array) ($json['response_data'] ?? ['error' => 'http_error']),
                $json ?: ['error' => 'http_error', 'message' => $e->getMessage()]
            );
        }
    }

    /**
     * Bill Inquiry — POST /checkout/bill_status
     */
    public function billStatus(string $referenceNumber): BillStatusResponse
    {
        try {
            $resp = ForeeRetry::run($this->retries, $this->retryMs, function () use ($referenceNumber) {
                return $this->http()->post('/checkout/bill_status', [
                    'reference_number' => $referenceNumber,
                ])->throw();
            });

            $json = $resp->json() ?? [];

            return new BillStatusResponse(
                responseCode: (int) ($json['response_code'] ?? -1),
                responseData: (array) ($json['response_data'] ?? []),
                raw: $json
            );
        } catch (ConnectionException $e) {
            return new BillStatusResponse(-1, ['error' => 'connection_error', 'message' => $e->getMessage()]);
        } catch (RequestException $e) {
            $json = $e->response?->json() ?? [];
            return new BillStatusResponse(
                (int) ($json['response_code'] ?? -1),
                (array) ($json['response_data'] ?? ['error' => 'http_error']),
                $json ?: ['error' => 'http_error', 'message' => $e->getMessage()]
            );
        }
    }
}

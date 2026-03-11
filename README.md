<<<<<<< HEAD
# smartlane-foree-laravel
=======
# Foree Laravel Package

A proper Composer package for the [Foree](https://foree.co) Bill Payment Gateway.  
Install once, use in **any Laravel project** — no copy-pasting files.

---

## Installation

### Option A — From GitHub (Recommended)

Push this package folder to a GitHub repo (e.g. `github.com/abdulbaqi-smart/foree-laravel`), then in each Laravel project:

**1. Add repository to `composer.json`:**
```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/abdulbaqi-smart/foree-laravel"
    }
],
"require": {
    "abdulbaqi-smart/foree-laravel": "^1.0"
}
```

**2. Install:**
```bash
composer require abdulbaqi-smart/foree-laravel
```

---

### Option B — Local Path (During Development)

If the package is on your local machine:

```json
"repositories": [
    {
        "type": "path",
        "url": "../foree-laravel"
    }
],
"require": {
    "abdulbaqi-smart/foree-laravel": "*"
}
```
```bash
composer require abdulbaqi-smart/foree-laravel
```

---

### Option C — Packagist (Public)

If you publish to [packagist.org](https://packagist.org), then simply:
```bash
composer require abdulbaqi-smart/foree-laravel
```
No repository entry needed.

---

## Setup (Do this in each project)

### 1. Publish config
```bash
php artisan vendor:publish --tag=foree-config
```

### 2. Add to `.env`
```env
FOREE_BASE_URL=https://api-sandbox.foreebill.com
FOREE_API_KEY=your_project_specific_api_key

FOREE_TIMEOUT=20
FOREE_RETRIES=2
FOREE_RETRY_MS=300

# Optional: restrict webhook to Foree IPs
FOREE_WEBHOOK_IP_ALLOWLIST=1.2.3.4,5.6.7.8
```

### 3. Run migration
```bash
php artisan migrate
```
> Migration runs automatically. The `foree_bills` table will be created.

### 4. Exclude webhook from CSRF

In `app/Http/Middleware/VerifyCsrfToken.php`:
```php
protected $except = [
    'api/webhooks/foree/bill',
];
```

### 5. Share webhook URL with Foree

Give Foree this URL:
```
https://yourproject.com/api/webhooks/foree/bill
```

---

## Usage

### Create a Bill
```php
use abdulbaqi-smart\Foree\Services\DTO\CreateBillRequest;
use abdulbaqi-smart\Foree\Services\ForeeBillService;
use Carbon\Carbon;

$bill = app(ForeeBillService::class)->createOrGetBill(
    new CreateBillRequest(
        crn:                   'ORDER-1001',
        subBillerName:         'Till-01',
        consumerName:          'Ahmed Khan',
        customerEmailAddress:  'ahmed@example.com',
        customerPhoneNumber:   '03001234567',
        amount:                1500.00,
        lateAmount:            1600.00,
        dueDate:               Carbon::now()->addDays(3),
        expiryDate:            Carbon::now()->addDays(10),
    ),
    idempotencyExtra: 'INV-1001'  // your invoice/order ID — prevents duplicates
);

$bill->payment_link;            // send to customer
$bill->foree_reference_number;  // PSID
$bill->qr;                      // QR code data
```

Or using the Facade:
```php
use abdulbaqi-smart\Foree\Facades\Foree;

$bill = Foree::createOrGetBill($request, 'INV-1001');
```

### Check Bill Status (Manual Refresh)
```php
$bill = \abdulbaqi-smart\Foree\Models\ForeeBill::find($id);
app(ForeeBillService::class)->refreshStatus($bill);
```

### Top-up Bill
```php
new CreateBillRequest(
    // ... same fields ...
    crnType: 'Fixed CRN - Topup',
)
```

---

## Listen for Payment Events

In `app/Providers/EventServiceProvider.php`:
```php
use abdulbaqi-smart\Foree\Events\ForeeBillPaid;
use abdulbaqi-smart\Foree\Events\ForeeBillStatusUpdated;

protected $listen = [
    ForeeBillPaid::class => [
        \App\Listeners\MarkInvoicePaid::class,
    ],
    ForeeBillStatusUpdated::class => [
        \App\Listeners\LogBillStatusChange::class,
    ],
];
```

Your listener:
```php
class MarkInvoicePaid
{
    public function handle(ForeeBillPaid $event): void
    {
        $bill = $event->bill;
        Order::where('crn', $bill->crn)->update(['status' => 'paid']);
    }
}
```

---

## Multiple Projects — Summary

| | Project A | Project B | Project C |
|---|---|---|---|
| Install | `composer require` | `composer require` | `composer require` |
| `FOREE_API_KEY` | key_for_A | key_for_B | key_for_C |
| `FOREE_BASE_URL` | sandbox | production | sandbox |
| Webhook URL | projectA.com/api/... | projectB.com/api/... | projectC.com/api/... |

Same package, each project has its own `.env` = fully independent. ✅

---

## Package Structure

```
foree-laravel/
├── composer.json
├── config/
│   └── foree.php
├── database/
│   └── migrations/
│       └── 2026_01_21_000001_create_foree_bills_table.php
├── routes/
│   └── foree.php
└── src/
    ├── ForeeServiceProvider.php
    ├── Facades/
    │   └── Foree.php
    ├── Events/
    │   ├── ForeeBillPaid.php
    │   └── ForeeBillStatusUpdated.php
    ├── Models/
    │   └── ForeeBill.php
    ├── Http/
    │   ├── Controllers/
    │   │   └── ForeeWebhookController.php
    │   └── Requests/
    │       └── ForeeWebhookRequest.php
    └── Services/
        ├── ForeeClient.php
        ├── ForeeBillService.php
        ├── ForeeRetry.php
        ├── ForeeSignature.php
        └── DTO/
            ├── CreateBillRequest.php
            ├── ForeeResponse.php
            └── BillStatusResponse.php
```

---

## Requirements
- PHP 8.1+
- Laravel 10, 11, or 12
>>>>>>> 37c1088 (Initial Foree Laravel package)

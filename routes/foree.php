<?php

use Illuminate\Support\Facades\Route;
use Yourvendor\Foree\Http\Controllers\ForeeWebhookController;

/*
|--------------------------------------------------------------------------
| Foree Webhook Route (auto-registered by package)
|--------------------------------------------------------------------------
| Foree will POST to: https://yourproject.com/api/webhooks/foree/bill
|
| Exclude from CSRF in your app:
|   app/Http/Middleware/VerifyCsrfToken.php → $except = ['api/webhooks/foree/bill']
*/

Route::post('/api/webhooks/foree/bill', [ForeeWebhookController::class, 'handle'])
    ->name('foree.webhook');

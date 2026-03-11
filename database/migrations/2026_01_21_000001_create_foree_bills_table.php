<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('foree_bills', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('idempotency_key', 120)->unique();

            // Request fields
            $table->string('crn', 64)->index();
            $table->string('sub_biller_name', 100)->nullable();
            $table->string('consumer_name', 150);
            $table->string('customer_email_address', 150)->nullable();
            $table->string('customer_phone_number', 30)->nullable();
            $table->decimal('amount', 18, 2);
            $table->decimal('late_amount', 18, 2)->default(0);
            $table->timestampTz('due_at')->nullable();
            $table->timestampTz('expiry_at')->nullable();
            $table->string('crn_type', 60)->nullable();

            // Foree response fields
            $table->string('foree_reference_number', 80)->nullable()->unique();
            $table->string('payment_link')->nullable();
            $table->longText('qr')->nullable();
            $table->string('bill_status', 40)->nullable()->index();

            // Payment fields (from inquiry / webhook)
            $table->decimal('paid_amount', 18, 2)->nullable();
            $table->string('transaction_ref_id', 120)->nullable()->index();
            $table->timestampTz('transaction_at')->nullable();
            $table->string('instrument_type', 60)->nullable();
            $table->string('instrument_institution', 120)->nullable();
            $table->string('instrument_number', 80)->nullable();
            $table->string('payment_channel', 80)->nullable();
            $table->string('initiator', 60)->nullable();
            $table->string('business_crn', 80)->nullable()->index();

            // Observability
            $table->integer('last_foree_response_code')->nullable();
            $table->json('last_foree_raw')->nullable();

            $table->timestampsTz();
            $table->index(['crn', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foree_bills');
    }
};

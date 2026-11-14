<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registra los pagos recibidos desde Recurrente.
     */
    public function up(): void
    {
        Schema::create('recurrente_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('recurrente');
            $table->string('payment_key');
            $table->string('svix_id')->nullable()->unique();
            $table->string('intent_id')->nullable();
            $table->string('legacy_id')->nullable();
            $table->string('event_type')->nullable();
            $table->string('status')->nullable();
            $table->string('raw_status')->nullable();
            $table->unsignedBigInteger('receipt_number')->nullable();
            $table->unsignedBigInteger('amount_in_cents')->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('checkout_id')->nullable();
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->foreignId('participant_id')->nullable()->constrained('participants')->nullOnDelete();
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'payment_key']);
            $table->index('receipt_number');
            $table->index('customer_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurrente_webhook_events');
    }
};

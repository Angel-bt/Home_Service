<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('plan_expires_at')->nullable()->after('subscription_plan');
        });

        Schema::create('subscription_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('method', 20);
            $table->decimal('amount', 10, 2);
            $table->unsignedSmallInteger('periods')->default(1);
            $table->string('status', 20)->default('pending');
            $table->string('reference', 100)->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('client_transaction_id', 50)->nullable()->unique();
            $table->string('gateway_transaction_id', 50)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('paid_until')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('plan_expires_at');
        });
    }
};

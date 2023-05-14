<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');

            $table->string('asaas_id', 30)->nullable();
            $table->string('customer', 30)->nullable();
            $table->string('billing_type', 30)->nullable();
            $table->date('due_date')->nullable();
            $table->float('value')->nullable();
            $table->integer('installment')->nullable();
            $table->string('installment_token', 50)->nullable();
            $table->string('description')->nullable();
            $table->string('bank_slip_url')->nullable();
            $table->string('status', 30)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}

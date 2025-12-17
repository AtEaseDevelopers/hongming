<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMachineRentalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('machine_rental', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained(); 
            $table->string('delivery_order_number')->unique(); // Better name than MDO_number
            $table->foreignId('customer_id')->constrained(); 
            $table->date('date'); 
            $table->string('issued_by')->nullable();
            $table->decimal('total_amount', 10, 2);

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
        Schema::dropIfExists('machine_rental');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMachineRentalItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('machine_rental_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_rental_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained(); 
            $table->string('description')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 8, 2);
            $table->decimal('amount', 10, 2);

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
        Schema::dropIfExists('machine_rental_items');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->date('date'); 
            $table->string('dono'); 
            $table->string('place_name')->max(255); 
            $table->string('place_address')->max(255);
            $table->decimal('place_latitude', 10, 7); 
            $table->decimal('place_longitude', 10, 7); 
            $table->foreignId('product_id')->constrained();
            $table->foreignId('company_id')->constrained(); 
            $table->string('total_order')->max(255);
            $table->string('this_load')->max(255); 
            $table->string('progress_total')->max(255); 
            $table->string('strength_at')->nullable()->max(255);
            $table->string('slump')->nullable()->max(255); 
            $table->string('remark')->nullable()->max(255); 
            $table->tinyInteger('status')->in([0, 1, 2, 3, 4, 5]); 

            // Additional timing fields
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->time('time_taken')->nullable();

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
        Schema::dropIfExists('delivery_orders');
    }
}

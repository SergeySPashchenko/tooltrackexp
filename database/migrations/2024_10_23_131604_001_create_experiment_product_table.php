<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('experiment_product', function (Blueprint $table) {
            $table->bigInteger('product_id')->unsigned();
            $table->bigInteger('experiment_id')->unsigned();

            $table
                ->foreign('product_id')
                ->references('id')
                ->on('product')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table
                ->foreign('experiment_id')
                ->references('id')
                ->on('experiments')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('experiment_product');
    }
};

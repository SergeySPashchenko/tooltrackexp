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
        Schema::create('experiments', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->string('original_url')->nullable();
            $table->string('variant_url')->nullable();
            $table->integer('original_visitors')->default(0);
            $table->integer('variant_visitors')->default(0);
            $table->integer('original_conversions')->default(0);
            $table->integer('variant_conversions')->default(0);
            $table->decimal('original_avg_order_value')->nullable();
            $table->decimal('variant_avg_order_value')->nullable();
            $table->decimal('original_revenue')->nullable();
            $table->decimal('variant_revenue')->nullable();
            $table->decimal('confidence_level')->nullable();
            $table->bigInteger('experiment_group_id')->unsigned();
            $table->bigInteger('product_id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table
                ->foreign('experiment_group_id')
                ->references('id')
                ->on('experiment_groups')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table
                ->foreign('product_id')
                ->references('id')
                ->on('product')
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
        Schema::dropIfExists('experiments');
    }
};

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
        Schema::create('product', function (Blueprint $table) {
            $table->id();
            $table->string('Product', 255);
            $table->string('ProductName', 255)->nullable();
            $table->string('ProductPage', 255)->nullable();
            $table->string('site_code', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('product1', 255)->nullable();
            $table->string('product2', 255)->nullable();
            $table->string('product3', 255)->nullable();
            $table->mediumText('emailTemplate')->nullable();
            $table->string('emailSubject', 255)->nullable();
            $table->string('emailFrom', 255)->nullable();
            $table
                ->integer('newSystem', false, true)
                ->nullable();
            $table
                ->integer('Visible', false, true)
                ->nullable();
            $table->string('abandon_list', 255)->nullable();
            $table->string('buyer_list', 255)->nullable();
            $table->string('newsletter_list', 255)->nullable();
            $table->string('SendyURL', 255)->nullable();
            $table->string('TrackingCode', 999)->nullable();
            $table->string('GoalCode', 999)->nullable();
            $table->string('RemarketingCode', 999)->nullable();
            $table
                ->string('ffnote', 255)
                ->nullable();
            $table->string('Brand', 255)->nullable();
            $table->text('amazon_link')->nullable();
            $table->string('visual_name', 255)->nullable();
            $table->string('flyer', 255)->nullable();
            $table->mediumText('thank_announcement')->nullable();
            $table->mediumText('thank_announcement_bottom')->nullable();
            $table->bigInteger('main_category_id')->unsigned()->nullable();
            $table->bigInteger('marketing_category_id')->unsigned()->nullable();
            $table->bigInteger('gender_id')->unsigned()->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table
                ->foreign('main_category_id')
                ->references('category_id')
                ->on('category')
                ->onUpdate('cascade');
            $table
                ->foreign('marketing_category_id')
                ->references('category_id')
                ->on('category')
                ->onUpdate('cascade');
            $table
                ->foreign('gender_id')
                ->references('gender_id')
                ->on('gender')
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
        Schema::dropIfExists('product');
    }
};

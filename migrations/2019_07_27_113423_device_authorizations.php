<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DeviceAuthorizations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_authorizations', function ($table) {
            $table->string('uuid')->primary();
            $table->text('fingerprint');
            $table->string('browser');
            $table->string('ip');
            $table->integer('user_id');
            $table->string('verify_token');
            $table->timestamp('verified_at')->nullable();
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
        Schema::dropIfExists('device_authorizations');
    }
}

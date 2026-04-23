<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timeseries', function (Blueprint $table): void {
            $table->unsignedInteger('ts_account_id');
            $table->timestamp('ts_timestamp');
            $table->json('ts_data');

            $table->primary(['ts_account_id', 'ts_timestamp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeseries');
    }
};

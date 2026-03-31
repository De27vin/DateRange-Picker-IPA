<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timeseries_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->timestamp('ts_utc');
            $table->json('data');
            $table->timestamps();

            $table->unique(['account_id', 'ts_utc']);
            $table->index(['account_id', 'ts_utc']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeseries_snapshots');
    }
};

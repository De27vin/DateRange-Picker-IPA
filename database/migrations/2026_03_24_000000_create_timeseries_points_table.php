<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timeseries_points', function (Blueprint $table): void {
            $table->id();
            $table->string('chart', 64);
            $table->timestamp('ts_utc');
            $table->unsignedTinyInteger('value');
            $table->timestamps();

            $table->unique(['chart', 'ts_utc']);
            $table->index(['chart', 'ts_utc', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeseries_points');
    }
};

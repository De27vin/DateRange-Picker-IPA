<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
create table timeseries
(
    ts_account_id int                                   not null,
    ts_timestamp  datetime                              not null,
    ts_data       longtext collate utf8mb4_bin          not null
        check (json_valid(`ts_data`)),
    primary key (ts_account_id, ts_timestamp)
)
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('timeseries');
    }
};

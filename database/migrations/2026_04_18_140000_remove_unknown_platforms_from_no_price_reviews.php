<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('no_price_reviews')) {
            return;
        }

        $known = array_keys(config('igdb.all_platforms', []));

        if (empty($known)) {
            return;
        }

        DB::table('no_price_reviews')
            ->whereNotIn('platform_id', $known)
            ->delete();
    }

    public function down(): void {}
};

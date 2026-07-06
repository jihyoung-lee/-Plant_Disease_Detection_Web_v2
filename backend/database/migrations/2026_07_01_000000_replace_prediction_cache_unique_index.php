<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SINGLE_INDEX = 'prediction_caches_hashname_unique';
    private const COMPOSITE_INDEX = 'prediction_caches_hashname_crop_name_unique';

    public function up(): void
    {
        if ($this->indexExists(self::SINGLE_INDEX)) {
            Schema::table('prediction_caches', function (Blueprint $table) {
                $table->dropUnique(self::SINGLE_INDEX);
            });
        }

        if (!$this->indexExists(self::COMPOSITE_INDEX)) {
            Schema::table('prediction_caches', function (Blueprint $table) {
                $table->unique(['hashname', 'crop_name']);
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists(self::COMPOSITE_INDEX)) {
            Schema::table('prediction_caches', function (Blueprint $table) {
                $table->dropUnique(self::COMPOSITE_INDEX);
            });
        }

        if (!$this->indexExists(self::SINGLE_INDEX)) {
            Schema::table('prediction_caches', function (Blueprint $table) {
                $table->unique('hashname');
            });
        }
    }

    private function indexExists(string $indexName): bool
    {
        foreach (DB::select('SHOW INDEX FROM `prediction_caches`') as $index) {
            if (($index->Key_name ?? null) === $indexName) {
                return true;
            }
        }

        return false;
    }
};

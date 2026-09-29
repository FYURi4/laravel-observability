<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observability_traces', function (Blueprint $table) {
            $table->index('created_at', 'obs_traces_created_idx');
        });

        Schema::table('observability_spans', function (Blueprint $table) {
            $table->index('trace_id', 'obs_spans_trace_idx');
            $table->index('created_at', 'obs_spans_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('observability_traces', function (Blueprint $table) {
            $table->dropIndex('obs_traces_created_idx');
        });

        Schema::table('observability_spans', function (Blueprint $table) {
            $table->dropIndex('obs_spans_trace_idx');
            $table->dropIndex('obs_spans_created_idx');
        });
    }
};

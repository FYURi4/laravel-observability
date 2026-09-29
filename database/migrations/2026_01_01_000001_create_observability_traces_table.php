<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observability_traces', function (Blueprint $table) {
            $table->id();
            $table->uuid('trace_id')->unique();
            $table->string('method', 10);
            $table->string('path');
            $table->unsignedSmallInteger('status');
            $table->decimal('duration_ms', 12, 3);
            $table->unsignedInteger('spans_count')->default(0);
            $table->timestamp('created_at')->index();

            $table->index('path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observability_traces');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observability_spans', function (Blueprint $table) {
            $table->id();
            $table->uuid('trace_id')->index();
            $table->string('name', 50);
            $table->text('sql')->nullable();
            $table->text('bindings')->nullable();
            $table->decimal('duration_ms', 12, 3)->nullable();
            $table->boolean('is_slow')->default(false);
            $table->text('message')->nullable();
            $table->string('class')->nullable();
            $table->string('file')->nullable();
            $table->unsignedInteger('line')->nullable();
            $table->longText('trace')->nullable();
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observability_spans');
    }
};

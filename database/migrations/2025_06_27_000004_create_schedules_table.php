<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // 0 = Domingo, 6 = Sábado
            $table->tinyInteger('day_of_week');

            $table->time('opens_at');
            $table->time('closes_at');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Permite varios horarios por día (mañana y tarde)
            $table->unique([
                'tenant_id',
                'user_id',
                'day_of_week',
                'opens_at'
            ]);

            $table->index([
                'tenant_id',
                'is_active'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
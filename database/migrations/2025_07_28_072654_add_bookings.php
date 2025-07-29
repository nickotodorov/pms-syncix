<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'bookings';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('external_id')->nullable();
            $table->date('arrival_date');
            $table->date('departure_date');
            $table->string('status');
            $table->text('notes')->nullable();
            $table->string('sync_hash', 64)->nullable();
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_type_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
    }
};

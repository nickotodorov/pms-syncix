<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'rooms';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->unsignedBigInteger('id')
                ->primary();
            $table->string('number');
            $table->integer('floor')
                ->nullable();
            $table->foreignId('room_type_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('sync_hash', 64)
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
    }
};

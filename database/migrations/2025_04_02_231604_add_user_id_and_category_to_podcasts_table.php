<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('podcasts', function (Blueprint $table) {
            // Añadir el campo user_id si no existe
            if (!Schema::hasColumn('podcasts', 'user_id')) {
                $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }

            // Añadir el campo category como enum
            $table->enum('category', ['Afirmaciones', 'Motivación', 'Autoestima'])->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('podcasts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->dropColumn('category');
        });
    }
};
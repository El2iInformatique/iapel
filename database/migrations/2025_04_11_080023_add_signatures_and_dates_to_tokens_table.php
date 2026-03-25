<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tokens', function (Blueprint $table) {
            $table->integer('x_signature')->nullable();
            $table->integer('y_signature')->nullable();
            $table->integer('x_date')->nullable();
            $table->integer('y_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tokens', function (Blueprint $table) {
            $table->dropColumn([
                'x_signature',
                'y_signature',
                'x_date',
                'y_date',
            ]);
        });
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'runtime_secrets';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::connection('runtime_secrets')->hasTable('runtime_secrets')) {
            return;
        }

        Schema::connection('runtime_secrets')->create('runtime_secrets', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('runtime_secrets')->dropIfExists('runtime_secrets');
    }
};

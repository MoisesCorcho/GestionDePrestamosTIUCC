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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->constrained()->cascadeOnUpdate();
            $table->foreignId('state_id')->nullable()->constrained()->cascadeOnUpdate();
            $table->foreignId('city_id')->nullable()->constrained()->cascadeOnUpdate();
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['state_id']);
            $table->dropForeign(['city_id']);

            $table->dropColumn([
                'country_id',
                'state_id',
                'city_id',
                'address',
                'postal_code',
            ]);
        });
    }
};

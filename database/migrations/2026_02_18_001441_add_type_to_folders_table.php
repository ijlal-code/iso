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
    Schema::table('folders', function (Blueprint $table) {
        // Enum: 'main' untuk folder biasa, 'panduan' untuk folder panduan
        $table->string('type')->default('main')->after('name'); 
    });
}

public function down(): void
{
    Schema::table('folders', function (Blueprint $table) {
        $table->dropColumn('type');
    });
}
};

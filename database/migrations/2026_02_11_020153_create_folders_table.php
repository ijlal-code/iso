<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // database/migrations/xxxx_create_folders_table.php
public function up(): void
{
    Schema::create('folders', function (Blueprint $table) {
        $table->id();
        $table->string('code')->nullable(); // Contoh: I, I.1, II.2.1
        $table->string('name');             // Contoh: KEBIJAKAN
        $table->unsignedBigInteger('parent_id')->nullable(); // Untuk Sub-folder
        $table->timestamps();

        $table->foreign('parent_id')->references('id')->on('folders')->onDelete('cascade');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};

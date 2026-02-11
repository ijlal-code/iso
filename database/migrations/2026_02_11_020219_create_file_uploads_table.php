<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // database/migrations/xxxx_create_file_uploads_table.php
public function up(): void
{
    Schema::create('file_uploads', function (Blueprint $table) {
        $table->id();
        $table->foreignId('folder_id')->constrained()->onDelete('cascade');
        $table->string('name');      // Nama yang diberikan user
        $table->string('file_path'); // Lokasi file asli di server
        $table->string('mime_type')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_uploads');
    }
};

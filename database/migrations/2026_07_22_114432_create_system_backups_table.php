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
        Schema::create('system_backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->bigInteger('file_size')->nullable(); // in bytes
            $table->string('destination')->default('local'); // local, ftp, google_drive
            $table->json('destination_config')->nullable(); // FTP details or GDrive config
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_backups');
    }
};

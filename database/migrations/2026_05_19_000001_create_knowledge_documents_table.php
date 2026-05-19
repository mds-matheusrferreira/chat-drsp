<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('mime_type')->nullable();
            $table->string('extension', 16);
            $table->unsignedBigInteger('size_bytes');
            $table->string('status')->default('uploaded')->index();
            $table->unsignedInteger('chunks_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->unique(['original_name', 'extension']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_documents');
    }
};

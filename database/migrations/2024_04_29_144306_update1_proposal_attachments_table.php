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
        Schema::table('proposal_attachments', function (Blueprint $table) {
            $table->dropColumn('filepath');
            $table->dropColumn('mimetype');
            $table->dropColumn('filename');
            $table->foreignId('document_id');

            $table->foreign('document_id')->references('id')->on('documents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposal_attachments', function (Blueprint $table) {
            //
        });
    }
};

<?php

use App\Models\DocumentAnalysis;
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
        Schema::table('document_analyses', function (Blueprint $table) {
            $table->string('ai_provider')
                ->default(DocumentAnalysis::PROVIDER_OPENAI)
                ->after('prompt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_analyses', function (Blueprint $table) {
            $table->dropColumn('ai_provider');
        });
    }
};

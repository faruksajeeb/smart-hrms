<?php

use App\Models\MasterDataItem;
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
        Schema::create('master_data_items', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->foreignId('parent_id')->nullable()->constrained('master_data_items')->nullOnDelete();
            $table->string('code')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->string('status')->default(MasterDataItem::STATUS_ACTIVE);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['category', 'code']);
            $table->unique(['category', 'name']);
            $table->index(['category', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_data_items');
    }
};

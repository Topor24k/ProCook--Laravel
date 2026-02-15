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
        Schema::table('recipes', function (Blueprint $table) {
            // Add compound index for user recipes optimization
            $table->index(['user_id', 'created_at'], 'recipes_user_created_index');
            
            // Add index for category filtering
            $table->index('category_id');
            
            // Add index for search functionality based on title
            $table->index('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropIndex('recipes_user_created_index');
            $table->dropIndex(['category_id']);
            $table->dropIndex(['title']);
        });
    }
};
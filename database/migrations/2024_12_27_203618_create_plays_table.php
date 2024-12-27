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
        Schema::create('plays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id') // Foreign key referencing users
                ->constrained('users')
                ->onUpdate('cascade') // Updates plays if user id is modified
                ->onDelete('cascade'); // Deletes plays if the user is deleted
            $table->integer('dice1'); // First dice roll
            $table->integer('dice2'); // Second dice roll
            $table->boolean('success'); // True if dice1 + dice2 == 7
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plays');
    }
};

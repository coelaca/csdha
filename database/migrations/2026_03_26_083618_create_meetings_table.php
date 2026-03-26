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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamp('schedule');
            $table->string('location', length: 2000);
            $table->text('agenda');
            $table->text('minutes')->nullable();
            $table->foreignId('meeting_status_id');
            $table->foreign('meeting_status_id')->references('id')->on('meeting_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};

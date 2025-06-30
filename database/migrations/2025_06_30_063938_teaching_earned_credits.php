<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TeachingEarnedCredits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teaching_earned_credits', function (Blueprint $table) {
            $table->increments('id');
$table->unsignedInteger('employee_id');
$table->foreign('employee_id')->references('id')->on('teaching');
            $table->string('earned_date')->nullable(); // Fixed: Should be date, not string
            $table->string('special_order')->nullable();
            $table->decimal('days', 8, 2)->default(0); // Fixed: Allow decimal for partial days
            $table->string('reference')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teaching_earned_credits');
    }
}

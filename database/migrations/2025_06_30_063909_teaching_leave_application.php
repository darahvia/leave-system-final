<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TeachingLeaveApplication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teaching_leave_applications', function (Blueprint $table) {
            $table->increments('id');
$table->unsignedInteger('employee_id');
$table->foreign('employee_id')->references('id')->on('teaching');
            $table->date('leave_incurred_date')->nullable();
            $table->integer('leave_incurred_days')->nullable(); // Fixed: Should be integer, not string
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
        Schema::dropIfExists('teaching_leave_applications');
    }
}

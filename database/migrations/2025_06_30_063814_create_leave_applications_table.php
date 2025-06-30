<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->increments('id');
$table->unsignedInteger('employee_id');
$table->foreign('employee_id')->references('id')->on('employees');
            $table->string('leave_type')->nullable();
            $table->string('leave_details')->nullable();
            $table->integer('working_days')->default(0);
            $table->date('inclusive_date_start')->nullable();
            $table->date('inclusive_date_end')->nullable();
            $table->date('date_filed')->nullable();
            $table->date('date_incurred')->nullable();
            $table->string('commutation')->nullable();
            $table->decimal('current_vl', 5, 2)->default(0);
            $table->decimal('current_sl', 5, 2)->default(0);
            $table->boolean('is_credit_earned')->default(false);
            $table->date('earned_date')->nullable();
            $table->boolean('is_cancellation')->default(false);
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
        Schema::dropIfExists('leave_applications');
    }
}

<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_cto_applications_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateCtoApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cto_applications', function (Blueprint $table) {
            // Corrected for older Laravel versions (pre-5.8) where id() method does not exist
            $table->bigIncrements('id'); 

            // Corrected for older Laravel versions (pre-7.x) where foreignId()->constrained() does not exist
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');


            // For CTO activities (credits earned)
            $table->string('special_order')->nullable();
            $table->date('date_of_activity_start')->nullable();
            $table->date('date_of_activity_end')->nullable();
            $table->text('activity')->nullable();
            $table->decimal('credits_earned', 8, 2)->nullable();


            // For CTO usage (credits deducted)
            $table->date('date_of_absence_start')->nullable();
            $table->date('date_of_absence_end')->nullable();
            $table->decimal('no_of_days', 5, 2)->nullable()->change();

            // Balance after this transaction
            $table->decimal('balance', 8, 2)->default(0);


            // Flag to distinguish between activity (credit) and usage (debit)
            $table->boolean('is_activity')->default(true);


            $table->timestamps();


            // Indexes for better performance
            $table->index(['employee_id', 'date_of_activity_start']);
            $table->index(['employee_id', 'date_of_absence_start']);
            $table->index(['employee_id', 'is_activity']);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cto_applications');


    }
}

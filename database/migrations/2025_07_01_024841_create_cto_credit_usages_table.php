<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateCtoCreditUsagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cto_credit_usages', function (Blueprint $table) {
            // Changed from $table->id(); to $table->bigIncrements('id') for compatibility with older Laravel versions.
            $table->bigIncrements('id'); 

            // Corrected for older Laravel versions (pre-7.x) where foreignId()->constrained() does not exist
            $table->unsignedBigInteger('cto_activity_id');
            $table->foreign('cto_activity_id')->references('id')->on('cto_applications')->onDelete('cascade');


            // Corrected for older Laravel versions (pre-7.x) where foreignId()->constrained() does not exist
            $table->unsignedBigInteger('cto_absence_id');
            $table->foreign('cto_absence_id')->references('id')->on('cto_applications')->onDelete('cascade');


            $table->decimal('days_used', 8, 2); // How many days from the activity were used by this absence
            $table->timestamps();


            // Ensures that a specific activity credit is only linked to a specific absence once
            // $table->unique(['cto_activity_id', 'cto_absence_id']);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cto_credit_usages');
    }
}

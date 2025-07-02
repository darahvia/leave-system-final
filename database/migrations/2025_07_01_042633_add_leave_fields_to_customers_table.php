<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLeaveFieldsToCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('vl');
            $table->integer('sl');
            $table->integer('spl');
            $table->integer('fl');
            $table->integer('solo_parent');
            $table->integer('ml');
            $table->integer('pl');
            $table->integer('ra9710');
            $table->integer('rl');
            $table->integer('sel');
            $table->integer('study_leave');
            $table->integer('adopt');
            $table->integer('vawc');
            $table->decimal('balance_forwarded_vl', 5, 2)->default(0);
            $table->decimal('balance_forwarded_sl', 5, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            //
        });
    }
}

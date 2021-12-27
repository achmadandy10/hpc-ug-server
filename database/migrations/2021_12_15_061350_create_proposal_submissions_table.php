<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProposalSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proposal_submissions', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->string('user_id');
            $table->string('phone_number');
            $table->string('research_field');
            $table->text('short_description');
            $table->text('data_description');
            $table->boolean('shared_data');
            $table->text('activity_plan');
            $table->text('output_plan');
            $table->text('previous_experience')->nullable();
            $table->string('facility_id');
            $table->string('use_stock');
            $table->string('proposal_file');
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('facility_id')->references('id')->on('facilities')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proposal_submissions');
    }
}

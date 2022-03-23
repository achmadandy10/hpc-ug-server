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
            $table->string('type_of_proposal');
            $table->string('phone_number');
            $table->string('educational_level');
            $table->string('application_file');
            $table->string('study_program');
            $table->string('gpu');
            $table->string('ram');
            $table->string('storage');
            $table->string('partner');
            $table->string('duration');
            $table->string('research_field');
            $table->text('short_description');
            $table->text('data_description');
            $table->boolean('shared_data');
            $table->text('activity_plan');
            $table->text('output_plan');
            $table->text('previous_experience')->nullable();
            $table->integer('research_fee')->nullable();
            $table->string('docker_image');
            $table->string('proposal_file');
            $table->boolean('term_and_condition');
            $table->string('status');
            $table->text('rev_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
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

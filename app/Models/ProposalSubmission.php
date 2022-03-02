<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProposalSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'phone_number',
        'educational_level',
        'application_file',
        'study_program',
        'gpu',
        'ram',
        'storage',
        'partner',
        'duration',
        'research_field',
        'short_description',
        'data_description',
        'shared_data',
        'activity_plan',
        'output_plan',
        'previous_experience',
        'docker_image',
        'research_fee',
        'proposal_file',
        'term_and_condition',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

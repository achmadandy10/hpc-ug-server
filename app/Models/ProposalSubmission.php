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
        'research_field',
        'short_description',
        'data_description',
        'shared_data',
        'activity_plan',
        'output_plan',
        'previous_experience',
        'facility_id',
        'proposal_file',
        'status',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}

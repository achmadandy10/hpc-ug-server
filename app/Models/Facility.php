<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facility extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'start_stock',
        'remaining_stock',
        'use_stock',
    ];

    public function proposal_submissions()
    {
        return $this->hasMany(ProposalSubmission::class);
    }
}

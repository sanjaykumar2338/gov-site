<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectDetail extends Model
{
    protected $table = 'project_details';
    protected $guarded = []; // Or specify fillable fields
    public $timestamps = true;

    public function unitSummaries()
    {
        return $this->hasMany(ProjectUnitSummary::class, 'project_id', 'id');
    }

    public function unitBoxes()
    {
        return $this->hasMany(ProjectUnitBoxView::class, 'project_id', 'id');
    }
}


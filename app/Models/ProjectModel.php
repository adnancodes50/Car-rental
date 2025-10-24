<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectModel extends Model
{
    use HasFactory;

    protected $table = 'projects_details';
    public $timestamps = true;

    protected $fillable = [
        'project_name',
        'logo',
    ];

    // Accessor: convenient URL for the logo
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }
}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Model representing a project
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Project extends Model
{
    protected $fillable = [
        'name',
        'notes',
    ];
}

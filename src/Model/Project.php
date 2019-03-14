<?php

namespace App\Model;

use App\Model\AbstractModel;
use App\Traits\HasValidationTrait;
use Respect\Validation\Validator;

/**
 * Model representing a project
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Project extends AbstractModel
{
    protected $fillable = [
        'name',
        'notes',
    ];

    protected $fieldNames = [
        // 'name' => 'Project Name',
    ];

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getValidators()
    {
        return [
            'name' => Validator::notEmpty()
        ];
    }
}

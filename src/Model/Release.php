<?php

namespace App\Model;

use App\Model\AbstractModel;
use App\Provider\Gitlab;
use App\Traits\HasValidationTrait;
use Respect\Validation\Validator;

/**
 * Model representing a project release
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Release extends AbstractModel
{
    protected $fillable = [
        'project',
        'number',
    ];

    protected $attributes = [
        'status' => 'new'
    ];

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getValidators()
    {
        return [
            'project' => Validator::notEmpty()->intVal()->min(1),
            'hash' => Validator::stringType(),
            'status' => Validator::notEmpty()->in(['new', 'deployed', 'error']),
        ];
    }
}

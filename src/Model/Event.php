<?php

namespace App\Model;

use App\Model\Deployment;
use App\Model\Finder\EventFinder;
use Respect\Validation\Validator;
use Ronanchilvers\Orm\Model;
use Ronanchilvers\Orm\Traits\HasValidationTrait;

/**
 * Model representing a deployment event
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Event extends Model
{
    use HasValidationTrait;

    static protected $finder       = EventFinder::class;
    static protected $columnPrefix = 'event';

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function setupValidation()
    {
        $this->registerRules([
            'deployment' => Validator::notEmpty()->intVal()->min(1),
            'type'       => Validator::notEmpty(),
            'header'     => Validator::notEmpty(),
        ]);
    }

    /**
     * Relationship with project
     *
     * @return \App\Model\Deployment
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function relateDeployment()
    {
        return $this->belongsTo(
            Deployment::class
        );
    }
}

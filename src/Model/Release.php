<?php

namespace App\Model;

use App\Model\AbstractModel;
use App\Model\Finder\ReleaseFinder;
use App\Provider\Gitlab;
use Respect\Validation\Validator;
use Ronanchilvers\Orm\Model;
use Ronanchilvers\Orm\Traits\HasValidationTrait;

/**
 * Model representing a project release
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Release extends Model
{
    use HasValidationTrait;

    static protected $finder = ReleaseFinder::class;

    protected $data = [
        'status' => 'new'
    ];

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function setupValidation()
    {
        $this->registerRules([
            'project' => Validator::notEmpty()->intVal()->min(1),
            'hash'    => Validator::stringType(),
            'status'  => Validator::notEmpty()->in(['new', 'deployed', 'error']),
        ]);
    }
}

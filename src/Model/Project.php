<?php

namespace App\Model;

use App\Facades\Strategy;
use App\Model\AbstractModel;
use Respect\Validation\Validator;
use Ronanchilvers\Orm\Model;
use Ronanchilvers\Orm\Traits\HasValidationTrait;
use Ronanchilvers\Utility\Str;

/**
 * Model representing a project
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Project extends Model
{
    use HasValidationTrait;

    static protected $columnPrefix = 'project';

    protected $providers = [
        'github' => 'Github.com',
        'gitlab' => 'Gitlab.com',
    ];

    protected $data = [
        'project_branch' => 'master',
    ];

    /**
     * Boot the model
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function boot()
    {
        $this->addType('datetime', 'last_released');
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function setupValidation()
    {
        $providerKeys = array_keys($this->providers);
        $this->registerRules([
            'name'       => Validator::notEmpty(),
            'provider'   => Validator::notEmpty()->in($providerKeys),
            'repository' => Validator::notEmpty(),
            'branch'     => Validator::notEmpty(),
        ]);
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function beforeCreate()
    {
        if (empty($this->token)) {
            $this->token = Str::token(64);
        }
    }

    /**
     * Get the list of valid providers
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getProviderOptions()
    {
        return $this->providers;
    }
}

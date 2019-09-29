<?php

namespace App\Model;

use App\Model\Finder\ProjectFinder;
use Respect\Validation\Validator;
use Ronanchilvers\Orm\Model;
use Ronanchilvers\Orm\Orm;
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

    static protected $finder       = ProjectFinder::class;
    static protected $columnPrefix = 'project';

    protected $providers = [
        'github' => 'Github',
        'gitlab' => 'Gitlab',
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
        $this->addType('datetime', 'last_deployment');
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
        $key = preg_replace('#[^A-z0-9\-]#', '-', $this->name);
        $key = preg_replace('#[-]{2,}#', '-', $key);
        $key = strtolower($key);
        if (!Orm::finder(get_called_class())->keyIsUnique($key)) {
            $this->addError('name', 'Name must be unique');
            return false;
        }
        $this->key = $key;
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

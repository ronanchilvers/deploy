<?php

namespace App\Model;

use App\Facades\Provider;
use App\Model\Finder\ProjectFinder;
use Respect\Validation\Validator;
use Ronanchilvers\Orm\Model;
use Ronanchilvers\Orm\Orm;
use Ronanchilvers\Orm\Traits\HasValidationTrait;
use Ronanchilvers\Utility\Str;

/**
 * Model representing a project
 *
 * @property int id
 * @property string name
 * @property string token
 * @property string key
 * @property string provider
 * @property string repository
 * @property string branch
 * @property string status
 * @property int last_number
 * @property null|\Carbon\Carbon last_deployment
 * @property string last_author
 * @property string last_sha
 * @property string last_status
 * @property null|\Carbon\Carbon created
 * @property null|\Carbon\Carbon updated
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Project extends Model
{
    use HasValidationTrait;

    static protected $finder       = ProjectFinder::class;
    static protected $columnPrefix = 'project';

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
        $providerKeys = array_keys(Provider::getOptions());
        $this->registerRules([
            'name'       => Validator::notEmpty(),
            'provider'   => Validator::notEmpty()->in($providerKeys),
            'repository' => Validator::notEmpty(),
            'branch'     => Validator::notEmpty(),
            'status'     => Validator::notEmpty()->in(['active', 'deploying']),
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
     * Mark this project as deploying
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function markDeploying()
    {
        $this->status = 'deploying';

        return $this->save();
    }

    /**
     * Mark this project as active
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function markActive()
    {
        $this->status = 'active';

        return $this->save();
    }

    /**
     * Is this project deployable?
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isDeployable()
    {
        return 'active' == $this->status;
    }
}

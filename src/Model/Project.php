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

    protected $providers = [
        'github' => 'Github.com',
        'gitlab' => 'Gitlab.com',
        'local'  => 'On local disk',
    ];

    protected $data = [
        'branch' => 'master',
    ];

    /**
     * @var App\Provider\StrategyInterface
     */
    protected $strategy;

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

    /**
     * Get the deploy config for this project
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getDeployConfig(): ?string
    {
        return $this->strategy()->getDeployConfig($this);
    }

    /**
     * Get the Clone URL for this project
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getCloneUrl(): string
    {
        return $this->strategy()->getCloneUrl($this);
    }

    /**
     * Get the strategy object for this project
     *
     * @return App\Provider\StrategyInterface
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function strategy()
    {
        if (!$this->strategy instanceof StrategyInterface) {
            $this->strategy = Strategy::get($this->provider);
        }

        return $this->strategy;
    }
}

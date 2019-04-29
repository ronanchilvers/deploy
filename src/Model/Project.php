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
    protected $providers = [
        'github' => 'Github.com',
        'gitlab' => 'Gitlab.com',
    ];

    protected $fillable = [
        'name',
        'provider',
        'repository',
        'branch',
        'notes',
    ];

    protected $fieldNames = [
        // 'name' => 'Project Name',
    ];

    protected $attributes = [
        'branch' => 'master',
    ];

    /**
     * @var App\Provider\StrategyInterface
     */
    protected $strategy;

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
     * Get the Clone URL for this project
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getCloneUrl()
    {
        return $this->strategy()->getCloneUrl();
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getValidators()
    {
        $providerKeys = array_keys($this->providers);
        return [
            'name'       => Validator::notEmpty(),
            'provider'   => Validator::notEmpty()->in($providerKeys),
            'repository' => Validator::notEmpty(),
        ];
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
            $class = "\App\Provider\\" . ucfirst(strtolower($this->provider)) . "Strategy";
            if (!class_exists($class)) {
                Log::error('Invalid provider strategy', [
                    'project' => $this->id,
                    'provider' => $this->provider,
                ]);
                throw new RuntimeException("Invalid provider strategy");
            }
            $this->strategy = new $class($this);
        }

        return $this->strategy;
    }
}

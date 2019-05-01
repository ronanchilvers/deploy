<?php

namespace App\Model;

use App\Facades\Strategy;
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
        'local'  => 'On local disk',
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
            $this->strategy = Strategy::get($this->provider);
        }

        return $this->strategy;
    }
}

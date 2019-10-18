<?php

namespace App\Model;

use App\Model\Event;
use App\Model\Finder\DeploymentFinder;
use App\Model\Project;
use Carbon\Carbon;
use Respect\Validation\Validator;
use Ronanchilvers\Orm\Model;
use Ronanchilvers\Orm\Traits\HasValidationTrait;

/**
 * Model representing a project deployment
 *
 * @property int id
 * @property \App\Model\Project project
 * @property int number
 * @property \App\Model\Deployment|null original
 * @property string sha
 * @property string author
 * @property string message
 * @property string configuration
 * @property string status
 * @property \Carbon\Carbon|null started
 * @property \Carbon\Carbon|null finished
 * @property \Carbon\Carbon|null failed
 * @property \Carbon\Carbon|null created
 * @property \Carbon\Carbon|null updated
 * @property string source
 * @property string committer
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Deployment extends Model
{
    use HasValidationTrait;

    static protected $finder       = DeploymentFinder::class;
    static protected $columnPrefix = 'deployment';

    protected $data = [
        'deployment_status' => 'pending'
    ];

    /**
     * Boot the model
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function boot()
    {
        $this->addType('datetime', 'started');
        $this->addType('datetime', 'finished');
        $this->addType('datetime', 'failed');
        $this->addType('model', 'project', [
            'class' => Project::class
        ]);
        $this->addType('model', 'original', [
            'class' => static::class
        ]);
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function clone()
    {
        $this->status   = 'pending';
        $this->started  = null;
        $this->finished = null;
        $this->failed   = null;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function setupValidation()
    {
        $this->registerRules([
            'project' => Validator::notEmpty()->intVal()->min(1),
            'hash'    => Validator::stringType(),
            'status'  => Validator::notEmpty()->in(['pending', 'deploying', 'deployed', 'failed']),
            'type'    => Validator::notEmpty()->in(['deployment', 'reactivation']),
        ]);
    }

    /**
     * Relationship with project
     *
     * @return \App\Model\Project
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    // protected function relateProject()
    // {
    //     return $this->belongsTo(
    //         Project::class
    //     );
    // }

    /**
     * Relate events to this deployment
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function relateEvents()
    {
        return $this->hasMany(
            Event::class
        );
    }

    /**
     * Relate original deployment to this one for reactivations
     *
     * @return \App\Model\Deployment|null
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    // protected function relateOriginal()
    // {
    //     return $this->belongsTo(
    //         Deployment::class,
    //         'original'
    //     );
    // }

    /**
     * Start the deployment
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function start()
    {
        $this->status  = 'deploying';
        $this->started = Carbon::now();

        return $this->save();
    }

    /**
     * Finish the deployment
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function finish()
    {
        $this->status   = 'deployed';
        $this->finished = Carbon::now();

        return $this->save();
    }

    /**
     * Mark the deployment as failed
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function fail()
    {
        $this->status = 'failed';
        $this->failed = Carbon::now();

        return $this->save();
    }

    /**
     * Is this a full deployment?
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isReactivation()
    {
        return 0 < $this->getAttributeRaw('original');
    }

    /**
     * Is the deployment deployed?
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isDeployed()
    {
        return 'deployed' == $this->status;
    }

    /**
     * Is the deployment deploying?
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isDeploying()
    {
        return 'deploying' == $this->status;
    }

    /**
     * Is the deployment pending?
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isPending()
    {
        return 'pending' == $this->status;
    }

    /**
     * Is the deployment failed?
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isFailed()
    {
        return 'failed' == $this->status;
    }

    /**
     * Get the duration in seconds for this deployment
     *
     * @return int
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getDuration()
    {
        if ('deployed' != $this->status) {
            return null;
        }
        $finished = $this->finished;
        if ($finished instanceof Carbon) {
            return $this->finished->diffInSeconds($this->started);
        }

        return null;
    }
}

<?php

namespace App\Model;

use App\Model\AbstractModel;
use App\Model\Finder\ReleaseFinder;
use App\Model\Project;
use App\Provider\Gitlab;
use Carbon\Carbon;
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

    static protected $finder       = ReleaseFinder::class;
    static protected $columnPrefix = 'release';

    protected $data = [
        'release_status' => 'pending'
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
        ]);
    }

    /**
     * Relationship with project
     *
     * @return App\Model\Project
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function relateProject()
    {
        return $this->belongsTo(
            Project::class

        );
    }

    /**
     * Start the release
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
     * Finish the release
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
     * Mark the release as failed
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
     * Is the release deployed?
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isDeployed()
    {
        return 'deployed' == $this->status;
    }

    /**
     * Is the release deploying?
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isDeploying()
    {
        return 'deploying' == $this->status;
    }

    /**
     * Is the release pending?
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isPending()
    {
        return 'pending' == $this->status;
    }

    /**
     * Is the release failed?
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isFailed()
    {
        return 'failed' == $this->status;
    }

    /**
     * Get the duration in seconds for this release
     *
     * @return int
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getDuration()
    {
        if ('deployed' != $this->status) {
            return null;
        }
        return $this->finished->diffInSeconds($this->started);
    }
}

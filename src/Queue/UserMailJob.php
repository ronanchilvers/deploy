<?php

namespace App\Queue;

use App\Facades\Mail;
use App\Model\User;
use Ronanchilvers\Foundation\Queue\Exception\FailedJobException;
use Ronanchilvers\Foundation\Queue\Job\Job;

/**
 * Base job for sending user emails
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
abstract class UserMailJob extends Job
{
    /**
     * @var string
     */
    protected $queue = 'emails';

    /**
     * @var string
     */
    protected $emailClass = null;

    /**
     * @var App\Model\User
     */
    protected $user;

    /**
     * @var array
     */
    protected $internalProperties = [
        'delay',
        'queue',
        'emailClass'
    ];

    /**
     * Class constructor
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(
        User $user,
    ) {
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function execute()
    {
        $class = $this->emailClass;
        $mail = new $class(
            $this->user
        );
        if (!Mail::send($mail)) {
            throw new FailedJobException('Unable to send User email : ' . $class);
        }
    }
}

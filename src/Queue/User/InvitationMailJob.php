<?php

namespace App\Queue;

use App\Mail\User\InvitationMail;
use App\Queue\UserMailJob;

/**
 * Job to send an account validation email
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class InvitationMailJob extends UserMailJob
{
    /**
     * @var string
     */
    protected $emailClass = InvitationMail::class;
}

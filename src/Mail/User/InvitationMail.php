<?php

namespace App\Mail\User;

use App\Mail\Email;
use App\Model\Customer;
use App\Model\User;

/**
 * Email sent to invite a user to sign up
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class InvitationMail extends Email
{
    /**
     * Class constructor
     *
     * @param App\Model\User $user The user to send the invitation to
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(
        User $user
    ) {
        $this
            ->addTo($user->email)
            ->setSubject('Invitation to deploy!')
            ->addTemplateContext(
                'user',
                $user
            );
    }
}

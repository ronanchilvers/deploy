<?php

namespace App\Security;

use App\Model\User;
use Ronanchilvers\Orm\Orm;
use Ronanchilvers\Sessions\Session;

/**
 * Manager responsible for managing security / login data
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Manager
{
    const SESSION_KEY = 'security.session';

    /**
     * @var Ronanchilvers\Sessions\Session
     */
    protected $session;

    /**
     * @var \App\Model\User
     */
    protected $user;

    /**
     * Class constructor
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Log a user in using an email address and password
     *
     * @param string $email
     * @param string $password
     * @return boolean|\App\Model\User $user
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function login($email, $password)
    {
        $user = Orm::finder(User::class)->select()
            ->where(User::prefix('email'), $email)
            ->where(User::prefix('status'), User::STATUS_ACTIVE)
            ->one();
        if (!$user instanceof User) {
            return false;
        }
        if (!$user->verify($password)) {
            return false;
        }
        $this->session->set(
            static::SESSION_KEY,
            [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        );

        return $user;
    }

    /**
     * Logout the current session
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function logout()
    {
        $this->session->delete(
            static::SESSION_KEY
        );
    }

    /**
     * Is a user logged in?
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function hasLogin()
    {
        return $this->session->has(
            static::SESSION_KEY
        );
    }

    /**
     * Refresh the session data
     *
     * @param \App\Model\User $user
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function refresh(User $user)
    {
        if (!$this->hasLogin()) {
            return false;
        }
        $session = $this->session->get(
            static::SESSION_KEY
        );
        if ($user->id !== $session['id']) {
            return false;
        }
        $session['name'] = $user->name;
        $session['email']= $user->email;
        $this->session->set(
            static::SESSION_KEY,
            $session
        );

        return true;
    }

    /**
     * Get the current user id
     *
     * @return integer
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function id()
    {
        if (!$this->hasLogin()) {
            return null;
        }
        $session = $this->session->get(
            static::SESSION_KEY
        );

        return $session['id'];
    }

    /**
     * Get the current logger in email
     *
     * @return null|string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function name()
    {
        if (!$this->hasLogin()) {
            return null;
        }
        $session = $this->session->get(
            static::SESSION_KEY
        );

        return $session['name'];
    }

    /**
     * Get the current logger in email
     *
     * @return null|string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function email()
    {
        if (!$this->hasLogin()) {
            return null;
        }
        $session = $this->session->get(
            static::SESSION_KEY
        );

        return $session['email'];
    }

    /**
     * Get the currently logged in user
     *
     * @return null|\App\Model\User
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function user()
    {
        if ($this->user instanceof User) {
            return $this->user;
        }
        if (!$this->hasLogin()) {
            return null;
        }
        $session = $this->session->get(
            static::SESSION_KEY
        );
        $user = Orm::finder(User::class)->one(
            $session['id']
        );
        if ($user instanceof User) {
            $this->user = $user;

            return $user;
        }

        return null;
    }
}

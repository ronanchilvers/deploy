<?php

namespace App\Facades;

use App\Security\Manager;
use Ronanchilvers\Foundation\Facade\Facade;

/**
 * Security manager facade class
 *
 * @method static login(string $email, string $password)
 * @method static logout()
 * @method static hasLogin()
 * @method static refresh(User $user)
 * @method static id()
 * @method static name()
 * @method static email()
 * @method static user()
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Security extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = Manager::class;
}

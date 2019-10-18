<?php

namespace App\Facades;

use App\Security\Manager;
use Ronanchilvers\Foundation\Facade\Facade;

/**
 * Security manager facade class
 *
 * @method @method login(string $email, string $password)
 * @method logout()
 * @method hasLogin()
 * @method refresh(User $user)
 * @method id()
 * @method name()
 * @method email()
 * @method user()
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Security extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = Manager::class;
}

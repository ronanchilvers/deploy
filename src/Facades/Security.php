<?php

namespace App\Facades;

use App\Security\Manager;
use Ronanchilvers\Foundation\Facade\Facade;

/**
 * Security manager facade class
 *
 * @method static bool |\App\Model\User login(string $email, string $password)
 * @method static void logout()
 * @method static bool hasLogin()
 * @method static void refresh(User $user)
 * @method static int id()
 * @method static string name()
 * @method static string email()
 * @method static string user()
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Security extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = Manager::class;
}

<?php

namespace App\Model\Finder;

use App\Model\User;
use Ronanchilvers\Orm\Finder;
use ClanCats\Hydrahon\Query\Expression;

/**
 * Finder for user models
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class UserFinder extends Finder
{
    /**
     * Get a user by hash
     *
     * @param string $hash
     * @return \App\Model\User
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function forHash($hash)
    {
        return $this->select()
            ->where(User::prefix('hash'), $hash)
            ->one();
    }
}

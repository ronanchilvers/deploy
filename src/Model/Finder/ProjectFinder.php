<?php

namespace App\Model\Finder;

use App\Model\Project;
use Ronanchilvers\Orm\Finder;
use ClanCats\Hydrahon\Query\Expression;

/**
 * Finder for project models
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class ProjectFinder extends Finder
{
    /**
     * Get a project by project key
     *
     * @param string $key
     * @return App\Model\Project
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function forKey($key)
    {
        return $this->select()
            ->where(Project::prefix('key'), $key)
            ->one();
    }
}

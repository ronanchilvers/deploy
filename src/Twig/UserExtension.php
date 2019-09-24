<?php

namespace App\Twig;

use App\Facades\Security;
use App\Model\Project;
use App\Provider\Factory;
use App\Security\Manager;
use Carbon\Carbon;
use Ronanchilvers\Foundation\Traits\Optionable;
use Ronanchilvers\Utility\Str;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

/**
 * Twig extension for user helper methods
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class UserExtension extends AbstractExtension
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'is_favourite',
                [$this, 'isFavourite']
            ),
        ];
    }

    /**
     * Is a given project in a user's favourites?
     *
     * @param App\Model\Project $project
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isFavourite(Project $project)
    {
        $user       = Security::user();
        $favourites = $user->preference('favourites', []);

        return in_array($project->id, $favourites);
    }

}

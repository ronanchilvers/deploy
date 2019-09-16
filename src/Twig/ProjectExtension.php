<?php

namespace App\Twig;

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
 * Twig extension for github helper methods
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class ProjectExtension extends AbstractExtension
{
    /**
     * @var App\Provider\Factory
     */
    protected $factory;

    /**
     * @var string
     */
    protected $repoLinkHtml = '<a class="button is-text" href="{url}" target="_blank"><span class="icon"><i class="fab fa-{provider}"></i></span><span>{repository}</span></a>';
    // protected $repoLinkHtml = '<a class="button is-text" href="{url}" target="_blank"><span>{repository}</span></a>';

    /**
     * @var string
     */
    protected $branchLinkHtml = '<a class="button is-text" href="{url}" target="_blank"><span class="icon"><i class="fas fa-code-branch"></i></span><span>{branch}</span></a>';

    /**
     * @var string
     */
    protected $shaLinkHtml = '<a class="button is-text" href="{url}" target="_blank"><span class="icon"><i class="fas fa-hashtag"></i></span><span>{sha}</span></a>';

    /**
     * Class constructor
     *
     * @param App\Provider\Factory $factory
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'repo_link',
                [$this, 'repositoryLink']
            ),
            new TwigFunction(
                'branch_link',
                [$this, 'branchLink']
            ),
            new TwigFunction(
                'sha_link',
                [$this, 'shaLink']
            ),
        ];
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getFilters()
    {
        return [
            new \Twig\TwigFilter(
                'human_date',
                [$this, 'humanDate']
            ),
        ];
    }

    /**
     * Get a repository link for a given project
     *
     * @param Project $project
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function repositoryLink(Project $project = null)
    {
        $provider = $this->factory->forProject(
            $project
        );
        $url = $provider->getRepositoryLink(
            $project->repository
        );
        $link = Str::moustaches(
            $this->repoLinkHtml,
            [
                'provider'   => $project->provider,
                'repository' => $project->repository,
                'url'        => $url
            ]
        );

        return new Markup($link, 'UTF-8');
    }

    /**
     * Get a branch link for a given project
     *
     * @param Project $project
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function branchLink(Project $project = null)
    {
        $provider = $this->factory->forProject(
            $project
        );
        $url = $provider->getBranchLink(
            $project->repository,
            $project->branch
        );
        $link = Str::moustaches(
            $this->branchLinkHtml,
            [
                'branch' => $project->branch,
                'url'    => $url
            ]
        );

        return new Markup($link, 'UTF-8');
    }

    /**
     * Create a link for a given SHA and project
     *
     * @param Project $project
     * @param string $sha
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function shaLink(Project $project = null, string $sha = null)
    {
        if (is_null($project) || is_null($sha)) {
            return 'N/A';
        }
        $provider = $this->factory->forProject(
            $project
        );
        $url = $provider->getShaLink(
            $project->repository,
            $sha
        );
        $link = Str::moustaches(
            $this->shaLinkHtml,
            [
                'sha' => substr($sha, 0, 8),
                'url' => $url
            ]
        );

        return new Markup($link, 'UTF-8');
    }

    /**
     * Generate a human readable datetime string from a Carbon object
     *
     * @param Carbon\Carbon $carbon
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function humanDate(Carbon $carbon = null)
    {
        if (is_null($carbon)) {
            return 'N/A';
        }
        // More than a day ago
        if (0 < $carbon->diffInDays()) {
            return $carbon->format('Y-m-d H:i:s');
        }
        // More than 30 minutes ago
        if (30 < $carbon->diffInMinutes()) {
            return $carbon->format('H:i:s');
        }

        return $carbon->diffForHumans(['parts' => 2]);
    }
}

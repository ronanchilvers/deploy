<?php

namespace App\Provider;

use App\Model\Project;

/**
 * Interface for source control providers
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
interface ProviderInterface
{
    /**
     * Does this provider handle a given project?
     *
     * @param App\Model\Project $project
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function handles(Project $project);

    /**
     * Get a repository link for a given repository
     *
     * @param string $repository
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getRepositoryLink(string $repository);

    /**
     * Get a link to a repository branch
     *
     * @param string $repository
     * @param string $branch
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getBranchLink(string $repository, string $branch);

    /**
     * Get a link for a given repository and sha
     *
     * @param string $repository
     * @param string $sha
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getShaLink(string $repository, string $sha);

    /**
     * Get the HEAD commit data for a given project, returned as an array
     *
     * @param App\Model\Project $project
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getHeadInfo(Project $project);

    /**
     * Download project code into a given directory
     *
     * @param array $params The parameters for the download - repository and ref (sha)
     * @param string $directory
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function download($params, $directory);

    /**
     * Scan the remote repository for configuration information
     *
     * @return Ronanchilvers\Foundation\Config
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function scanConfiguration(Project $project);
}

<?php

namespace App\Action\Traits;

use App\Action\Context;
use Ronanchilvers\Foundation\Config;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Trait to implement hook running
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
trait Hookable /* implements HookableInterface */
{
    /**
     * Run a set of hooks from a configuration object
     *
     * @param Ronanchilvers\Foundation\Config $configuration
     * @param App\Action\Context
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function runHooks(Config $configuration, Context $context)
    {
        $deployment = $context->getOrThrow(
            'deployment',
            'Invalid or missing deployment in hook runner'
        );
        $deploymentDir = $context->getOrThrow(
            'deployment_dir',
            'Invalid or missing deployment directory'
        );
        $key   = $this->getKey() . '.after';
        $hooks = $configuration->get($key);
        if (!is_array($hooks) || empty($hooks)) {
            $this->info(
                $deployment,
                sprintf('%s hooks not defined', $key)
            );
            return;
        }
        foreach ($hooks as $hook) {
            $this->info(
                $deployment,
                sprintf('%s hook running - %s', $key, $hook)
            );
            $process = new Process($hook, $deploymentDir);
            $process->run();
            if (!$process->isSuccessful()) {
                $this->error(
                    $deployment,
                    sprintf('%s hook failed to run : %s', $key, $hook),
                    [$process->getOutput, $process->getErrorOutput()]
                );
                throw new RuntimeException('Unable to run deployment hook');
            }
            $this->info(
                $deployment,
                sprintf('%s hook ran successfully', $key),
                $process->getOutput()
            );
        }
    }
}

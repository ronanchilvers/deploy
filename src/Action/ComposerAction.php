<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use App\Facades\Log;
use App\Facades\Settings;
use App\Model\Deployment;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Utility\File;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use RuntimeException;

/**
 * Action to run composer on the project if a composer.json file is found
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class ComposerAction extends AbstractAction
{
    /**
     * @see \App\Action\ActionInterface::run()
     */
    public function run(Config $configuration, Context $context)
    {
        $deployment    = $context->getOrThrow('deployment', 'Invalid or missing deployment');
        $deploymentDir = $context->getOrThrow('deployment_dir', 'Invalid or missing deployment directory');
        $composerJson  = File::join($deploymentDir, 'composer.json');
        if (!is_readable($composerJson)) {
            $this->info(
                $deployment,
                'No composer.json file found - skipping composer installation'
            );
            return;
        }
        $composerPath = $this->getComposerPath($deployment);
        $composerArgs = $configuration->get(
            'composer.command',
            'install --no-dev --optimize-autoloader'
        );
        $phpPath      = Settings::get('binary.php');
        $command      = "{$phpPath} {$composerPath} {$composerArgs}";
        Log::debug('Executing composer', [
            'command' => $command,
        ]);
        $this->info(
            $deployment,
            [
                "Directory - {$deploymentDir}",
                "Command - {$command}",
            ]
        );
        $process = new Process(
            explode(' ', $command),
            $deploymentDir,
            [
                'COMPOSER_HOME' => dirname($composerPath),
            ]
        );
        $process->run();
        if (!$process->isSuccessful()) {
            $this->error(
                $deployment,
                [
                    'Composer run failed',
                    $process->getErrorOutput()
                ]
            );
            throw new ProcessFailedException($process);
        }
        $this->info(
            $deployment,
            [
                $process->getOutput(),
                $process->getErrorOutput(),
            ]
        );
    }

    /**
     * Get the path to the installed composer phar file
     *
     * This method returns the path to a composer phar file, downloading it
     * if required.
     *
     * @return string
     * @throws RuntimeException If composer cannot be found
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getComposerPath(Deployment $deployment)
    {
        $baseDir  = Settings::get('build.base_dir');
        $filename = File::join($baseDir, 'composer.phar');
        if (!is_readable($filename)) {
            $this->info(
                $deployment,
                'Downloading composer.phar'
            );
            $installer = File::join($baseDir, 'composer-setup.php');
            if (!copy('https://getcomposer.org/installer', $installer)) {
                throw new RuntimeException('Unable to download composer installer');
            }
            $expected = file_get_contents('https://composer.github.io/installer.sig');
            $actual = hash_file('sha384', $installer);
            if (!$expected == $actual) {
                throw new RuntimeException('Signature mismatch for composer installer');
            }
            $phpPath = Settings::get('binary.php');
            $process = new Process(
                [$phpPath, $installer],
                $baseDir,
                [
                    'COMPOSER_HOME' => $baseDir,
                ]
            );
            $process->run();
            if (!$process->isSuccessful()) {
                $this->error(
                    $deployment,
                    [
                        'Failed downloading composer.phar',
                        $process->getOutput(),
                        $process->getErrorOutput()
                    ]
                );
                throw new RuntimeException('Failed to run composer installer');
            }
            if (!unlink($installer)) {
                $this->error(
                    $deployment,
                    'Unable to remove the composer installer file ' . $installer
                );
                throw new RuntimeException('Unable to remove the composer installer file');
            }
        }

        return $filename;
    }
}

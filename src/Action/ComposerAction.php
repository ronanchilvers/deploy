<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use App\Facades\Log;
use App\Facades\Settings;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Utility\File;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Action to run composer on the project if a composer.json file is found
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class ComposerAction extends AbstractAction implements ActionInterface
{
    /**
     * @see App\Action\ActionInterface::run()
     */
    public function run(Config $configuration, Context $context)
    {
        $releaseDir   = $context->getOrThrow('release_dir', 'Invalid or missing release directory');
        $composerJson = File::join($releaseDir, 'composer.json');
        if (!is_readable($composerJson)) {
            return;
        }
        $composerPath = $this->getComposerPath();
        $composerArgs = $configuration->get(
            'composer.command',
            'install --no-dev --optimize-autoloader'
        );
        $phpPath      = Settings::get('binary.php');
        $command      = "{$phpPath} {$composerPath} {$composerArgs}";
        Log::debug('Executing composer', [
            'command' => $command,
        ]);
        $process      = new Process(explode(' ', $command), $releaseDir);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
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
    protected function getComposerPath()
    {
        $filename = '/tmp/composer.phar';
        if (!is_readable($filename)) {
            $installer = '/tmp/composer-setup.php';
            if (!copy('https://getcomposer.org/installer', $installer)) {
                throw new RuntimeException('Unable to download composer installer');
            }
            $expected = file_get_contents('https://composer.github.io/installer.sig');
            $actual = hash_file('sha384', $installer);
            if (!$expected == $actual) {
                throw new RuntimeException('Signature mismatch for composer installer');
            }
            $phpPath = Settings::get('binary.php');
            $process = new Process([$phpPath, $installer], '/tmp');
            $process->run();
            if (!$process->isSuccessful()) {
                throw new RuntimeException('Failed to run composer installer');
            }
            if (!unlink($installer)) {
                throw new RuntimeException('Unable to remove the composer installer file');
            }
        }

        return $filename;
    }
}

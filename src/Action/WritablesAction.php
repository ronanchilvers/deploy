<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use App\Action\HookableInterface;
use App\Action\Traits\Hookable;
use App\Builder;
use App\Facades\Log;
use App\Facades\Settings;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Utility\File;
use RuntimeException;

/**
 * Action to manage the writable locations for a project
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class WritablesAction extends AbstractAction implements
    ActionInterface,
    HookableInterface
{
    use Hookable;

    /**
     * @see App\Action\ActionInterface::run()
     */
    public function run(Config $configuration, Context $context)
    {
        $deployment    = $context->getOrThrow('deployment', 'Invalid or missing deployment');
        $deploymentDir = $context->getOrThrow('deployment_dir', 'Invalid or missing deployment directory');
        $writableMode  = Settings::get('build.chmod.writable_folder', Builder::MODE_WRITABLE_DIR);
        $writables     = $configuration->get('writables.paths', []);
        if (0 == count($writables)) {
            $this->info(
                $deployment,
                'No writables found in the deployment configuration'
            );
            return;
        }
        foreach ($writables as $writable) {
            $dir = realpath(File::join($deploymentDir, $writable));
            $this->info(
                $deployment,
                'Verifying writable ' . $writable
            );
            Log::debug("Working on writable", [
                'writable' => $writable,
                'writable_dir' => $dir,
            ]);
            if (!is_dir($dir)) {
                $this->error(
                    $deployment,
                    'Writable path does not exist',
                    [
                        'Writable - ' . $writable,
                        'Writable Folder - ' . $dir,
                    ]
                );
                Log::error("Writable doesn't exist", [
                    'writable' => $writable,
                    'writable_dir' => $dir,
                ]);
                throw new RuntimeException("Writable doesn't exist - " . $writable);
            }
            if (!chmod($dir, $writableMode)) {
                $this->error(
                    $deployment,
                    'Unable to chmod writable',
                    [
                        'Writable - ' . $writable,
                        'Writable Folder - ' . $dir,
                    ]
                );
                Log::error('Unable to chmod writable', [
                    'writable' => $writable,
                    'writable_dir' => $dir,
                ]);
                throw new RuntimeException('Unable to chmod writable dir ' . $writable);
            }
            $this->info(
                $deployment,
                'Writable ' . $writable . ' verified',
                [
                    'Writable - ' . $writable,
                    'Writable Folder - ' . $dir,
                ]
            );
        }
    }
}

<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
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
class WritablesAction extends AbstractAction implements ActionInterface
{
    /**
     * @see App\Action\ActionInterface::run()
     */
    public function run(Config $configuration, Context $context)
    {
        $releaseDir   = $context->getOrThrow('release_dir', 'Invalid or missing release directory');
        $writableMode = Settings::get('build.chmod.writable_folder', Builder::MODE_WRITABLE_DIR);
        $writables    = $configuration->get('writables', []);
        if (0 == count($writables)) {
            return;
        }
        foreach ($writables as $writable) {
            $dir = realpath(File::join($releaseDir, $writable));
            Log::debug("Working on writable", [
                'writable' => $writable,
                'writable_dir' => $dir,
            ]);
            if (!is_dir($dir)) {
                Log::error("Writable doesn't exist", [
                    'writable' => $writable,
                    'writable_dir' => $dir,
                ]);
                throw new RuntimeException("Writable doesn't exist - " . $writable);
            }
            if (!chmod($dir, $writableMode)) {
                Log::error('Unable to chmod writable', [
                    'writable' => $writable,
                    'writable_dir' => $dir,
                ]);
                throw new RuntimeException('Unable to chmod writable dir ' . $writable);
            }
        }
    }
}

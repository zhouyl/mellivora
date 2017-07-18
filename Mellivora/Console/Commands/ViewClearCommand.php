<?php

namespace Mellivora\Console\Commands;

use Mellivora\Console\Command;
use RuntimeException;

class ViewClearCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'view:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all compiled view files';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $path = $this->container['config']['view.compiled'];

        if (!$path) {
            throw new RuntimeException('View path not found.');
        }

        foreach (glob("{$path}/*") as $view) {
            unlink($view);
        }

        $this->info('Compiled views cleared!');
    }
}

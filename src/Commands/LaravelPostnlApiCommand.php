<?php

namespace Adequaat\LaravelPostnlApi\Commands;

use Illuminate\Console\Command;

class LaravelPostnlApiCommand extends Command
{
    public $signature = 'laravel-postnl-api';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}

<?php

namespace Haxibiao\Matomo\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{

    /**
     * The name and signature of the Console command.
     *
     * @var string
     */
    protected $signature = 'matomo:install';

    /**
     * The Console command description.
     *
     * @var string
     */
    protected $description = '安装 haxibiao/matomo';

    /**
     * Execute the Console command.
     *
     * @return void
     */
    public function handle()
    {

        $this->comment('发布 资源文件 ...');
        $this->call('matomo:publish', ['--force' => true]);

    }
}

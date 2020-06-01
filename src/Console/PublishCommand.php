<?php
namespace haxibiao\matomo;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matomo:publish {--force : 发布配置和资源文件}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发布 matomo resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // 就配置文件自定义后，不方便覆盖，更新需要单独
        // vendor:publish --tag=matomo-config --force=true

        $this->call('vendor:publish', [
            '--tag'   => 'matomo-config',
            '--force' => false,
        ]);

    }
}

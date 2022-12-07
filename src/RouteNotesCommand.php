<?php
/*
 * @Author       : lovefc
 * @Date         : 2022-12-06 19:34:27
 * @LastEditTime : 2022-12-07 19:24:26
 */

namespace lovefc\LaravelRouteNotes;

use Illuminate\Console\Command;

class RouteNotesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // 在win中,执行命令为 --p 要变成 -p
    protected $signature = 'notes:route
	                        {--p|path= : Controller directory}
							{--f|file= : Routing file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Laravel annotation route';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path = $this->option('path') ?? '';
        $filename = $this->option('file') ?? date("Y-m-d-His") . '.php';
        $path = base_path('app/Http/Controllers/' . $path);
        $file = base_path('routes/' . $filename);
        if (!is_dir($path)) {
            $this->error('Controller directory does not exist.');
            return;
        }
        if (!is_writable(base_path('routes'))) {
            $this->error('Route directory has no write permission.');
            return;
        }
        $code = RouteNotes::creRouteApi($path);
        if (file_put_contents($file, $code)) {
            $this->info('Route file routes/' . $filename . ' created successfully.');
        } else {
            $this->error('Routing file writing failed.');
        }
    }
}

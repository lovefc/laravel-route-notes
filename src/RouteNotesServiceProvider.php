<?php
/*
 * @Author       : lovefc
 * @Date         : 2022-12-07 02:02:42
 * @LastEditTime : 2022-12-07 15:39:39
 */

namespace lovefc\LaravelRouteNotes;

use Illuminate\Support\ServiceProvider;


class RouteNotesServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot()
    {
		// 判断是不是运行在控制台
        if (!$this->app->runningInConsole()) {
            return;
        }
        $this->commands([
            RouteNotesCommand::class,
        ]);
    }
}

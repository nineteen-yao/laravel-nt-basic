<?php
/**
 * 抽象命令行
 *
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021/10/29 9:37
 */

namespace YLarNtBasic\Console;


use Illuminate\Console\Command;
use Illuminate\Support\Str;

abstract class AbstractCommand extends Command
{
    use ErrorTrait;

    //protected $signature = 'command:test {params?}';   //这里只能交给子类写

    protected $args;

    protected $route;

    protected $defaultRoute = 'index';

    protected function start()
    {
        try {
            $params = trim($this->argument('params'));
            $this->args = explode(',', $params);

            $this->route = (empty($this->args) || empty($this->args[0])) ? $this->defaultRoute : $this->args[0];

            array_shift($this->args);

            call_user_func_array([$this, $this->route], $this->args);
        } catch (\Throwable $throwable) {
            $this->showErr($throwable);
        }
    }

    public function handle()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024m');

        if (Str::contains($this->signature, '{params')) {
            $this->start();
            return;
        }

        $this->index();
    }

    public function index(...$params)
    {
        //默认路由
    }
}

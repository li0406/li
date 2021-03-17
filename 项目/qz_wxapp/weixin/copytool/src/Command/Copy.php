<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/19 0019
 * Time: 17:42
 */

namespace App\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Copy\CopyDo;

class Copy extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('copy');
        $this->setDescription('复制小程序');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $CopyDo = new CopyDo();
        $targetDirArr = $CopyDo->copyMores();
        //$output->writeln(ROOT_PATH);
        dump($targetDirArr);
        //$output->writeln(dump($targetDirArr));
        $datetime = date('c');
        $output->writeln($datetime . " 复制成功");

        $output->writeln($datetime . " 修改配置成功");
    }
}
<?php

namespace app\command;

use app\common\model\Images;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class DeleteImages extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('deleteimages');
        // 设置参数

    }

    protected function execute(Input $input, Output $output)
    {
        $day = time() - 24*60*60;
        $images = Images::where('status', 0)
            ->field('id,url')
            ->where('create_time', '<', $day)
            ->select();
        foreach ($images as $v) {
            $deleteImage = Images::get($v['id']);
            $deleteImage->delete();
            $path = './public' . $v['url'];
            if (file_exists($path))
                unlink($path);
        }
    }
}

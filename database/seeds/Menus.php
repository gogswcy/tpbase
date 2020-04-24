<?php

use think\migration\Seeder;

class Menus extends Seeder
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $exist = db('menus')
            ->find();
        if ($exist)
            return;
        $data = [
            ['name' => '日志管理', 'action' => '', 'pid' => 0],
            ['name' => '日志列表', 'action' => '/admin/logs/index', 'pid' => 1],
        ];
        $this->table('menus')->insert($data)->save();
    }
}
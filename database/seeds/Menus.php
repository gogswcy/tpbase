<?php

use think\Db;
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
        Db::query('truncate table menus');
        $data = [
            ['id' => 1, 'name' => '通用', 'pid' => 0, 'action' => ''],
            ['id' => 2, 'name' => '通用', 'pid' => 1, 'action' => '/admin/common/index'],
            ['id' => 3, 'name' => '日志管理', 'action' => '', 'pid' => 0],
            ['id' => 4, 'name' => '日志列表', 'action' => '/admin/log/index', 'pid' => 3],
        ];
        $this->table('menus')->insert($data)->save();
    }
}
<?php

use think\migration\Seeder;

class Logs extends Seeder
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
        $data = [];
        for ($i = 0; $i < 25; $i++) {
            $data[] = [
                'action' => '日志管理',
                'name' => 'admin',
                'create_time' => time() - $i*24*60*60,
            ];
        }
        $this->table('logs')->insert($data)->save();
    }
}
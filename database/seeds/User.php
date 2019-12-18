<?php

use think\migration\Seeder;

class User extends Seeder
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
        $data = [
            'account' => 'admin',
            'name' => 'admin',
            'password' => '96e79218965eb72c92a549dd5a330112',
            'create_time' => 1575250175,
            'delete_time' => 0
        ];
        $this->table('user')->insert($data)->save();
    }
}

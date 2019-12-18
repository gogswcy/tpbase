<?php

use think\migration\Migrator;
use think\migration\db\Column;

class User extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        // create the table
        $table = $this->table('user', array('id' => true, 'comment' => '后台管理员表', 'engine' => 'InnoDB'));
        $table->addColumn('account', 'string', array(
            'limit' => 15, 'default' => '', 'comment' => '账户, 登录用'
        ))
            ->addColumn('name', 'string', array(
                'limit' => 15, 'default' => '', 'comment' => '用户姓名'
            ))
            ->addColumn('password', 'string', array(
                'limit' => 32, 'default' => md5('111111'), 'comment' => '用户密码'
            ))
            ->addColumn('identity', 'integer', array(
                'limit' => 2, 'default' => '2', 'comment' => '用户的身份, 默认2: 普通, 1: 超级管理员'
            ))
            ->addColumn('token', 'string', array(
                'limit' => 32, 'default' => '', 'comment' => '登陆token'
            ))
            ->addColumn('expire_time', 'integer', array(
                'limit' => 11, 'default' => 0, 'comment' => '登录cookie到期时间'
            ))
            ->addColumn('last_login_ip', 'integer', array(
                'limit' => 11, 'default' => 0, 'comment' => '最后登录IP'
            ))
            ->addColumn('last_login_time', 'integer', array(
                'default' => 0, 'comment' => '最后登录时间'
            ))
            ->addColumn('create_time', 'integer', array(
                'limit' => 11, 'default' => 0, 'comment' => '创建时间'
            ))
            ->addColumn('delete_time', 'integer', array(
                'limit' => 11, 'default' => 0, 'comment' => '删除时间'
            ))
            ->addIndex(array('account', 'token'), array('unique' => true))
            ->create();
    }
    public function down()
    {
        $this->dropTable('user');
    }
}

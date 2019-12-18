<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Logs extends Migrator
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
        $table = $this->table('logs', array('id' => true, 'comment' => '日志表', 'engine' => 'MyISAM'));
        $table->addColumn('action', 'string', array(
            'limit' => 20, 'default' => '', 'comment' => '模块的名称',
        ))
            ->addColumn('name', 'string', array(
                'limit' => 15, 'default' => '', 'comment' => '操作员的名称',
            ))
            ->addColumn('create_time', 'integer', array(
                'limit' => 11, 'null' => true, 'comment' => '操作的时间'
            ))
            ->create();
    }
    public function down()
    {
        $this->dropTable('logs');
    }
}

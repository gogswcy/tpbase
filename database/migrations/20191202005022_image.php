<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Image extends Migrator
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
        $table = $this->table('image', array('id' => true, 'comment' => '所有的上传图片表', 'engine' => 'InnoDB'));
        $table->addColumn('url', 'string', array(
            'limit' => 255, 'default' => '', 'comment' => '图片的地址'
        ))
            ->addColumn('status', 'integer', array(
                'limit' => 1, 'default' => 0, 'comment' => '是否使用, 0: 未使用, 1: 已使用'
            ))
            ->addColumn('create_time', 'integer', array(
                'limit' => 11, 'default' => 0, 'comment' => '创建时间'
            ))
            ->addColumn('delete_time', 'integer', array(
                'limit' => 11, 'default' => 0, 'comment' => '删除时间'
            ))
            ->addIndex(array('url',), array('unique' => true))
            ->create();
    }
    public function down()
    {
        $this->dropTable('image');
    }
}
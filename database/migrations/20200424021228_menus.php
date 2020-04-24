<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Menus extends Migrator
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
        $table = $this->table('menus', ['id' => true, 'comment' => '菜单表', 'engine' => 'InnoDB']);
        $table->addColumn('name', 'string', [
            'limit' => 50, 'default' => '', 'comment' => '名称'
        ])->addColumn('action', 'string', [
            'limit' => 50, 'default' => '', 'comment' => '跳转路径'
        ])->addColumn('pid', 'integer', [
            'limit' => 11, 'default' => 0, 'comment' => '上级id'
        ])->addColumn('sort', 'integer', [
            'limit' => 11, 'default' => 0, 'comment' => '排序:倒序'
        ])->create();
    }

    public function down()
    {
        $this->dropTable('menus');
    }
}

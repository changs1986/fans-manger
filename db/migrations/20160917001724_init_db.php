<?php

use Phinx\Migration\AbstractMigration;

class InitDb extends AbstractMigration
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
        $table = $this->table('cx_customer');
        $table->addColumn('uid', 'integer')
              ->addColumn('openid', 'string', array('limit' => 32, 'default' => ''))
              ->addColumn('unionid', 'string', array('limit' => 32, 'default' => ''))
              ->addColumn('sex', 'integer', array('limit' => MysqlAdapter::INT_TINY, 'default' => 0, 'comment' => '1male 2female'))
              ->addColumn('province', 'string', array('limit' => 10, 'default' => ''))
              ->addColumn('city', 'string', array('limit' => 18, 'default' => ''))
              ->addColumn('nickname', 'string', array('limit' => 30, 'default' => ''))
              ->addColumn('remark', 'string', array('limit' => 50, 'default' => ''))
              ->addColumn('groupid', 'integer');

    }
}

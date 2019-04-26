<?php


use Phinx\Migration\AbstractMigration;

class Releases extends AbstractMigration
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
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $projects = $this->table('releases');
        $projects
            ->addColumn('project', 'integer')
            ->addColumn('number', 'integer', [
                'default' => 0,
                'null' => false
            ])
            ->addColumn('status', 'string', [
                'length' => 20,
                'null' => false
            ])
            ->addTimestamps()
            ->addIndex(['project'])
            ->create();
    }
}

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
        $projects = $this->table('releases', [
            'id' => 'release_id',
        ]);
        $projects
            ->addColumn('release_project', 'integer')
            ->addColumn('release_number', 'integer', [
                'default' => 0,
                'null' => false
            ])
            ->addColumn('release_sha', 'string', [
                'length' => 64,
                'null' => false
            ])
            ->addColumn('release_author', 'string', [
                'length' => 256,
                'null' => false
            ])
            ->addColumn('release_message', 'string', [
                'length' => 1024,
                'null' => false
            ])
            ->addColumn('release_configuration', 'string', [
                'length' => 4096,
                'null' => false
            ])
            ->addColumn('release_status', 'string', [
                'length' => 20,
                'null' => false
            ])
            ->addColumn('release_started', 'datetime', [
                'null' => true,
                'default' => null
            ])
            ->addColumn('release_finished', 'datetime', [
                'null' => true,
                'default' => null
            ])
            ->addColumn('release_failed', 'datetime', [
                'null' => true,
                'default' => null
            ])
            ->addTimestamps('release_created', 'release_updated')
            ->addIndex(['release_project'])
            ->create();
    }
}

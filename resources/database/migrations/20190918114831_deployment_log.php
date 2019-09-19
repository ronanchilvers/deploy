<?php

use Phinx\Migration\AbstractMigration;

class DeploymentLog extends AbstractMigration
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
        $projects = $this->table('events', [
            'id' => 'event_id',
        ]);
        $projects
            ->addColumn('event_deployment', 'integer')
            ->addColumn('event_type', 'string', [
                'length' => 20,
                'null' => false
            ])
            ->addColumn('event_header', 'string', [
                'length' => 1024,
                'null' => false
            ])
            ->addColumn('event_detail', 'text', [
                'null' => true
            ])
            ->addTimestamps('event_created', 'event_updated')
            ->addIndex(['event_deployment'])
            ->create();
    }
}

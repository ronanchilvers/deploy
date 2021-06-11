<?php


use Phinx\Migration\AbstractMigration;

class Deployments extends AbstractMigration
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
        $deployments = $this->table('deployments', [
            'id' => 'deployment_id',
        ]);
        $deployments
            ->addColumn('deployment_project', 'integer')
            ->addColumn('deployment_number', 'integer', [
                'default' => 0,
                'null' => false
            ])
            ->addColumn('deployment_sha', 'string', [
                'length' => 64,
                'null' => false
            ])
            ->addColumn('deployment_author', 'string', [
                'length' => 1024,
                'null' => false
            ])
            ->addColumn('deployment_message', 'string', [
                'length' => 1024,
                'null' => false
            ])
            ->addColumn('deployment_configuration', 'string', [
                'length' => 4096,
                'null' => false
            ])
            ->addColumn('deployment_status', 'string', [
                'length' => 20,
                'null' => false
            ])
            ->addColumn('deployment_started', 'datetime', [
                'null' => true,
                'default' => null
            ])
            ->addColumn('deployment_finished', 'datetime', [
                'null' => true,
                'default' => null
            ])
            ->addColumn('deployment_failed', 'datetime', [
                'null' => true,
                'default' => null
            ])
            ->addTimestamps('deployment_created', 'deployment_updated')
            ->addIndex(['deployment_project'])
            ->create();
    }
}

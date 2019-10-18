<?php


use Phinx\Migration\AbstractMigration;

class Projects extends AbstractMigration
{
    /**
     * Change Method
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function change()
    {
        $projects = $this->table('projects', [
            'id' => 'project_id',
        ]);
        $projects
            ->addColumn('project_name', 'string', ['length' => 256, 'null' => false])
            ->addColumn('project_token', 'string', ['length' => 64, 'null' => false])
            ->addColumn('project_key', 'string', ['length' => 1024, 'null' => false])
            ->addColumn('project_provider', 'string', ['length' => 15, 'null' => false])
            ->addColumn('project_repository', 'string', ['length' => 1024, 'null' => false])
            ->addColumn('project_branch', 'string', ['length' => 1024, 'null' => false])
            ->addColumn('project_last_number', 'integer', ['null' => true, 'default' => null])
            ->addColumn('project_last_deployment', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('project_last_author', 'string', ['length' => 256, 'null' => true, 'default' => null])
            ->addColumn('project_last_sha', 'string', ['length' => 64, 'null' => true, 'default' => null])
            ->addColumn('project_last_status', 'string', ['length' => 20, 'null' => true, 'default' => null])
            ->addTimestamps('project_created', 'project_updated')
            ->create();
    }
}

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
        $projects = $this->table('projects');
        $projects
            ->addColumn('name', 'string')
            ->addColumn('notes', 'text')
            ->addColumn('token', 'string', [ 'length' => 64, 'null' => false ])
            ->addColumn('keep_releases', 'integer', [ 'default' => 5 ])
            ->addColumn('provider', 'string', [ 'length' => 15, 'null' => false ])
            ->addColumn('repository', 'string', [ 'length' => 1024, 'null' => false ])
            ->addColumn('last_deployment', 'datetime', [ 'null' => true, 'default' => null ])
            ->addTimestamps()
            ->create();
    }
}

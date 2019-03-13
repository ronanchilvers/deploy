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
            ->addTimestamps()
            ->create();
    }
}

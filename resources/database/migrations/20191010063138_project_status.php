<?php

use Phinx\Migration\AbstractMigration;

class ProjectStatus extends AbstractMigration
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function change()
    {
        $table = $this->table('projects');
        $table
            ->addColumn('project_status', 'string', [
                'length' => 20,
                'null'   => false,
                'default'=> 'active',
                'after'  => 'project_branch',
            ])
            ->update();
    }
}

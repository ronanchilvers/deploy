<?php

use Phinx\Migration\AbstractMigration;

class DeploymentOriginal extends AbstractMigration
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function change()
    {
        $table = $this->table('deployments');
        $table
            ->addColumn('deployment_original', 'integer', [
                'null'   => false,
                'default'=> 0,
                'after'  => 'deployment_number',
            ])
            ->update();
    }
}

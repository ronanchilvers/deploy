<?php

use Phinx\Migration\AbstractMigration;

class RecordCommitter extends AbstractMigration
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function change()
    {
        $table = $this->table('deployments');
        $table
            ->addColumn(
                'deployment_committer',
                'string',
                [
                    'length' => 1024,
                    'null'   => false,
                ]
            )
            ->update();
    }
}

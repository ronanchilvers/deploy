<?php

use Phinx\Migration\AbstractMigration;

class Users extends AbstractMigration
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function change()
    {
        $table = $this->table('users', [
            'id' => 'user_id',
        ]);
        $table
            ->addColumn('user_email', 'string', [
                'length' => 1024,
                'null'   => false,
            ])
            ->addColumn('user_password', 'string', [
                'length' => 128,
                'null'   => false,
            ])
            ->addColumn('user_status', 'string', [
                'length'  => 24,
                'null'    => false,
            ])
            ->addColumn('user_preferences', 'text', [
                'null'    => true,
            ])
            ->addTimestamps('user_created', 'user_updated')
            ->addIndex(['user_email'])
            ->create();
    }
}

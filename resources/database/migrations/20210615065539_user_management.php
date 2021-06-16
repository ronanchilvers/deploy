<?php
declare(strict_types=1);

use App\Model\User;
use Phinx\Migration\AbstractMigration;

final class UserManagement extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $users = $this->table(User::table());
        $users
            ->addColumn('user_level', 'string', [
                'length' => 16,
                'null'   => false,
                'after'  => 'user_preferences',
            ])
            ->addColumn('user_last_login', 'datetime', [
                'null' => true,
                'default' => null,
                'after'  => 'user_level',
            ])
            ->update();
    }
}

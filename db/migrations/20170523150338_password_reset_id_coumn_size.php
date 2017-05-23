<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class PasswordResetIdCoumnSize extends AbstractMigration
{
  
    /**
     * Migrate Up.
     */
    public function up()
    {
        $passwordReset = $this->table('pa2_user_reset_request');
        $passwordReset->changeColumn('id', 'integer', array('limit' => MysqlAdapter::INT_MEDIUM))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
	// Delete the data, which means any ongoing resets will need to be re-done
	// To avoid duplicate key problems that can arise when trucating ID values
	$this->execute('delete from pa2_user_reset_request');

        $passwordReset = $this->table('pa2_user_reset_request');
        $passwordReset->changeColumn('id', 'integer', array('limit' => MysqlAdapter::INT_TINY))
              ->save();
    }
}

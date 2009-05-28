<?php

namespace Doctrine\Tests\DBAL\Functional\Schema;

use Doctrine\Tests\TestUtil;
use Doctrine\DBAL\Schema;
use Doctrine\DBAL\Types\Type;

require_once __DIR__ . '/../../../TestInit.php';
 
class MysqlSchemaManagerTest extends \Doctrine\Tests\DbalFunctionalTestCase
{
    private $_conn;

    protected function setUp()
    {
        $this->_conn = TestUtil::getConnection();
        if ($this->_conn->getDatabasePlatform()->getName() !== 'mysql')
        {
            $this->markTestSkipped('The MySqlSchemaTest requires the use of mysql');
        }
        $this->_sm = $this->_conn->getSchemaManager();
    }

    public function testListDatabases()
    {
        try {
            $this->_sm->dropDatabase('test_mysql_create_database');
        } catch (\Exception $e) {}

        $this->_sm->createDatabase('test_mysql_create_database');

        $databases = $this->_sm->listDatabases();
        $this->assertEquals(true, in_array('test_mysql_create_database', $databases));
    }

    public function testListFunctions()
    {
        try {
            $this->_sm->listFunctions();
        } catch (\Exception $e) {
            return;
        }
 
        $this->fail('Sqlite listFunctions() should throw an exception because it is not supported');
    }

    public function testListTriggers()
    {
        try {
            $this->_sm->listTriggers();
        } catch (\Exception $e) {
            return;
        }
 
        $this->fail('Sqlite listTriggers() should throw an exception because it is not supported');
    }

    public function testListSequences()
    {
        $columns = array(
            'id' => array(
                'type' => Type::getType('integer'),
                'autoincrement' => true,
                'primary' => true,
                'notnull' => true
            ),
            'test' => array(
                'type' => Type::getType('string'),
                'length' => 255
            )
        );

        $options = array();

        try {
            $this->_sm->dropTable('list_sequences_test');
        } catch (\Exception $e) {}

        $this->_sm->createTable('list_sequences_test', $columns, $options);

        $sequences = $this->_sm->listSequences();
        $this->assertEquals(true, in_array('list_sequences_test', $sequences));
    }

    public function testListTableConstraints()
    {
        $columns = array(
            'id' => array(
                'type' => Type::getType('integer'),
                'autoincrement' => true,
                'primary' => true,
                'notnull' => true
            ),
            'test' => array(
                'type' => Type::getType('string'),
                'length' => 255
            )
        );

        $options = array();

        try {
            $this->_sm->dropTable('list_table_constraints_test');
        } catch (\Exception $e) {}

        $this->_sm->createTable('list_table_constraints_test', $columns, $options);

        $tableConstraints = $this->_sm->listTableConstraints('list_table_constraints_test');

        $this->assertEquals(array('PRIMARY'), $tableConstraints);
    }

    public function testListTableColumns()
    {
        $columns = array(
            'id' => array(
                'type' => Type::getType('integer'),
                'autoincrement' => true,
                'primary' => true,
                'notnull' => true
            ),
            'test' => array(
                'type' => Type::getType('string'),
                'length' => 255
            )
        );

        $options = array();

        try {
            $this->_sm->dropTable('list_tables_test');
        } catch (\Exception $e) {}

        $this->_sm->createTable('list_tables_test', $columns, $options);

        $columns = $this->_sm->listTableColumns('list_tables_test');

        $this->assertEquals('id', $columns[0]['name']);
        $this->assertEquals(true, $columns[0]['primary']);
        $this->assertEquals('Doctrine\DBAL\Types\IntegerType', get_class($columns[0]['type']));
        $this->assertEquals(4, $columns[0]['length']);
        $this->assertEquals(false, $columns[0]['unsigned']);
        $this->assertEquals(false, $columns[0]['fixed']);
        $this->assertEquals(true, $columns[0]['notnull']);
        $this->assertEquals(null, $columns[0]['default']);

        $this->assertEquals('test', $columns[1]['name']);
        $this->assertEquals(false, $columns[1]['primary']);
        $this->assertEquals('Doctrine\DBAL\Types\StringType', get_class($columns[1]['type']));
        $this->assertEquals(255, $columns[1]['length']);
        $this->assertEquals(false, $columns[1]['unsigned']);
        $this->assertEquals(false, $columns[1]['fixed']);
        $this->assertEquals(false, $columns[1]['notnull']);
        $this->assertEquals(null, $columns[1]['default']);
    }

    public function testListTableIndexes()
    {
        $columns = array(
            'id' => array(
                'type' => Type::getType('integer'),
                'autoincrement' => true,
                'primary' => true,
                'notnull' => true
            ),
            'test' => array(
                'type' => Type::getType('string'),
                'length' => 255
            )
        );

        $options = array(
            'indexes' => array(
                'test_index_name' => array(
                    'fields' => array(
                        'test' => array()
                    ),
                    'type' => 'unique'
                )
            )
        );

        try {
            $this->_sm->dropTable('list_table_indexes_test');
        } catch (\Exception $e) {}

        $this->_sm->createTable('list_table_indexes_test', $columns, $options);

        $tableIndexes = $this->_sm->listTableIndexes('list_table_indexes_test');

        $this->assertEquals('test_index_name', $tableIndexes[0]['name']);
        $this->assertEquals('test', $tableIndexes[0]['column']);
        $this->assertEquals(true, $tableIndexes[0]['unique']);
    }

    public function testListTables()
    {
        $columns = array(
            'id' => array(
                'type' => Type::getType('integer'),
                'autoincrement' => true,
                'primary' => true,
                'notnull' => true
            ),
            'test' => array(
                'type' => Type::getType('string'),
                'length' => 255
            )
        );

        $options = array();

        try {
            $this->_sm->dropTable('list_tables_test');
        } catch (\Exception $e) {}

        $this->_sm->createTable('list_tables_test', $columns, $options);

        $tables = $this->_sm->listTables();

        $this->assertEquals(true, in_array('list_tables_test', $tables));
    }

    public function testListUsers()
    {
        $users = $this->_sm->listUsers();
        $this->assertEquals(true, is_array($users));
        $params = $this->_conn->getParams();
        $testUser = $params['user'];
        $found = false;
        foreach ($users as $user) {
            if ($user['user'] == $testUser) {
                $found = true;
            }
        }
        $this->assertEquals(true, $found);
    }

    public function testListViews()
    {
        try {
            $this->_sm->dropView('test_create_view');
        } catch (\Exception $e) {}

        $this->_sm->createView('test_create_view', 'SELECT * from mysql.user');
        $views = $this->_sm->listViews();

        $this->assertEquals('test_create_view', $views[0]['name']);
        $this->assertEquals('/* ALGORITHM=UNDEFINED */ select `mysql`.`user`.`Host` AS `Host`,`mysql`.`user`.`User` AS `User`,`mysql`.`user`.`Password` AS `Password`,`mysql`.`user`.`Select_priv` AS `Select_priv`,`mysql`.`user`.`Insert_priv` AS `Insert_priv`,`mysql`.`user`.`Update_priv` AS `Update_priv`,`mysql`.`user`.`Delete_priv` AS `Delete_priv`,`mysql`.`user`.`Create_priv` AS `Create_priv`,`mysql`.`user`.`Drop_priv` AS `Drop_priv`,`mysql`.`user`.`Reload_priv` AS `Reload_priv`,`mysql`.`user`.`Shutdown_priv` AS `Shutdown_priv`,`mysql`.`user`.`Process_priv` AS `Process_priv`,`mysql`.`user`.`File_priv` AS `File_priv`,`mysql`.`user`.`Grant_priv` AS `Grant_priv`,`mysql`.`user`.`References_priv` AS `References_priv`,`mysql`.`user`.`Index_priv` AS `Index_priv`,`mysql`.`user`.`Alter_priv` AS `Alter_priv`,`mysql`.`user`.`Show_db_priv` AS `Show_db_priv`,`mysql`.`user`.`Super_priv` AS `Super_priv`,`mysql`.`user`.`Create_tmp_table_priv` AS `Create_tmp_table_priv`,`mysql`.`user`.`Lock_tables_priv` AS `Lock_tables_priv`,`mysql`.`user`.`Execute_priv` AS `Execute_priv`,`mysql`.`user`.`Repl_slave_priv` AS `Repl_slave_priv`,`mysql`.`user`.`Repl_client_priv` AS `Repl_client_priv`,`mysql`.`user`.`Create_view_priv` AS `Create_view_priv`,`mysql`.`user`.`Show_view_priv` AS `Show_view_priv`,`mysql`.`user`.`Create_routine_priv` AS `Create_routine_priv`,`mysql`.`user`.`Alter_routine_priv` AS `Alter_routine_priv`,`mysql`.`user`.`Create_user_priv` AS `Create_user_priv`,`mysql`.`user`.`ssl_type` AS `ssl_type`,`mysql`.`user`.`ssl_cipher` AS `ssl_cipher`,`mysql`.`user`.`x509_issuer` AS `x509_issuer`,`mysql`.`user`.`x509_subject` AS `x509_subject`,`mysql`.`user`.`max_questions` AS `max_questions`,`mysql`.`user`.`max_updates` AS `max_updates`,`mysql`.`user`.`max_connections` AS `max_connections`,`mysql`.`user`.`max_user_connections` AS `max_user_connections` from `mysql`.`user`', $views[0]['sql']);
    }

    public function testListTableForeignKeys()
    {
        // Create table that has foreign key
        $columns = array(
            'id' => array(
                'type' => Type::getType('integer'),
                'autoincrement' => true,
                'primary' => true,
                'notnull' => true
            ),
            'test' => array(
                'type' => Type::getType('integer'),
                'length' => 4
            )
        );

        $options = array('type' => 'innodb');

        try {
            $this->_sm->dropTable('list_table_foreign_keys_test2');
        } catch (\Exception $e) {}

        $this->_sm->createTable('list_table_foreign_keys_test2', $columns, $options);

        // Create the table that is being referenced in the foreign key
        $columns = array(
            'id' => array(
                'type' => Type::getType('integer'),
                'autoincrement' => true,
                'primary' => true,
                'notnull' => true
            ),
            'whatever' => array(
                'type' => Type::getType('string'),
                'length' => 255
            )
        );

        $options = array('type' => 'innodb');

        try {
            $this->_sm->dropTable('list_table_foreign_keys_test');
        } catch (\Exception $e) {}

        $this->_sm->createTable('list_table_foreign_keys_test', $columns, $options);

        // Create the foreign key between the tables
        $definition = array(
            'name' => 'testing',
            'local' => 'test',
            'foreign' => 'id',
            'foreignTable' => 'list_table_foreign_keys_test'
        );
        $this->_sm->createForeignKey('list_table_foreign_keys_test2', $definition);

        $tableForeignKeys = $this->_sm->listTableForeignKeys('list_table_foreign_keys_test2');
        $this->assertEquals(1, count($tableForeignKeys));
        $this->assertEquals('list_table_foreign_keys_test', $tableForeignKeys[0]['table']);
        $this->assertEquals('test', $tableForeignKeys[0]['local']);
        $this->assertEquals('id', $tableForeignKeys[0]['foreign']);
    }
}
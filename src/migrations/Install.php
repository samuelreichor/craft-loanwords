<?php

namespace samuelreichor\loanwords\migrations;

use Craft;
use craft\db\Migration;
use samuelreichor\loanwords\Constants;

class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public string $driver;

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    /**
     * @return bool
     */
    protected function createTables(): bool
    {
        $tablesCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema(Constants::TABLE_MAIN);
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                Constants::TABLE_MAIN,
                [
                    'id' => $this->primaryKey(),
                    'siteId' => $this->integer()->notNull(),
                    'loanword' => $this->string()->notNull(),
                    'lang' => $this->string(10)->notNull(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );
        }

        return $tablesCreated;
    }

    protected function removeTables(): void
    {
        $this->dropForeignKey('loanwords-siteId', Constants::TABLE_MAIN);
        $this->dropTableIfExists(Constants::TABLE_MAIN);
    }

    protected function addForeignKeys(): void
    {
        $this->addForeignKey(
            'loanwords-siteId',
            Constants::TABLE_MAIN,
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            null,
            Constants::TABLE_MAIN,
            'id',
            '{{%elements}}',
            'id',
            'CASCADE',
            null
        );
    }
}

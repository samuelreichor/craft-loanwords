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
                    'title' => $this->string(),
                    'lang' => $this->string(10),
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
        $this->dropTableIfExists(Constants::TABLE_MAIN);
    }

    protected function addForeignKeys(): void
    {
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

<?php

namespace App\Core;

abstract class Service
{
    /**
     * Start a database transaction.
     */
    protected function beginTransaction(): bool
    {
        return Database::getInstance()->beginTransaction();
    }

    /**
     * Commit the active database transaction.
     */
    protected function commit(): bool
    {
        return Database::getInstance()->commit();
    }

    /**
     * Rollback the active database transaction.
     */
    protected function rollBack(): bool
    {
        return Database::getInstance()->rollBack();
    }
}

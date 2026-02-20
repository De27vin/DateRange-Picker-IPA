<?php

namespace App\Overrides\MySqlConnector;

use Illuminate\Database\Connectors\MySqlConnector as BaseMySqlConnector;

class CustomMySqlConnector extends BaseMySqlConnector
{
    /**
     * Establish a PDO connection based on the configuration.
     *
     * @param  array  $config
     * @return \PDO
     */
    public function connect(array $config)
    {
        $pdo = parent::connect($config);

        // Set app modes
        $pdo->exec("SET sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';");

        return $pdo;
    }
}

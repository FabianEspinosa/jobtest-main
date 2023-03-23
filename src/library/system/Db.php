<?php

namespace App\System;

class DB
{
    private $connection;

    public function __construct($config)
    {
        if ($config->data('DB_HOSTNAME')) {
            $this->connection = new \mysqli($config->data('DB_HOSTNAME'), $config->data('DB_USERNAME'), $config->data('DB_PASSWORD'), $config->data('DB_DATABASE'), $config->data('DB_PORT', 3306));

            if ($this->connection->connect_error) {
                echo 'error connecting to database';
                var_dump($this->connection->connect_error);
                throw new \Exception('Error: ' . $this->connection->error . '<br />Error No: ' . $this->connection->errno);
            }

            if (!defined('DB_LOG')) {
                define('DB_LOG', false);
            }

            $this->connection->set_charset("utf8");
            $this->connection->query("SET SQL_MODE = ''");
        }
    }

    public function query($sql)
    {
        $query = $this->connection->query($sql);

        if (DB_LOG) {
            $filename = DIR_LOGS . 'sql.log';
            $actual   = file_get_contents($filename);
            $actual .= $sql . ";\n\n";
            file_put_contents($filename, $actual);
        }

        if (!$this->connection->errno) {
            if ($query instanceof \mysqli_result) {
                $data = array();

                while ($row = $query->fetch_assoc()) {
                    $data[] = $row;
                }

                $result           = new \stdClass();
                $result->num_rows = $query->num_rows;
                $result->row      = isset($data[0]) ? $data[0] : array();
                $result->rows     = $data;
                $result->sql      = $sql;

                $query->close();

                return $result;
            } else {
                return true;
            }
        } else {
            throw new \Exception('Error: ' . $this->connection->error . '<br />Error No: ' . $this->connection->errno . '<br />' . $sql);
        }
    }

    public function escape($value)
    {
        return $this->connection->real_escape_string($value);
    }

    public function countAffected()
    {
        return $this->connection->affected_rows;
    }

    public function getLastId()
    {
        return $this->connection->insert_id;
    }

    public function connected()
    {
        return $this->connection->ping();
    }

    public function __destruct()
    {
        $this->connection->close();
    }
}

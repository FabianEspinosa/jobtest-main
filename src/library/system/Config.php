<?php

namespace App\System;

class Config
{
    protected $data = [];

    public function __construct()
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(APP_PATH);
        $dotenv->load();

        foreach ($_ENV as $key => $val) {
            $this->data[$key] = $val;
        }
    }

    public function data($key, $default = null)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        } elseif (!is_null($default)) {
            return $default;
        }

        return null;
    }
}

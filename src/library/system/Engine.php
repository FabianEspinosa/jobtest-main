<?php

namespace App\System;

use App\System\Config as Config;
use App\System\Db as Db;
use ICanBoogie\Inflector;

class Engine
{
    public $config;
    public $db;
    public $inflector;

    public function __construct()
    {
        $this->config = new Config();
        $this->db = new Db($this->config);

        $this->inflector = Inflector::get();
    }
}

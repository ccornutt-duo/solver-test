<?php

namespace App;

use Illuminate\Database\Capsule\Manager as Capsule;

class Database
{
    public static function init()
    {
        $capsule = new Capsule;
        $config = require __DIR__ . '/../config/database.php';
        
        $capsule->addConnection([
            'driver'   => $config['driver'],
            'database' => $config['database'],
            'charset'  => $config['charset'],
            'collation' => $config['collation'],
            'prefix'   => $config['prefix'],
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        
        return $capsule;
    }
}

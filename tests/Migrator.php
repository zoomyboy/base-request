<?php

namespace Zoomyboy\BaseRequest\Tests;

use Illuminate\Database\Migrations\Migrator as BaseMigrator;
use Illuminate\Support\Str;

class Migrator extends BaseMigrator {
    /**
     * Resolve a migration instance from a file.
     *
     * @param  string  $file
     * @return object
     */
    public function resolve($file)
    {
        $class = '\\Zoomyboy\\BaseRequest\\Tests\\Migrations\\'.Str::studly(implode('_', array_slice(explode('_', $file), 4)));

        return new $class;
    }
}

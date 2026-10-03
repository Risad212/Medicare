<?php

namespace App\Support;

class Module
{
    public static function enabled(string $name): bool
    {
        return (bool) config("modules.{$name}", false);
    }
}

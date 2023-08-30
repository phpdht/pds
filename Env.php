<?php

class Env
{
    static public function get($name, $d = '')
    {
        $env = getenv($name);
        return $env ?: $d;
    }

}
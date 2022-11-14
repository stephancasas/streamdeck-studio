<?php

namespace App\Lib\FontAwesome\Support;

use Illuminate\Support\Facades\Cache;

class AnonymousCache
{
    public function __construct($prefix, $key)
    {
        $this->prefix = $prefix;
        $this->key = $key;
    }

    public function get()
    {
        return Cache::get("$this->prefix$this->key");
    }

    public function put($value, $ttl = null)
    {
        return Cache::put("$this->prefix$this->key", $value, $ttl);
    }
}

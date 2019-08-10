<?php
$memcache_obj = new Memcache;
$memcache_obj->connect('127.0.0.1', 11211) or die ("Could not connect");
$memcache_obj->add("name","123",false,30);
//$memcache_obj->set("name","123",false,30);
//$memcache_obj->delete("name");
//$memcache_obj->get("name");
var_dump($memcache_obj->get("name"));
<?php
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler;
$mc=new Memcached();
$mc->addServer("127.0.0.1", 11211);
$storage = new NativeSessionStorage(array(), new MemcachedSessionHandler($mc));
$session = new Session($storage);
$session->start();
$sid=$session->getId();
$sessions=json_decode(file_get_contents('./data/sessions'));
$my_session= new stdClass;
if (isset($sessions->$sid)) $my_session=$sessions->$sid;

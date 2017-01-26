<?php
define("LINK_PREFIX",base64_encode("contacts_".getcwd()));
define("LINK_PATH","fake_ws/");
$mc=new Memcached();
$mc->addServer("127.0.0.1", 11211);
if($mc->get(LINK_PREFIX.'nots')===false) $mc->set(LINK_PREFIX.'nots',array());
if($mc->get(LINK_PREFIX.'subs')===false) $mc->set(LINK_PREFIX.'subs',array());
if($mc->get(LINK_PREFIX.'verrous')===false) $mc->set(LINK_PREFIX.'verrous',array());
if($mc->get(LINK_PREFIX.'cache')===false) $mc->set(LINK_PREFIX.'cache',array());
if($mc->get(LINK_PREFIX.'nots_locked')===false) $mc->set(LINK_PREFIX."nots_locked",0);
if($mc->get(LINK_PREFIX.'subs_locked')===false) $mc->set(LINK_PREFIX."subs_locked",0);
if($mc->get(LINK_PREFIX.'link_locked')===false) $mc->set(LINK_PREFIX."link_locked",0);
if($mc->get(LINK_PREFIX.'cache_locked')===false) $mc->set(LINK_PREFIX."cache_locked",0);
include(LINK_PATH.'fake_ws.php');
include(LINK_PATH.'context_router.php');


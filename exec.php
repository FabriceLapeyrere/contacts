<?php
if (PHP_SAPI === 'cli')
{
    foreach (glob("server/*.php") as $filename)
    {
        include $filename;
    }
    include 'fake_ws/conf.php';
    include 'conf/main.php';
    
	$action=$argv[1];

	include "server/cli/$action.php";

}


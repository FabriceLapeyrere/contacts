<?php
if (PHP_SAPI === 'cli')
{
	chdir(__DIR__);
	require __DIR__ . '/vendor/autoload.php';
	foreach (glob("server/*.php") as $filename)
	{
		include $filename;
	}
	include 'conf/main.php';
	
	$action=$argv[1];

	include "server/cli/$action.php";

}


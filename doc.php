<?php
require __DIR__ . '/vendor/autoload.php';
foreach (glob("server/*.php") as $filename)
{
	include $filename;
}
include 'conf/main.php';
include 'conf/auth.php';
$type=$_POST['type'];
include "server/doc/$type.php";


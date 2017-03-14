<?php
foreach (glob("server/*.php") as $filename)
{
	include $filename;
}
include 'fake_ws/conf.php';
include 'conf/main.php';
include 'conf/auth.php';
	$type=$_POST['type'];
	include "server/doc/$type.php";


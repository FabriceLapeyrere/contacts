<?php
require __DIR__ . '/vendor/autoload.php';
foreach (glob("server/*.php") as $filename)
{
    include $filename;
}
include 'conf/main.php';
if (isset($_REQUEST['auth_cle'])) $auth_cle=$_REQUEST['auth_cle'];
if (isset($_REQUEST['action'])) $action=$_REQUEST['action'];

if (isset($_REQUEST['params'])) $params=json_decode($_REQUEST['params'],true);
else $params=$_REQUEST;
error_log("services : $auth_cle, server/services/$action.php\n".var_export($params,true)."\n",3,'../data/log/services.log');

if ($auth_cle=="1A2Z3E4R5T6Y") {
	$S['user']=array(
		'login'=>'admin',
		'name'=>'admin',
		'id'=>1
	);
	include "server/services/$action.php";
}

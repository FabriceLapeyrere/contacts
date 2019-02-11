<?php
include 'session.php';
if (!isset($my_session->user)){
	$reponse['auth']=false;
	echo json_encode($reponse);
	exit;
}
$reponse['auth']=true;
$reponse['user']=$my_session->user;

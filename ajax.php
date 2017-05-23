<?php
set_time_limit(0);
if (!file_exists('./data/log')) mkdir('./data/log', 0777, true);
$t0=microtime(true);
$t=$t0*10000%10000;
$params=json_decode(file_get_contents('php://input'));
$reponse=array();
foreach (glob("server/*.php") as $filename)
{
    include $filename;
}
include 'fake_ws/conf.php';
include 'conf/main.php';
include 'conf/auth.php';

$uid=$params->uid;
$datas=$params->data;
$res=array();
error_log("---------------------------------------------\n:::: ".$uid." :::: AJAX CALL 1 $t\n",3,"./data/log/link.log");
WS::clear_old_subs();
while(WS::link_locked()){
    usleep(20000);
}
WS::link_lock();
if (is_array($datas)) {
	foreach($datas as $data) {
		//d'abord les verrous
		if ($data->action=='del_verrou') {
		    WS::del_verrou($uid,$data->type);
		}
		if ($data->action=='set_verrou') {
		    WS::set_verrou($uid,$data->verrou);
		}
		if ($data->action=='kill_me') {
		    WS::del_sub($data->uid);
		    WS::del_old_verrous();
		}
	}    //ensuite le modele
	foreach($datas as $data) {
		if ($data->action!='del_verrou' && $data->action!='set_verrou' && $data->action!='kill_me' && $data->action!='update_contexts') {
			$func=$data->action;
			$res[]=Actions::$func($data->params);
			if (strpos($func,'add')===0 || strpos($func,'mod')===0 || strpos($func,'del')===0 || strpos($func,'mov')===0 || strpos($func,'pub')===0) {
				$log=array(
					'user'=>$S['user'],
					'date'=>millisecondes(),
					'params'=>$params
				);
				error_log(json_encode($log)."\n", 3, "./data/log/log.txt");
                CR::maj(array('log'));
			}
		}
	}
	foreach($datas as $data) {
		//enfin les notifications
		if ($data->action=='update_contexts') {
		    WS::contexts_update($uid,$data->contexts);
		}
	}
}
WS::link_unlock();
$t1=microtime(true);
$reponse['dt']=$t1-$t0;
$reponse['res']=$res;
echo json_encode($reponse);
error_log("---------------------------------------------\n:::: ".$uid." :::: AJAX END 1 $t\n",3,"./data/log/link.log");

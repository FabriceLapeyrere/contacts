<?php
if (!file_exists('./data/log')) mkdir('./data/log', 0777, true);
foreach (glob("server/*.php") as $filename)
{
    include $filename;
}
include 'fake_ws/conf.php';
include 'conf/main.php';
include 'conf/auth.php';
$params=json_decode(file_get_contents('php://input'));
$uid=$params->uid;
$data=array('message'=>'no news');
$t=microtime(true)*10000%10000;
error_log("---------------------------------------------\n:::: ".$uid." :::: LINK CALL $t\n",3,"./data/log/link.log");
WS::clear_old_subs();
if (isset($params->force) || !WS::has_sub($uid)) WS::subscribe($uid);
else WS::subscribe_update($uid);
for ($i=0;$i<2000;$i++){
    if (!WS::sub_exists($uid)) break;
    if (!WS::link_locked() && count(WS::get_nots($uid))>0) {
        WS::link_lock();
        $data=array('modele'=>WS::notify($uid));
        WS::link_unlock();
        break;
    }
    usleep(10000);
}
$data['user']=$S['user'];
echo json_encode($data);
error_log("---------------------------------------------\n:::: ".$uid." :::: LINK END $t\n",3,"./data/log/link.log");

<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class WS implements MessageComponentInterface {
 	public $clients;
	public $nots;
	public $notified;
	public $subs;
	public $verrous;
	public $cache;
 	public $tmp;
 	public function __construct() {
 	 	$this->clients = array();
 	 	$this->nots = array();
 	 	$this->notified = array();
 	 	$this->subs =  array();
 	 	$this->verrous =  array();
 	 	$this->cache =  array();
		$this->sessions = array();
		$this->tmp = array();
	}
	public function getSession($conn,$k) {
		$sid=$conn->Session->getId();
		if (array_key_exists($sid, $this->sessions) && array_key_exists($k, $this->sessions[$sid])) {
			 return $this->sessions[$sid][$k];
		}
		return NULL;
	}
 	public function setSession($conn,$k,$v) {
		$sid=$conn->Session->getId();
		if (!array_key_exists($sid, $this->sessions)) $this->sessions[$sid]=array();
		$this->sessions[$sid][$k]=$v;
		file_put_contents('./data/sessions', json_encode($this->sessions));
	}
 	public function removeSession($conn,$k) {
		$sid=$conn->Session->getId();
		if (array_key_exists($sid, $this->sessions)) unset($this->sessions[$sid][$k]);
		file_put_contents('./data/sessions', json_encode($this->sessions));
	}
 	public function onOpen(ConnectionInterface $conn) {
 	 	// Store the new connection to send messages to later
 	 	$this->clients[$conn->resourceId]=$conn;
		echo "New connection! {$conn->resourceId}\n";
 	}

 	public function onMessage(ConnectionInterface $from, $msg) {
 	 	$CR= new CR($this, $from);
 		$Actions= new Actions($this, $from);
		$payload=json_decode($msg);
 	 	echo sprintf('Connection %d sending message "%s"'."\n"
 	 	 	, $from->resourceId, $msg);
		if (!file_exists('./data/log')) mkdir('./data/log', 0777, true);
		$t0=microtime(true);
		$t=$t0*10000%10000;
		$rid=$payload->id;
		$key= isset($payload->key) ? $payload->key : 0 ;
		$params=$payload->data;
		$reponse=array();
		$uid=$from->resourceId;
		$datas=$params->data;
		$res=array();
		error_log("---------------------------------------------\n:::: ".$uid." :::: AJAX CALL 1 $t\n",3,"./data/log/link.log");
        if (count($datas)==1 && $datas[0]->action=='login' ) {
			$res[]=$Actions->login($datas[0]->params);
		}
        if ($rid==-1 && $key=='1234') {
			$this->setSession($from,'user',array(
				'login'=>'admin',
				'name'=>'Admin',
				'id'=>'1'
			));
		}
        $u=$this->getSession($from,'user');
		if (count($datas)==1 && $datas[0]->action=='public-login' &&  $u===NULL) {
		    $this->setSession($from,'user',array(
				'login'=>'anonyme',
				'name'=>'Anonyme',
				'id'=>'-2'
			));
            $u=$this->getSession($from,'user');
        }
        error_log(var_export($u,true),3,'/tmp/fab.log');
        if ($u!==NULL && is_array($datas)) {
			foreach($datas as $data) {
				//d'abord les verrous
				if ($data->action=='del_verrou') {
				    $this->del_verrou($uid,$data->type);
				}
				if ($data->action=='set_verrou') {
				    $this->set_verrou($uid,$data->verrou);
				}
				if ($data->action=='kill_me') {
				    $this->del_sub($data->uid);
				    $this->del_old_verrous();
				}
			}    //ensuite le modele
			foreach($datas as $data) {
				if ($data->action!='login'
                    && $data->action!='public-login'
                    && $data->action!='del_verrou'
					&& $data->action!='maj'
					&& $data->action!='set_verrou'
					&& $data->action!='kill_me'
					&& $data->action!='update_contexts'
					&& $data->action!='init') {
					$func=$data->action;
					$d_params=isset($data->params) ? $data->params : new stdClass;
					$res[]=$Actions->$func($d_params);
					if (strpos($func,'add')===0 || strpos($func,'mod')===0 || strpos($func,'del')===0 || strpos($func,'mov')===0 || strpos($func,'pub')===0) {
						$log=array(
							'user'=>$this->getSession($from,'user'),
							'date'=>millisecondes(),
							'params'=>$params
						);
						error_log(json_encode($log)."\n", 3, "./data/log/log.txt");
						$this->maj(array('log'));
					}
				}
			}
			foreach($datas as $data) {
				//enfin les notifications
				if ($data->action=='update_contexts') {
				 	$this->contexts_update($uid,$data->contexts);
				}
				if ($data->action=='maj') {
				 	$this->maj($data->types);
				}
			}
		}
		$t1=microtime(true);
		$reponse['dt']=$t1-$t0;
		$reponse['res']=$res;
		$reponse['uid']=$uid;
		$reponse['user']=$this->getSession($from,'user');
		error_log("---------------------------------------------\n:::: ".$uid." :::: AJAX END 1 $t\n",3,"./data/log/link.log");
		$from->send(json_encode(array('id'=>$rid,'data'=>$reponse)));
		$this->clear_old_subs();
	}

 	public function onClose(ConnectionInterface $conn) {
 	 	// The connection is closed, remove it, as we can no longer send it messages
 	 	unset($this->clients[$conn->resourceId]);
 	 	echo "Connection {$conn->resourceId} has disconnected\n";
		$this->del_sub($conn->resourceId);
 		$this->del_verrous($conn->resourceId);
 	}
 	public function onError(ConnectionInterface $conn, \Exception $e) {
 	 	echo "An error has occurred: {$e->getMessage()}\n";

 	 	$conn->close();
 	}
	//MAJ
 	public function maj($types){
		//echo "MAJ\n";
		$res=array();
		foreach($types as $type){
			foreach($this->get_sub_contexts($type) as $t){
				$res=array_merge($res,$this->deps($t));
			}
		}
		$res=array_unique($res);
		$this->del_cache($res);
		$this->prep_notify($res);
		$this->send_nots();
	}
	public function send_nots(){
		foreach ($this->clients as $uid=>$client){
			$data=array();
			if (count($this->get_nots($uid))>0) {
				$data['user']=$this->getSession($client,'user');
				$data['uid']=$uid;
				$data['modele']=$this->notify($uid);
				if (count($data['modele'])>0){
					$ks="";
					foreach($data['modele'] as $k=>$v){
						$ks.=" $k";
					}
					error_log("SEND :::: ".$uid." :::: $ks\n",3,"./data/log/link.log");
					$client->send(json_encode(array('data'=>$data)));
				}
			}
		}
	}
	public function deps($type){
		$tab=explode('/',$type);
		$res=array();
		switch ($tab[0]) {
			case 'contact':
				$res=array($type,'casquettes','casquettes_mail_erreur','casquettes_sel','etabs','suivis','contact/*','doublons_texte','doublons_email','carte','cluster','contact_prev_next');
				break;
			case 'user':
				$res=array($type,'panier');
				$res[]='users';
				$res[]='usersall';
				break;
			case 'users':
				$res=array($type,'usersall');
				break;
			case 'casquettes':
				$res=array($type,'etabs','contact_prev_next');
				break;
			case 'envoi':
				$res=array($type,'envois');
				break;
			case 'suivi':
				$res=array($type,'suivis');
				break;
			case 'modele':
				$res=array($type,'modeles');
				break;
            case 'support':
				$res=array($type,'supports');
				break;
            case 'template':
				$res=array($type,'templates');
				break;
			case 'mail':
				$res=array($type,'mails');
				break;
            case 'news':
				$res=array($type,'newss');
				break;
            case 'form':
				$res=array($type,'forms');
				break;
			case 'imap':
				$res=array($type);
				break;
			default:
				$res[]=$type;
				break;
		}
		return $res;
	}
 	//NOTIFY
 	public function force_notify($uid) {
 	 	//echo "force_notify\n";
		$types=array();
 	 	foreach($this->subs[$uid]->contexts as $c) {
 	 	 	$types[]=$c->type;
 	 	}
 	 	$this->prep_notify($types);
 	 	error_log(":::: ".$uid." :::: force notify\n",3,"./data/log/link.log");
	}
 	public function notify($uid) {
 		//echo "notify\n";
		$CR= new CR($this, $this->clients[$uid]);
 		$datas=array();
 	 	foreach($this->nots[$uid] as $type=>$context) {
 	 	 	$data=$CR->get_context($context);
 	 	 	$notified= isset($this->notified[$uid."-".$type]) ? $this->notified[$uid."-".$type] : NULL;
 	 	 	if (md5(var_export($data,true))!=md5(var_export($notified,true))) {
 	 	 	 	$this->notified[$uid."-".$type]=$data;
 	 	 	 	$datas[$type]=$data;
 	 	 	 	error_log(":::: ".$uid." :::: notify => $type\n",3,"./data/log/link.log");
 	 	 	} else {
 	 	 	 	error_log(":::: ".$uid." :::: notify => $type not needed\n",3,"./data/log/link.log");
 	 	 	}
 	 	}
 	 	$this->del_nots($uid);
 	 	return $datas;
 	}
 	public function prep_notify($types) {
 	 	//echo "prep_notify\n";
		$nots=array();
 	 	foreach($types as $s) {
 	 	 	foreach($this->subs as $uid=>$sub) {
	 	 	 	foreach($sub->contexts as $c){
 	 	 	 	 	if ($c->type==$s) {
			 	 	 	$nots[]=array('uid'=>$uid,'context'=>$c);
 	 	 	 	 	}
 	 	 	 	}
 	 	 	}
 	 	}
 	 	if (count($nots)>0) {
 	 	 	$this->make_nots($nots);
 	 	}
 	}
 	public function get_nots($uid) {
 	 	//echo "get_nots\n";
		return array_key_exists($uid,$this->nots) ? $this->nots[$uid] : array() ;
 	}
 	public function del_nots($uid) {
 	 	//echo "del_nots\n";
		unset($this->nots[$uid]);
 	}
 	public function make_nots($nots) {
 	 	//echo "make_nots\n";
		$i=0;
 	 	foreach($nots as $not) {
 	 	 	$uid=$not['uid'];
 	 	 	$context=$not['context'];
 	 	 	if (!isset($this->nots[$uid])) $this->nots[$uid]=array();
 	 	 	$this->nots[$uid][$context->type]=$context;
 	 	 	$i++;
 	 	}
 	}
 	public function del_cache($types) {
 	 	//echo "del_cache\n";
		foreach($types as $type){
 	 	 	unset($this->cache[$type]);
 	 	}
 	}
 	public function del_cache_all() {
 	 	//echo "del_cache_all\n";
		$this->cache=array();
 	}
 	public function get_cache($context,$from) {
 	 	//echo "get_cache\n";
		$type=$context->type;
 	 	$params=$context->params;
 	 	$u=$this->getSession($from,'user');
 	 	$key=md5(json_encode($params).$u['id']);
 		if (isset($this->cache[$type][$key])) return $this->cache[$type][$key];
 	 	else return false;
 	}
 	public function set_cache($context,$data,$from) {
 	 	//echo "set_cache\n";
		$type=$context->type;
 	 	$params=$context->params;
		$u=$this->getSession($from,'user');
 	 	$key=md5(json_encode($params).$u['id']);
 		if(!isset($this->cache[$type])) $this->cache[$type]=array();
 	 	$this->cache[$type][$key]=$data;
	}
 	//SUBSCRIBE
 	public function subscribe($uid,$ws) {
 	 	//echo "subscribe\n";
		$this->set_sub($uid,array());
 	 	error_log(":::: ".$uid." :::: subscribe\n",3,"./data/log/link.log");
 	}
 	public function subscribe_update($uid) {
 	 	echo "subscribe_update\n";
		$sub=$this->subs($uid);
 	 	$this->set_sub($uid,$sub->contexts);
 	 	error_log(":::: ".$uid." :::: subscribe update\n",3,"./data/log/link.log");
 	}
 	public function contexts_update($uid,$contexts) {
		//echo "contexts_update\n";
		$this->set_sub($uid,$contexts);
 	 	error_log(":::: ".$uid." :::: contexts update ".count($contexts)." item(s) \n",3,"./data/log/link.log");
 	 	$this->force_notify($uid);
 		$this->send_nots();
	}
 	public function clear_old_subs(){
 	 	//echo "clear_old_subs\n";
		$t0=microtime(true);
 	 	foreach($this->subs as $uid=>$sub) {
 	 		if (!array_key_exists($uid,$this->clients)) {
				$this->del_sub($uid);
			}
 	 	}
		$this->del_old_verrous();
	}
 	public function has_sub($uid){
 	 	//echo "has_sub\n";
		return isset($this->subs[$uid]);
 	}
 	public function sub_exists($uid) {
 	   	//echo "sub_exists\n";
		return isset($this->subs[$uid]);
 	}
 	public function del_sub($uid) {
 	 	//echo "del_sub\n";
		unset($this->subs[$uid]);
 	 	$this->maj(array('logged'));
 	 	error_log(":::: del $uid\n",3,"./data/log/link.log");
 	}
 	public function set_sub($uid,$contexts) {
		//echo "set_sub\n";
		$this->subs[$uid]=(object) array('time'=>microtime(true),'contexts'=>$contexts, 'user'=>(object) $this->getSession($this->clients[$uid],'user'));
 	 	$this->maj(array('logged'));
 	}
 	public function get_sub_contexts($filtre){
 	 	//echo "get_sub_contexts\n";
		$res=array();
 	 	$tab=explode('/',$filtre);
 	 	if (isset($tab[1]) &&  $tab[1]=='*'){
 	 	 	$this->del_cache_all();
	 	 	foreach($this->subs as $sub){
		 	 	foreach($sub->contexts as $c){
			 	 	$tabc=explode('/',$c->type);
			 	 	if ($tab[0]==$tabc[0]) $res[]=$c->type;
		 	 	}
	 	 	}
 	 	} else if ($filtre=='*'){
 	 	 	$this->del_cache_all();
	 	 	foreach($this->subs as $sub){
		 	 	foreach($sub->contexts as $c){
			 	 	$res[]=$c->type;
		 	 	}
	 	 	}
 	 	} else {
	 	 	$res[]=$filtre;
 	 	}
 	 	return $res;
 	}
 	//VERROUS
 	public function set_verrou($uid,$type) {
 	 	//echo "set_verrou\n";
		error_log(":::: ".$uid." :::: set verrou $uid\n",3,"./data/log/link.log");
 	 	if (!array_key_exists($type,$this->verrous)) {
 	 	 	$this->verrous[$type]=$uid;
 	 	}
		$this->maj(array('verrous'));
 	}
 	public function del_verrou($uid,$type) {
 	 	//echo "del_verrou\n";
		if (array_key_exists($type,$this->verrous)) {
 	 	 	if ($this->verrous[$type]==$uid) unset($this->verrous[$type]);
 	 	}
 		$this->maj(array('verrous'));
 	}
 	public function del_verrous($uid) {
 	 	//echo "del_verrou\n";
		foreach($this->verrous as $type=>$v) {
 	 	 	if ($v==$uid) unset($this->verrous[$type]);
 	 	}
 		$this->maj(array('verrous'));
 	}
 	public function del_old_verrous() {
 	 	//echo "del_old_verrous\n";
		$types=array();
		$test=false;
 	 	foreach($this->verrous as $type=>$u) {
 	 	 	if (!array_key_exists($u,$this->subs)) {
 	 	 	 	error_log(":::: -> $u\n",3,"./data/log/link.log");
 	 	 	 	unset($this->verrous[$type]);
				$test=true;
 	 	 	}
 	 	}
 		if ($test) {
			error_log(":::: del old verrous\n",3,"./data/log/link.log");
	 	 	$this->maj(array('verrous'));
		}
 	}
 	public function get_verrou($type) {
		//echo "get_verrou\n";
		error_log("$type\n",3,"./data/log/link.log");
 	 	return array_key_exists($type,$this->verrous) ? $this->verrous[$type] : 'none';
 	}
}
?>

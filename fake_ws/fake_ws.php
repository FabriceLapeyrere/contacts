<?php
	class WS
	{
        //NOTIFY
        public static function force_notify($uid) {
            global $t;
            $sub=WS::get_sub($uid);
            $types=array();
            foreach($sub->contexts as $c) {
                $types[]=$c->type;
            }
            WS::prep_notify($types);
            error_log(":::: ".$uid." :::: force notify $t\n",3,"./data/log/link.log");
        }
        public static function notify($uid) {
            global $mc,$t;
            $datas=new stdClass();
            foreach(WS::get_nots($uid) as $type=>$context) {
                $data=CR::get_context($context);
                $notified=$mc->get("notified-".$uid."-".$type);
                if (md5(var_export($data,true))!=md5(var_export($notified,true))) {
                    $mc->set("notified-".$uid."-".$type,$data);
                    $datas->$type=$data;
                    error_log(":::: ".$uid." :::: notify => $type $t\n",3,"./data/log/link.log");
                } else {
                    error_log(":::: ".$uid." :::: notify => $type not needed $t\n",3,"./data/log/link.log");
                }
            }
	        WS::del_nots($uid);
            return $datas;
        }
        public static function prep_notify($types) {
            global $t;
            $subs=WS::get_subs();
            $nots=array();
            foreach($types as $s) {
                foreach($subs as $uid=>$sub) {
		            foreach($sub->contexts as $c){
                        if ($c->type==$s) {
				            $nots[]=array('uid'=>$uid,'context'=>$c);
                        }
                    }
                }
            }
            if (count($nots)>0) {
                WS::make_nots($nots);
            }
        }
        public static function get_nots($uid) {
            global $mc;
            $nots=$mc->get(LINK_PREFIX."nots");
            return array_key_exists($uid,$nots) ? $nots[$uid] : array() ;
        }
        public static function del_nots($uid) {
            global $mc;
            while($mc->get(LINK_PREFIX."nots_locked")==1){
                usleep(20000);
            }
            $mc->set(LINK_PREFIX."nots_locked",1);
            $nots=$mc->get(LINK_PREFIX."nots");
            unset($nots[$uid]);
            $mc->set(LINK_PREFIX."nots",$nots);
            $mc->set(LINK_PREFIX."nots_locked",0);
        }
        public static function make_nots($nots) {
            global $mc;
            while($mc->get(LINK_PREFIX."nots_locked")==1){
                usleep(20000);
            }
            $mc->set(LINK_PREFIX."nots_locked",1);
            $cnots=$mc->get(LINK_PREFIX."nots");
            $time=microtime(true);
            $i=0;
            foreach($nots as $not) {
                $uid=$not['uid'];
                $context=$not['context'];
                if (!isset($cnots[$uid])) $cnots[$uid]=array();
                $cnots[$uid][$context->type]=$context;
                $i++;
            }
            $mc->set(LINK_PREFIX."nots",$cnots);
            $mc->set(LINK_PREFIX."nots_locked",0);
        }
        public static function link_lock() {
            global $mc;
            $mc->set(LINK_PREFIX."link_locked",1,5);
        }
        public static function link_unlock() {
            global $mc;
            $mc->delete(LINK_PREFIX."link_locked");
        }
        public static function link_locked() {
            global $mc;
            return $mc->get(LINK_PREFIX."link_locked");
        }

        public static function del_cache($types) {
            global $mc;
            while($mc->get(LINK_PREFIX."cache_locked")==1){
                usleep(20000);
            }
            $mc->set(LINK_PREFIX."cache_locked",1);
            $cache=$mc->get(LINK_PREFIX."cache");
            foreach($types as $type){
                unset($cache[$type]);
            }
            $mc->set(LINK_PREFIX."cache",$cache);
            $mc->set(LINK_PREFIX."cache_locked",0);
        }
        public static function del_cache_all() {
            global $mc;
            while($mc->get(LINK_PREFIX."cache_locked")==1){
                usleep(20000);
            }
            $mc->set(LINK_PREFIX."cache_locked",1);
            $mc->set(LINK_PREFIX."cache",array());
            $mc->set(LINK_PREFIX."cache_locked",0);
        }
        public static function get_cache($context) {
            global $mc;
            $type=$context->type;
            $params=$context->params;
            $key=md5(json_encode($params));
            $cache=$mc->get(LINK_PREFIX."cache");
            if (isset($cache[$type][$key])) return $cache[$type][$key];
            else return false;
        }
        public static function set_cache($context,$data) {
            global $mc;
            $type=$context->type;
            $params=$context->params;
            $key=md5(json_encode($params));
            while($mc->get(LINK_PREFIX."cache_locked")==1){
                usleep(20000);
            }
            $mc->set(LINK_PREFIX."cache_locked",1);
            $cache=$mc->get(LINK_PREFIX."cache");
            if(!isset($cache[$type])) $cache[$type]=array();
            $cache[$type][$key]=$data;
            $mc->set(LINK_PREFIX."cache",$cache);
            $mc->set(LINK_PREFIX."cache_locked",0);
        }
        //SUBSCRIBE
        public static function subscribe($uid) {
	        global $t;
            WS::set_sub($uid,array());
            error_log(":::: ".$uid." :::: subscribe $t\n",3,"./data/log/link.log");
            CR::maj(array('logged'));
        }
        public static function subscribe_update($uid) {
	        global $t;
            $sub=WS::get_sub($uid);
            WS::set_sub($uid,$sub->contexts);
            error_log(":::: ".$uid." :::: subscribe update $t\n",3,"./data/log/link.log");
        }
        public static function contexts_update($uid,$contexts) {
	        WS::set_sub($uid,$contexts);
            error_log(":::: ".$uid." :::: contexts update ".count($contexts)." item(s) \n",3,"./data/log/link.log");
            WS::force_notify($uid);
        }
        public static function clear_old_subs(){
            global $t;
            $t0=microtime(true);
            foreach(WS::get_subs() as $uid=>$sub) {
                if ($t0-$sub->time>120) {
                    error_log(":::: clear old sub $t\n",3,"./data/log/link.log");
                    WS::del_sub($uid);
                    WS::del_old_verrous();
                }
	        }
        }
        public static function has_sub($uid){
            $subs=WS::get_subs();
            return isset($subs[$uid]);
        }
        public static function get_subs() {
            global $mc;
	        return $mc->get(LINK_PREFIX."subs");
        }
        public static function get_sub($uid) {
           	$subs=WS::get_subs();
            return $subs[$uid];
        }
        public static function sub_exists($uid) {
           	$subs=WS::get_subs();
            return isset($subs[$uid]);
        }
        public static function del_sub($uid) {
            global $mc;
            while($mc->get(LINK_PREFIX."subs_locked")==1){
                usleep(20000);
            }
            $mc->set(LINK_PREFIX."subs_locked",1);
            $subs=WS::get_subs();
            unset($subs[$uid]);
            $mc->set(LINK_PREFIX."subs",$subs);
            $mc->set(LINK_PREFIX."subs_locked",0);
            CR::maj(array('logged'));
            error_log(":::: del $uid\n",3,"./data/log/link.log");
        }
        public static function set_sub($uid,$contexts) {
            global $mc, $S;
            while($mc->get(LINK_PREFIX."subs_locked")==1){
                usleep(20000);
            }
            $mc->set(LINK_PREFIX."subs_locked",1);
            $subs=$mc->get(LINK_PREFIX."subs");
            $subs[$uid]=(object) array('time'=>microtime(true),'contexts'=>$contexts, 'user'=>(object) $S['user']);
            $mc->set(LINK_PREFIX."subs",$subs);
            $mc->set(LINK_PREFIX."subs_locked",0);
        }
        public static function get_sub_contexts($filtre){
	        $res=array();
	        $tab=explode('/',$filtre);
	        if (isset($tab[1]) &&  $tab[1]=='*'){
                WS::del_cache_all();
		        $subs=WS::get_subs();
		        foreach($subs as $sub){
			        foreach($sub->contexts as $c){
				        $tabc=explode('/',$c->type);
				        if ($tab[0]==$tabc[0]) $res[]=$c->type;
			        }
		        }
	        } else if ($filtre=='*'){
                WS::del_cache_all();
		        $subs=WS::get_subs();
		        foreach($subs as $sub){
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
        public static function set_verrou($uid,$type) {
            global $t, $mc;
            error_log(":::: ".$uid." :::: set verrou $uid $t\n",3,"./data/log/link.log");
            $verrous=$mc->get(LINK_PREFIX."verrous");
            if ($verrous===false) $mc->set(LINK_PREFIX."verrous",array());
            if (!array_key_exists($type,$verrous)) {
                $verrous[$type]=$uid;
                $mc->set(LINK_PREFIX."verrous",$verrous);
                CR::maj(array(CR::context_verrou($type)));
            }
        }
        public static function del_verrou($uid,$type) {
            global $t, $mc;
            error_log(":::: ".$uid." :::: del verrou $uid $t\n",3,"./data/log/link.log");
            $verrous=$mc->get(LINK_PREFIX."verrous");
            if ($verrous===false) $mc->set(LINK_PREFIX."verrous",array());
            if (array_key_exists($type,$verrous)) {
                if ($verrous[$type]==$uid) unset($verrous[$type]);
                $mc->set(LINK_PREFIX."verrous",$verrous);
                CR::maj(array(CR::context_verrou($type)));
            }
        }
        public static function del_old_verrous() {
            global $t, $mc;
            error_log(":::: del verrous $t\n",3,"./data/log/link.log");
            $verrous=$mc->get(LINK_PREFIX."verrous");
            $subs=WS::get_subs();
            $types=array();
            foreach($verrous as $type=>$u) {
                if (!array_key_exists($u,$subs)) {
                    error_log(":::: -> $u\n",3,"./data/log/link.log");
                    unset($verrous[$type]);
                    $mc->set(LINK_PREFIX."verrous",$verrous);
                    $types[]=CR::context_verrou($type);
                }
            }
            CR::maj($types);
        }
        public static function get_verrou($type) {
            global $mc;
            $verrous=$mc->get(LINK_PREFIX."verrous");
            if ($verrous===false) $mc->set(LINK_PREFIX."verrous",array());
            return array_key_exists($type,$verrous) ? $verrous[$type] : 'none';
        }
	}
?>

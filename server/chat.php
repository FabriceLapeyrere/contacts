<?php
	class Chat
	{
		protected $WS;
		protected $from;
		public function __construct($WS,$from) {
	 	 	$this->WS= $WS;
	 	 	$this->from= $from;
		}
		public function send_message($params,$id) {
			$t=Chat::do_send_message($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_send_message($params,$id) {
			$db= new DB();
			$id_from=$params->id_from;
			$id_to=$params->id_to;
			$message=$params->message;
			$insert = $db->database->prepare('INSERT INTO chat (id_from, id_to, message, creationdate, modificationdate) VALUES (?,?,?,?,?)');
			$t=millisecondes();
			$insert->execute(array($id_from, $id_to, $message, $t, $t));
			$id = $db->database->lastInsertId();
			
			$params->id_user=$id_from;
			$params->id_corresp=$id_to;
			Chat::do_set_lus($params);
			return array('maj'=>array('chat'),'res'=>$id);
		}
		public function mod_message($params,$id) {
			$t=Chat::do_mod_message($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_mod_message($params,$id) {
			$db= new DB();
			$id=$params->message->id;
			$message=$params->message->message;
			$update = $db->database->prepare('UPDATE chat SET message=?, modificationdate=? WHERE id=?');
			$update->execute(array($message, millisecondes(), $id));
			$params->id=$id;
			return array('maj'=>array('chat'),'res'=>$id);
		}
		public function set_lus($params,$id) {
			$t=Chat::do_set_lus($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_set_lus($params,$id) {
			$db= new DB();
			$id_user=$params->id_user;
			$id_corresp=$params->id_corresp;
			$query = "SELECT * FROM lus WHERE id_user=$id_user and id_corresp=$id_corresp";
			$id=-1;
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$id=$row['id'];
			}
			if ($id>=0) {
				$update = $db->database->prepare('UPDATE lus set date=? where id=?');
				$update->execute(array(millisecondes(), $id));
			} else {
				$update = $db->database->prepare('INSERT INTO lus (date, id_user, id_corresp) VALUES (?,?,?)');
				$update->execute(array(millisecondes(), $id_user, $id_corresp));   
			}
			return array('maj'=>array('chat'),'res'=>1);
		}
		public static function get_chat($id) {
			$db= new DB();
			$reponse=array();
			$query = "SELECT * FROM chat WHERE id_from=$id OR id_to=$id OR id_to IN (SELECT -id_group from user_group where id_user=$id)";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				if ($row['id_to']<0) {
					if(!isset($res[$row['id_to']])) $res[$row['id_to']]=array();
					$res[$row['id_to']][$row['id']]=$row;
				} else {
					$corresp=0;
					if ($row['id_to']!=$id) $corresp=$row['id_to'];
					if ($row['id_from']!=$id) $corresp=$row['id_from'];
					if(!isset($res[$corresp])) $res[$corresp]=array();
					$res[$corresp][$row['id']]=$row;
				}
			}
			$reponse['collection']=$res;
			$query = "SELECT * FROM lus WHERE id_user=$id";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res[$row['id_corresp']]=$row['date'];
			}
			$reponse['lus']=$res;
			return $reponse;
		}
	}

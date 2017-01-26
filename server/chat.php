<?php
	class Chat
	{
		public static function send_message($params) {
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
			Chat::set_lus($params);
            CR::maj(array('chat'));
			return $id;
		}
		public static function mod_message($params) {
			$db= new DB();
			$id=$params->message->id;
			$message=$params->message->message;
			$update = $db->database->prepare('UPDATE chat SET message=?, modificationdate=? WHERE id=?');
			$update->execute(array($message, millisecondes(), $id));
			$params->id=$id;
            CR::maj(array('chat'));
		}
		public static function set_lus($params) {
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
            CR::maj(array("chat"));
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

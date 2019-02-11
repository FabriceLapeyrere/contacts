<?php
$conf=conf();
$msg=array();
$msg['id']=-1;
$msg['key']='1234';
$msg['data']=json_decode('{"data":[{"action":"update_contexts", "contexts":[{"type":"logged"}]}]}');

\Ratchet\Client\connect('ws://localhost:'.$conf->ws_port)->then(function($conn) use ($msg) {
	echo "Connexion OK";
	$conn->on('message', function($m) use ($conn) {
        	$res=json_decode($m,true);
		if (isset($res['data']['modele']['logged']['byUid'])) {
			if (count($res['data']['modele']['logged']['byUid'])>1) {
				echo "\nUtilisateurs en ligne :\n";
				foreach($res['data']['modele']['logged']['byUid'] as $k=>$u) {
					if ($k!=$res['data']['uid']) echo "$k -> {$u['name']}, {$u['login']}, {$u['id']}\n";
				}
				echo "----\n";
			}
			if (count($res['data']['modele']['logged']['byUid'])==1) {
				echo "\nPersonne en ligne\n";
				echo "----\n";
			}
		}
        });
	$conn->send(json_encode($msg));
}, function ($e) {
	echo "Could not connect: {$e->getMessage()}\n";
});

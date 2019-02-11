<?php
$msg=base64_decode($argv[2]);
$conf=conf();
\Ratchet\Client\connect('ws://localhost:'.$conf->ws_port)->then(function($conn) use ($msg) {
	echo "Connexion OK";
	$conn->send($msg);
	$conn->close();
}, function ($e) {
	echo "Could not connect: {$e->getMessage()}\n";
});

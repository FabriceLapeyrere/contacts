<?php
$host=$argv[2];
$msg=base64_decode($argv[3]);
$conf=conf();
\Ratchet\Client\connect("ws://$host:8082")->then(function($conn) use ($msg) {
	echo "Connexion OK";
	$conn->send($msg);
	$conn->close();
}, function ($e) {
	echo "Could not connect: {$e->getMessage()}\n";
});

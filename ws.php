<?php
require __DIR__ . '/vendor/autoload.php';
foreach (glob("server/*.php") as $filename)
{
	include $filename;
}
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Session\SessionProvider;
use Symfony\Component\HttpFoundation\Session\Storage\Handler;
if (!file_exists('./data/log')) mkdir('./data/log', 0777, true);	
$db= new DB(true);
$conf=conf();
$mc=new Memcached();
$mc->addServer("127.0.0.1", 11211);
$session = new SessionProvider(
        new WsServer(new WS())
      , new Handler\MemcachedSessionHandler($mc)
    );
$server = IoServer::factory(
	new HttpServer($session),
	$conf->ws_port
);
echo $conf->ws_port."\n";
$server->run();

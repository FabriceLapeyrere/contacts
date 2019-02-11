<?php
use GO\Scheduler;

// Create a new scheduler
$scheduler = new Scheduler();
// ... configure the scheduled jobs (see below) ...
$envois=Mailing::get_schedules(1);
foreach($envois as $e) {
	$scheduler->call(
		function ($s) {
			$params=new stdClass;
			$params->id_envoi=$s['id_item'];
			$delete=Mailing::do_del_schedule($params);
			WS_maj($delete['maj']);
			Mailing::start_envoi($s['id_item'],$s['by']);
			return "Envoi n°{$s['id_item']} démarré\n";
		},
		array('envoi'=>$e),
		'envoi'
	)->date(date('Y-m-d H:i',$e['json']->date/1000))->output('./data/log/scheduler.log');
}

// Let the scheduler execute jobs which are due.
$scheduler->run();

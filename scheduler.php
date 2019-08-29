<?php require_once __DIR__.'/vendor/autoload.php';

use GO\Scheduler;

// Create a new scheduler
$scheduler = new Scheduler();

// ... configure the scheduled jobs (see below) ...

$scheduler->call(
    function ($args) {
	error_log("cool\n",3,"/tmp/fab.log");
	return $args['user'];
    },
    [
        'user' => 'cool',
    ],
    'myCustomIdentifier'
)->date('2018-11-30 16:44')->output('../data/log/scheduler.log');

// Let the scheduler execute jobs which are due.
$scheduler->run();

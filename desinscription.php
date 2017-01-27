<?php
foreach (glob("server/*.php") as $filename)
{
    include $filename;
}
include 'fake_ws/conf.php';
include 'conf/main.php';

function mail_utf8($to, $subject = '(No subject)', $message = '', $header = '') {
  $header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
  mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header_ . $header);
}

if (isset($_REQUEST['hash'])) {
	$params=json_decode(base64_decode($_REQUEST['hash']),true);
	$emails=$params['emails'];
	$nb=0;
	foreach($emails as $email){
		$nb=Contacts::remove_mail($email,1);
	}
	if ($nb>0) {
		foreach(explode(",",$C->app->mails_notification->value) as $dest){
			mail_utf8(trim($dest),"Désinscription automatique",implode(", ",$emails),'From: '.$C->app->mails_notification_from->value);		
		}
?><html>
<head>
<title>Désinscription</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<link href="lib/css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
</head>
<body>
	<div class="col-xs-12 col-md-6 col-md-offset-3">
	<h1>Désinscription</h1>
	<p><b><?=implode(", ",$emails)?></b></p>
	<?if (count($emails)==1){?><p>Cette adresse a bien été désinscrite !</p><?}?>
	<?if (count($emails)>1){?><p>Ces adresses ont bien été désinscrites !</p><?}?>
	</div>
</body>
</html><?} else {
?><html>
<head>
<title>Désinscription</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<link href="lib/css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
</head>
<body>
	<div class="col-xs-12 col-md-6 col-md-offset-3">
	<h1>Désinscription</h1>
	<p><b><?=implode(", ",$emails)?></b></p>
	<p>Cette adresse n'est pas inscrite.</p>
	</div>
</body>
</html><?}
} else {?><html>
<head>
<title>404</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
</head>
<body>
	La page demandée n'existe pas.
</body>
</html><?}

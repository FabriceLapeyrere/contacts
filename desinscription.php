<?php
require __DIR__ . '/vendor/autoload.php';
foreach (glob("server/*.php") as $filename)
{
    include $filename;
}
include 'conf/main.php';
$C=Config::get();

if (isset($_REQUEST['hash'])) {
	$params=json_decode(base64_decode($_REQUEST['hash']),true);
	$emails=$params['emails'];
	$nb=0;
	if (isset($_REQUEST['ok'])) {
		foreach($emails as $email){
			$r=Contacts::do_remove_mail($email,1);
			WS_maj($r['maj']);
		}
		if ($r['res']>0) {
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
</html><?
		} else {
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
</html><?
		}
	} else {
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
	<p>Pour valider votre désinscription, merci de cliquer sur le bouton "Valider"</p>
	<p>Ceci aura pour effet de désinscrire <b><?=implode(", ",$emails)?></b> .</p>
	<form method="post" action="">
		<input type="hidden" name="hash" value="<?=$_REQUEST['hash']?>"/>
		<input type="submit" name="ok" value="Valider"/>
	</form>
	</div>
</body>
</html><?}
} else {
?><html>
<head>
<title>404</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
</head>
<body>
	La page demandée n'existe pas.
</body>
</html><?}

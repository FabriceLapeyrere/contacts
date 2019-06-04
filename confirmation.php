<?php
require __DIR__ . '/vendor/autoload.php';
foreach (glob("server/*.php") as $filename)
{
    include $filename;
}
include 'conf/main.php';
$C=Config::get();
function ass_casquette($id_casquette,$id_contact,$id_categorie)
{
	$p=(object) null;
	$p->cas=(object) null;
	$p->cas->id=$id_casquette;
	$p->cas->id_contact=$id_contact;
	$p->tag=(object) null;
	$p->tag->id=$id_categorie;
	$tab=Contacts::do_add_cas_tag($p,1);
    WS_maj($tab['maj']);
	return array('ajout'=>'ok');
}
function aj_contact($params)
{
    $maj=array();
	$c=(object) null;
	$c->contact=(object) null;
	$c->contact->nom=$params['nom'];
	$c->contact->prenom=$params['prenom'];
	$c->contact->type=1;
    $tab=Contacts::do_add_contact($c,1);
    $maj=array_merge($maj,$tab['maj']);
	$contact=Contacts::get_contact($tab['res'],false,1);
	$p=(object) null;
	foreach($contact['casquettes'] as $cas){
		$p->cas= (object) $cas;
		$p->cas->donnees=$params['donnees'];
	}
	$tab=Contacts::do_mod_casquette($p,1);
	$maj=array_merge($maj,$tab['maj']);
	$p->tag=(object) null;
	foreach($params['categories'] as $id_tag){
		$p->tag->id=$id_tag;
		$tab=Contacts::do_add_cas_tag($p,1);
        $maj=array_merge($maj,$tab['maj']);
	}
    $maj=array_values(array_unique($maj));
    WS_maj($maj);
}
if (isset($_GET['cle'])) {
	if (file_exists("data/cle/".$_GET['cle'])) {
		$fichier=file("data/cle/".$_GET['cle']);
		$params['nom']=trim($fichier[0]);
		$params['prenom']=trim($fichier[1]);
		$params['donnees']=array();
		$params['donnees'][]=(object) array(
			'label'=>'E-mail',
			'type'=>'email',
			'value'=>trim($fichier[2])
		);
		$id_categorie=trim($fichier[3]);
		$params['categories']=array($id_categorie);
		$path='data/newsletter/'.$id_categorie;
		if (file_exists($path)) {
			$logo="$path/logo.png";
			$data=json_decode(file_get_contents("$path/data.json"));
			$brand=$data->brand;
			$msg=$data->msg;
			$from=$data->from;
			$bgcolor=$data->bgcolor;
			$color=$data->color;
			if (isset($fichier[4]) && $fichier[4]=='done')
			{
?>
<html>
<head>
<title>Inscription à la newsletter : <?=$brand?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<link href="lib/css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
</head>
<body style="background-color:<?=$bgcolor?>;color:<?=$color?>">
	<div class="col-xs-12" style="text-align:center;padding:100px 0;">
		<img src="<?=$logo?>"/>
	</div>
	<form id="newsletter" class="col-xs-12 col-md-6 col-md-offset-3 " method="post" action="inscription.php">
		<p>Votre inscription a déjà été prise en compte !</p>
		<p><?=$msg?></p>
        </div>
</body>
</html>
<?
			} else {
				$message="Bonjour!!
Une nouvelle inscription à la newsletter $brand :
Nom : {$params['nom']}
Prenom : {$params['prenom']}
email : {$params['donnees'][0]->value}

ciao";
				foreach(explode(",",$C->app->mails_notification->value) as $dest){
					mail_utf8(trim($dest),"Inscription automatique à la newsletter $brand",$message,'From: '.$C->app->mails_notification_from->value);
				}
				aj_contact($params);
				$fichier[]='done';
				file_put_contents("data/cle/".$_GET['cle'],$fichier);
?>
<html>
<head>
<title>Inscription à la newsletter : <?=$brand?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<link href="lib/css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
</head>
<body style="background-color:<?=$bgcolor?>;color:<?=$color?>">
	<div class="col-xs-12" style="text-align:center;padding:100px 0;">
		<img src="<?=$logo?>"/>
	</div>
	<div class="col-xs-12 col-md-6 col-md-offset-3 ">
		<p>Votre inscription a bien été prise en compte !</p>
		<p><?=$msg?></p>
        </div>
</body>
</html>
<?
			}
		}
	}
}
?>

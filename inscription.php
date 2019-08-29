<?php
require __DIR__ . '/vendor/autoload.php';
foreach (glob("server/*.php") as $filename)
{
    include $filename;
}
include 'conf/main.php';
$C=Config::get();
function test_email($email)
{
	if( preg_match("~^[_\.0-9a-z-]+@([0-9a-z-]+\.)+[a-z]{2,4}$~",$email) )
	{
	// L'adresse email est valide
	return true;
	}
	else
	{
	// L'adresse email n'est pas valide
	return false;
	}
}
function casquette($email)
{
	$db= new DB(true);
	$query="select id from casquettes where emails like '%$email%'";
	$id=0;
	foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
		$id=$row['id'];
	}
	$casquette=array();
	if ($id>0) {
		$casquette[]=Contacts::get_casquette($id,false,1);
	}
	return $casquette;
}

function ass_casquette($id_casquette,$id_contact,$id_categorie)
{
	$p=(object) null;
	$p->cas=(object) null;
	$p->cas->id=$id_casquette;
	$p->cas->id_contact=$id_contact;
	$p->tag=(object) null;
	$p->tag->id=$id_categorie;
	Contacts::add_cas_tag($p,1);
	return array('ajout'=>'ok');
}


if (isset($_REQUEST['cat']) && file_exists('data/newsletter/'.$_REQUEST['cat'])) {
	if (!file_exists('data/cle')) mkdir('../data/cle', 0777, true);
	$id_categorie=$_REQUEST['cat'];
	$path='data/newsletter/'.$id_categorie;
	$logo="$path/logo.png";
	$data=json_decode(file_get_contents("$path/data.json"));
	$brand=$data->brand;
	$msg=$data->msg;
	$from=$data->from;
	$bgcolor=$data->bgcolor;
	$color=$data->color;
	$res="";
	if (isset($_POST) && count($_POST)>0) {
		if ($_POST['email']=="") $res="Veuillez saisir une adresse e-mail";
		else {
			if (test_email($_POST['email'])) {
				$casquette=casquette($_POST['email']);
				if (count($casquette)>0){
					$casquette=$casquette[0];
					if (in_array($id_categorie,$casquette['tags'])){
						$res='Nous avons déjà votre adresse.';
					} else {
						ass_casquette($casquette['id'],$casquette['id_contact'],$id_categorie);
						$res='Nous avons bien enregistré votre adresse.';
					}
				} else {
					$cle=basename(tempnam('cle',''));
					$infos=array();
					$infos[]=$_POST['nom']."\n";
					$infos[]=$_POST['prenom']."\n";
					$infos[]=$_POST['email']."\n";
					$infos[]=$id_categorie."\n";
					file_put_contents("data/cle/$cle",$infos);

					$message="Afin de completer votre inscription à notre newsletter, merci de suivre le lien suivant :

{$C->app->url->value}/confirmation.php?cle=$cle

$msg";

					mail_utf8($_POST['email'],"Newsletter $brand, confirmation",$message,"From: $from");

					$res="Nous vous avons envoyé un e-mail de confirmation. (pensez à vérifier vos spams)";
				}
			}
			else $res="L'adresse e-mail n'est pas valide !";
		}
	}
?><html>
<head>
<title>Inscription à la newsletter : <?=$brand?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<link href="lib/css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
</head>
<body style="background-color:<?=$bgcolor?>;color:<?=$color?>;">
	<div class="col-xs-12" style="text-align:center;padding:100px 0;">
		<img src="<?=$logo?>" aria-label="Audiostories"/>
	</div>
<?if ($res==""){?>
	<form id="newsletter" class="col-xs-12 col-md-6 col-md-offset-3 " method="post" action="inscription.php">
		<h3>Inscription à la newsletter : <?=$brand?></h3>
		<input type="hidden" value="<?=$id_categorie?>" name="cat">
	    <div class="col-xs-12 col-md-4 form-group"><input type="text" value="" name="prenom" placeholder="prénom" class="form-control input-sm"></div>
	    <div class="col-xs-12 col-md-4 form-group"> <input type="text" value="" name="nom" placeholder="nom" class="form-control input-sm"></div>
	    <div class="col-xs-12 col-md-4 form-group"><input type="email" value="" name="email" placeholder="e-mail" class="form-control input-sm"></div>
            <input class="col-xs-12 btn btn-primary btn-xs" type="submit" value="inscrivez-vous à la newsletter" name="envoyer"/>
        </div>
<?}?>
        <div id="reponse" class="col-xs-12 col-md-6 col-md-offset-3"><?=$res?></div>
</body>
</html>
<? } else { ?>
<html>
<head>
<title>404</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
</head>
<body>
	La page demandée n'existe pas.
</body>
</html>
<? }?>

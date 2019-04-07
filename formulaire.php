<?php
require __DIR__ . '/vendor/autoload.php';
foreach (glob("server/*.php") as $filename)
{
    include $filename;
}
include 'conf/main.php';
$C=Config::get();
function mail_utf8($to, $subject = '(No subject)', $message = '', $header = '') {
  $header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
  mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header_ . $header);
}
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
$t=millisecondes();
$form=Forms::get_form($_REQUEST['id'],1);
if ($form['state']=='open' || $form['state']=='scheduled' && $form['from_date']<$t && $form['to_date']>$t) {
    if (!file_exists('data/cle')) mkdir('./data/cle', 0777, true);
	$res="";
	if (isset($_POST) && count($_POST)>0) {
		if ($_POST['email']=="") $res="Veuillez saisir une adresse e-mail";
		else {
			if (test_email($_POST['email'])) {
				$casquette=casquette($_POST['email']);
				if (count($casquette)>0){
					$casquette=$casquette[0];
					if (in_array($id,$casquette['forms'])){
                        $instance=Forms::get_form_instance($form['id'],$casquette['id'],1);
                        $message="Bonjour,

        Voici le lien pour remplir le formulaire : ".$form['nom']." :

        {$C->app->url->value}/form.php?h=".$instance['hash']."
";
                        mail_utf8($_POST['email'],"Votre lien pour le formulaire ".$form['nom'],$message,'From: '.$C->app->mails_notification_from->value);
                        $res="Nous vous avons envoyé un lien par email. (pensez à vérifier vos spams)";
					} else {
                        $params= new stdClass;
                        $params->id_cas=$casquette['id'];
                        $params->id_form=$form['id'];
                        error_log(var_export($params,true),3,"/tmp/fab.log");
					    $tab=Forms::do_add_form_cas($params,1);
                        WS_maj($tab['maj']);
						$instance=Forms::get_form_instance($form['id'],$casquette['id'],1);
                        $message="Bonjour,

        Voici le lien pour remplir le formulaire : ".$form['nom']." :

        {$C->app->url->value}/form.php?h=".$instance['hash']."
";
                        mail_utf8($_POST['email'],"Votre lien pour le formulaire ".$form['nom'],$message,'From: '.$C->app->mails_notification_from->value);
                        $res="Nous vous avons envoyé un lien par email. (pensez à vérifier vos spams)";
					}
				} else {
					$cle=basename(tempnam('cle',''));
					$infos=array();
					$infos[]=$_POST['nom']."\n";
					$infos[]=$_POST['prenom']."\n";
					$infos[]=$_POST['email']."\n";
					$infos[]=$form['id']."\n";
					file_put_contents("data/cle/$cle",$infos);

					$message="Afin de completer votre inscription au formulaire, merci de suivre le lien suivant :

{$C->app->url->value}/confirmation_form.php?cle=$cle ";

					mail_utf8($_POST['email'],"Formulaire ".$form['nom'].", confirmation",$message,"From: {$C->app->mails_notification_from->value}");

					$res="Nous vous avons envoyé un e-mail de confirmation. (pensez à vérifier vos spams)";
				}
			}
			else $res="L'adresse e-mail n'est pas valide !";
		}
	}
?><html>
<head>
<title>Inscription au formulaire: <?=$form['nom']?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<link href="lib/css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
</head>
<body style="background-color:#FFF;color:#000;">
<?if ($res==""){?>
	<form id="formulaire" class="col-xs-12 col-md-6 col-md-offset-3 " method="post" action="formulaire.php">
		<h3>Inscription au formulaire : <?=$form['nom']?></h3>
		<input type="hidden" value="<?=$_REQUEST['id']?>" name="id">
	    <div class="col-xs-12 col-md-4 form-group"><input type="text" value="" name="prenom" placeholder="prénom" class="form-control input-sm"></div>
	    <div class="col-xs-12 col-md-4 form-group"> <input type="text" value="" name="nom" placeholder="nom" class="form-control input-sm"></div>
	    <div class="col-xs-12 col-md-4 form-group"><input type="email" value="" name="email" placeholder="e-mail" class="form-control input-sm"></div>
            <input class="col-xs-12 btn btn-primary btn-xs" type="submit" value="inscrivez-vous au formulaire" name="envoyer"/>
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

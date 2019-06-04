<?php
require __DIR__ . '/vendor/autoload.php';
foreach (glob("server/*.php") as $filename)
{
    include $filename;
}
include 'conf/main.php';
$C=Config::get();
function label($l) {
    $tab=explode('|',$l);
	$res=trim($tab[0]);
	foreach ($tab as $k=>$t) {
        if ($k>0) $res=$res.' <span class="traduction">/ '.trim($t).'</span>';
	}
	return $res;
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
		$casquette[]=Contacts::get_casquette($id,true,1);
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
$btn="inscrivez-vous au formulaire";
if (isset($form['schema']->home_btn) && $form['schema']->home_btn!="") $btn=$form['schema']->home_btn;
$text="";
if (isset($form['schema']->home_body) && $form['schema']->home_body!="") $text=$form['schema']->home_body;
$mail_body="";
if (isset($form['schema']->mail_body) && $form['schema']->mail_body!="") $mail_body=$form['schema']->mail_body;
$mail_body_confirm="";
if (isset($form['schema']->mail_body_confirm) && $form['schema']->mail_body_confirm!="") $mail_body_confirm=$form['schema']->mail_body_confirm;

$msg_email_missing="Veuillez saisir une adresse e-mail";
if (isset($form['schema']->msg_email_missing) && $form['schema']->msg_email_missing!="") $msg_email_missing=label($form['schema']->msg_email_missing);
$msg_email_invalid="L'adresse e-mail n'est pas valide !";
if (isset($form['schema']->msg_email_invalid) && $form['schema']->msg_email_invalid!="") $msg_email_invalid=label($form['schema']->msg_email_invalid);
$msg_link_sent="Nous vous avons envoyé un lien par email. (pensez à vérifier vos spams)";
if (isset($form['schema']->msg_link_sent) && $form['schema']->msg_link_sent!="") $msg_link_sent=label($form['schema']->msg_link_sent);
$msg_confirm_sent="Nous vous avons envoyé un e-mail de confirmation. (pensez à vérifier vos spams)";
if (isset($form['schema']->msg_confirm_sent) && $form['schema']->msg_confirm_sent!="") $msg_confirm_sent=label($form['schema']->msg_confirm_sent);

$email="";
if (isset($_REQUEST['email'])) $email=$_REQUEST['email'];

if ($form['state']=='open' || $form['state']=='scheduled' && $form['from_date']<$t && $form['to_date']>$t) {
    if (!file_exists('data/cle')) mkdir('./data/cle', 0777, true);
	$res="";
	if (isset($_POST) && count($_POST)>0) {
		if ($_POST['email']=="") $res=$msg_email_missing;
		else {
			if (test_email($_POST['email'])) {
				$casquette=casquette($_POST['email']);
				if (count($casquette)>0){
					$casquette=$casquette[0];
                    $hash="";
                    foreach ($casquette['forms'] as $f) {
                        if ($f['id_form']==$_REQUEST['id']) $hash=$f['hash'];
                    }
					if ($hash!=""){
                        $instance=Forms::get_form_instance($hash,1);
                        $message="Bonjour,

        Voici le lien pour remplir le formulaire : ".$form['nom']." :

        {$C->app->url->value}/form/".$instance['hash']."
";
                        if ($mail_body!='') {
                            $message=str_replace('##URL##',$C->app->url->value."/form/".$instance['hash'],$mail_body);
                        }
                        mail_utf8($_POST['email'],"Votre lien / ".$form['nom'],$message,'From: '.$C->app->mails_notification_from->value);
                        $res=$msg_link_sent;
					} else {
                        $params= new stdClass;
                        $params->id_cas=$casquette['id'];
                        $params->id_form=$form['id'];
                        error_log(var_export($params,true),3,"/tmp/fab.log");
					    $tab=Forms::do_add_form_instance_cas($params,1);
                        WS_maj($tab['maj']);
						$instance=Forms::get_form_instance($tab['res'],1);
                        $message="Bonjour,

        Voici le lien pour remplir le formulaire : ".$form['nom']." :

        {$C->app->url->value}/form/".$instance['hash']."
";
                        if ($mail_body!='') {
                            $message=str_replace('##URL##',$C->app->url->value."/form/".$instance['hash'],$mail_body);
                        }
                        mail_utf8($_POST['email'],"Votre lien / ".$form['nom'],$message,'From: '.$C->app->mails_notification_from->value);
                        $res=$msg_link_sent;
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

					if ($mail_body_confirm!='') {
                        $message=str_replace('##URL##',"{$C->app->url->value}/confirmation_form.php?cle=$cle",$mail_body_confirm);
                    }
                    mail_utf8($_POST['email'],$form['nom'].", confirmation",$message,"From: {$C->app->mails_notification_from->value}");

					$res=$msg_confirm_sent;
				}
			}
			else $res=$msg_email_invalid;
		}
	}
?><html>
<head>
<title><?=$form['nom']?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<link href="lib/css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
<?php
if(file_exists('./data/formulaires/'.$_REQUEST['id'].'/styles.css')) echo "<link href=\"data/formulaires/".$_REQUEST['id']."/styles.css\" media=\"all\" type=\"text/css\" rel=\"stylesheet\">";
?>
</head>
<body>
	<form id="formulaire" class="col-xs-12 col-md-6 col-md-offset-3" method="post" action="formulaire.php?id=<?=$_REQUEST['id']?>">
		<?php
        if(file_exists('./data/formulaires/'.$_REQUEST['id'].'/header.html')) echo file_get_contents('./data/formulaires/'.$_REQUEST['id'].'/header.html')."\n";
        ?>
        <div class="text-container"><?=$text?></div>
        <div class="row">
    		<input type="hidden" value="<?=$_REQUEST['id']?>" name="id">
    	    <div class="col-xs-12 col-md-4 form-group"><input type="text" value="" name="prenom" placeholder="prénom" class="form-control input-sm"></div>
    	    <div class="col-xs-12 col-md-4 form-group"> <input type="text" value="" name="nom" placeholder="nom" class="form-control input-sm"></div>
    	    <div class="col-xs-12 col-md-4 form-group"><input type="email" name="email" placeholder="e-mail" class="form-control input-sm" value="<?=$email?>"></div>
        </div>
        <input class="col-xs-12 btn btn-primary" type="submit" value="<?=$btn?>" name="envoyer"/>
        </div>
    </form>

<?if ($res!=""){?>
        <div id="reponse" class="col-xs-12 col-md-6 col-md-offset-3"><?=$res?></div>
<?}?>
    <div class="col-xs-12 col-md-6 col-md-offset-3">
		<?php
        if(file_exists('./data/formulaires/'.$_REQUEST['id'].'/footer.html')) echo file_get_contents('./data/formulaires/'.$_REQUEST['id'].'/footer.html')."\n";
        ?>
    </div>
</body>
</html>
<? } elseif ($form['state']=='scheduled' && $form['from_date']>$t) { ?>
<html>
<head>
<title>404</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
</head>
<body>
	Ce formulaire sera disponible à partir du <?=date('d/m/Y',$form['from_date']/1000)?>.
</body>
</html>
<? } elseif ($form['state']=='closed' || $form['state']=='scheduled' && $form['to_date']<$t) { ?>
<html>
<head>
<title>404</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
</head>
<body>
	Ce formulaire est fermé.
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

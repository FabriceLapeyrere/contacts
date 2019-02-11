<?php
require __DIR__ . '/vendor/autoload.php';
foreach (glob("server/*.php") as $filename)
{
	include $filename;
}
include 'conf/main.php';
$S=array();
$S['user']=array(
	'login'=>'nobody',
	'name'=>'nobody',
	'id'=>1
);
$C=Config::get();
$news=Mailing::get_news($_REQUEST['id'],1);
if ($news['publie']==1) {
	$sujet=$news['sujet'];
	$html='';
	foreach($news['blocs'] as $n=>$b){
		$html.=$b->html."\n";
		$html=str_replace("##UNSUBSCRIBEURL##","",$html);
	};
	$pjs=array();
	foreach($news['pjs'] as $p){
		if (!$p['used']) $pjs[]=$p;
	}	
	echo "<head>\n<title>".$sujet."</title>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>\n<style>".$C->news->css->value."</style>\n</head>\n<body style=\"margin:0;padding:0;\">\n";
	$header="";
	if ($news['id_newsletter']!="" && $news['id_newsletter']!=-1) {
		$header=$C->news->newsletters->value[$news['id_newsletter']]->html->value."\n";
	}
	echo "$header";
	echo "$html\n";
	if (count($pjs)>0){
		echo "<h3>Pièces jointes</h3>";
		echo "<ul>";
		foreach($pjs as $pj){
			echo "<li><a href=\"{$pj['path']}\">{$pj['filename']}</a></li>";
		}
		echo "</ul>";
	}
	echo '</body>';
} else {
	echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><title>La page n\'existe pas</title></head><body><p>La page n\'existe pas</p></body>';
}


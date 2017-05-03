<?php
foreach (glob("server/*.php") as $filename)
{
	include $filename;
}
include 'fake_ws/conf.php';
include 'conf/main.php';
include 'conf/auth.php';
$news=Mailing::get_news($_REQUEST['id'],1);
if ($news['publie']==1) {
	$sujet=$news['sujet'];
	$html='';
	foreach($news['blocs'] as $b){
		$html.=$b->html."\n";
	};
	$pjs=array();
	foreach($news['pjs'] as $p){
		if (!$p['used']) $pjs[]=$p;
	}	
	echo '<head><title>'.$sujet.'</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><style>'.$C->news->css->value.'</style></head><body>'.$html;
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


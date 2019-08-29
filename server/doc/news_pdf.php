<?php
/**
 *
 * @license	GPL 3 (http://www.gnu.org/licenses/gpl.html)
 * @author	 Fabrice Lapeyrere <fabrice@surlefil.org>
 */
set_time_limit(0);
$C=Config::get();
$news=Mailing::get_news($_POST['id_news'],1);
$html="<html>
<head>
<meta content=\"text/html; charset=UTF-8\" http-equiv=\"content-type\"/>
</head>
<body>";
foreach($news['blocs'] as $b){
	$html.=$b->html."\n";
};
$html.='</body></html>';

$html=str_replace("src=\"data/files/","src=\"".$C->app->url->value."/data/files/",$html);
$html=str_replace("src=\"data/files/","src=\"".$C->app->url->value."/data/files/",$html);
$filename=filter2($news['sujet']);

$data = array('html' => $html, 'filename' => $filename);

$handle = curl_init('http://www.surlefil.org/services/html2pdf.php');
curl_setopt($handle, CURLOPT_POST, true);
curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
header("Content-type:application/pdf");
header('Content-Disposition: attachment; filename="'.$filename.'.pdf"');
print(curl_exec ($handle));

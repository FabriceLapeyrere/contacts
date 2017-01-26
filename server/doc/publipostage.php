<?php
set_time_limit(0);
foreach (glob("server/*.php") as $filename)
{
    include $filename;
}
include 'fake_ws/conf.php';
include 'conf/main.php';
include 'conf/auth.php';
	require('server/lib/tfpdf/tfpdf.php');
	define('FPDF_FONTPATH','server/lib/tfpdf/font/');
	class PDF extends TFPDF
	{
		var $widths;
		var $aligns;
		function NbLines($w, $h, $txt, $border=0, $align='J', $fill=false)
		{
			// Output text with automatic or explicit line breaks
			$cw = &$this->CurrentFont['cw'];
			if($w==0)
				$w = $this->w-$this->rMargin-$this->x;
			$wmax = ($w-2*$this->cMargin);
			$s = str_replace("\r",'',$txt);
			if ($this->unifontSubset) {
				$nb=mb_strlen($s, 'utf-8');
				while($nb>0 && mb_substr($s,$nb-1,1,'utf-8')=="\n")	$nb--;
			}
			else {
				$nb = strlen($s);
				if($nb>0 && $s[$nb-1]=="\n")
					$nb--;
			}
			$b = 0;
			if($border)
			{
				if($border==1)
				{
					$border = 'LTRB';
					$b = 'LRT';
					$b2 = 'LR';
				}
				else
				{
					$b2 = '';
					if(strpos($border,'L')!==false)
						$b2 .= 'L';
					if(strpos($border,'R')!==false)
						$b2 .= 'R';
					$b = (strpos($border,'T')!==false) ? $b2.'T' : $b2;
				}
			}
			$sep = -1;
			$i = 0;
			$j = 0;
			$l = 0;
			$ns = 0;
			$nl = 1;
			while($i<$nb)
			{
				// Get next character
				if ($this->unifontSubset) {
					$c = mb_substr($s,$i,1,'UTF-8');
				}
				else {
					$c=$s[$i];
				}
				if($c=="\n")
				{
					// Explicit line break
					if($this->ws>0)
					{
						$this->ws = 0;
						$this->_out('0 Tw');
					}
					$i++;
					$sep = -1;
					$j = $i;
					$l = 0;
					$ns = 0;
					$nl++;
					if($border && $nl==2)
						$b = $b2;
					continue;
				}
				if($c==' ')
				{
					$sep = $i;
					$ls = $l;
					$ns++;
				}

				if ($this->unifontSubset) { $l += $this->GetStringWidth($c); }
				else { $l += $cw[$c]*$this->FontSize/1000; }

				if($l>$wmax)
				{
					// Automatic line break
					if($sep==-1)
					{
						if($i==$j)
							$i++;
						if($this->ws>0)
						{
							$this->ws = 0;
							$this->_out('0 Tw');
						}
					}
					else
					{
						if($align=='J')
						{
							$this->ws = ($ns>1) ? ($wmax-$ls)/($ns-1) : 0;
							$this->_out(sprintf('%.3F Tw',$this->ws*$this->k));
						}
						$i = $sep+1;
					}
					$sep = -1;
					$j = $i;
					$l = 0;
					$ns = 0;
					$nl++;
					if($border && $nl==2)
						$b = $b2;
				}
				else
					$i++;
			}
			// Last chunk
			if($this->ws>0)
			{
				$this->ws = 0;
				$this->_out('0 Tw');
			}
			if($border && strpos($border,'B')!==false)
				$b .= 'B';
			$this->x = $this->lMargin;
			return $nl;
		}
	}
	$params=json_decode(json_encode($_POST));
    $ipcase=0;
	$support=Publipostage::get_support($params->id_support);
	$query=$params->res->query;
    $nb_lignes=$support['nb_lignes'];
	$nb_colonnes=$support['nb_colonnes'];
	$offset=$support['offset'];
	$mc_gauche=$support['mc_gauche'];
	$mc_droite=$support['mc_droite'];
	$mc_haut=$support['mc_haut'];
	$mc_bas=$support['mc_bas'];
	$mp_gauche=$support['mp_gauche'];
	$mp_droite=$support['mp_droite'];
	$mp_haut=$support['mp_haut'];
	$mp_bas=$support['mp_bas'];
	$h_page=$support['h_page'];
	$l_page=$support['l_page'];
	$police=12;
	if ($support['police']>0)
		$police=$support['police'];
	function rectangle($pdf,$x,$y,$l,$h,$casquette,$mc_gauche,$mc_droite,$mc_haut,$mc_bas,$police) {
		$tab=array();
		$adresse='';
		if ($casquette['id_etab']>0)
			$adresse=$casquette['nom_etab']."\n".adresse($casquette['donnees_etab']);
		if ($adresse=='')
			$adresse=adresse($casquette['donnees']);
		$civilite = civilite($casquette['donnees']);
		$nom=trim($casquette['prenom']." ".$casquette['nom']);
		$adresse=trim($civilite." ".$nom."\n".$adresse);
		if($adresse!="") {
			$htexte=10000;
			$hcase=$h-$mc_haut-$mc_bas;
			$taille_police=$police;
			while ($htexte>$hcase) {
				$pdf->SetFont('Arial','',$taille_police);
				$h_ligne=$taille_police*25.4/72;
				$nbl=$pdf->NbLines($l - $mc_gauche - $mc_droite, $h_ligne ,$adresse, 0, 'L');
				$htexte=$nbl*$taille_police*25.4/72;
				if($htexte>$hcase) $taille_police-=1;
			}
			$nbl=$pdf->NbLines($l - $mc_gauche - $mc_droite, $h_ligne ,$adresse, 0, 'L');
			$h_ligne=$taille_police*25.4/72;
			$pdf->SetXY($x + $mc_gauche, $y + $h - $mc_bas - $nbl*$h_ligne);
	   		$pdf->MultiCell($l - $mc_gauche - $mc_droite, $h_ligne , $adresse, 0, 'L');
		}
	}
	
	if ($l_page>$h_page) {
		$pdf = new PDF('L', 'mm', array($h_page,$l_page));
	}
	else {
		$pdf = new PDF('P', 'mm', array($l_page,$h_page));
	}
	$j=0;
	$pdf->AddFont('Arial', '', 'DejaVuSans.ttf',true);
	$casquettes=Contacts::get_casquettes(array('query'=>$query,'page'=>1,'nb'=>10,'all'=>true),0,$S['user']['id']);
	$modele=array_values($casquettes['collection']);
	for($i=0;$i<$offset;$i++) array_unshift($modele, '');
	$nb_enr=count($modele);
	if($nb_enr>0){
		while ($j<$nb_enr) {
			$pdf->AddPage();
			$pdf->SetAutoPageBreak(false);
			for ($i=0;$i<$nb_lignes;$i++) {
				for ($k=0;$k<$nb_colonnes;$k++) {
					if (($k+$i*$nb_colonnes)>=$ipcase && $j<$nb_enr) {
					rectangle($pdf, $mp_gauche + $k*($l_page - $mp_gauche - $mp_droite)/$nb_colonnes, $mp_haut + $i * ($h_page - $mp_haut - $mp_bas)/$nb_lignes, ($l_page - $mp_gauche - $mp_droite)/$nb_colonnes, ($h_page - $mp_haut - $mp_bas)/$nb_lignes, $modele[$j],$mc_gauche,$mc_droite,$mc_haut,$mc_bas,$police);
					}
					$j++;
				}
			}
			$ipcase=0;
		}
	}
	$nom="publipostage_".date('Ymd');
	$pdf->Output($nom.'.pdf','D');
?>

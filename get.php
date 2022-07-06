<?php

$ipdb="DB_ADDR";
$user="DB_USER";
$mdp="DB_MDP";
$db="DB_NAME";

mysql_connect($ipdb, $user, $mdp);
@mysql_select_db($db);

if(function_exists($_GET['method']))
{
	$_GET['method']();
}

function getMAJ()
{
	$maj = array("alert('Breton t\'es ren\'ss !');");
	$maj = json_encode($maj);
	echo $_GET['jsoncallback'].'('.$maj.')';
}

function getManips()
{	
	$query = "SELECT * FROM manips";
	$resultat = mysql_query($query);

	while($info = mysql_fetch_array($resultat))
	{
		$manips[] = $info;
	}
	
	$manips = json_encode($manips);
	echo $_GET['jsoncallback'].'('.$manips.')';
}

function getPGs()
{
	$query = "SELECT * FROM pgs";
	$resultat = mysql_query($query);

	while($info = mysql_fetch_array($resultat))
	{
		$pgs[] = $info;
	}
	
	$pgs = json_encode($pgs);
	echo $_GET['jsoncallback'].'('.$pgs.')';
}

function getPGsAndManips()
{
	$query = "SELECT * FROM manips";
	$resultat = mysql_query($query);

	while($info = mysql_fetch_array($resultat))
	{
		$manips[] = $info;
	}
	
	$query = "SELECT * FROM pgs";
	$resultat = mysql_query($query);

	while($info = mysql_fetch_array($resultat))
	{
		array_walk(
			$info,
			function (&$entry) {
				$entry = iconv('Windows-1250', 'UTF-8', $entry);
			}
		);
		$pgs[] = $info;
	}
	
	$maj[] = "";
	$pgsManips = json_encode(array('Manips' => $manips,'PGs' => $pgs,'MAJ' => $maj));
	echo $_GET['jsoncallback'].'('.$pgsManips.')';
}

function postTemp()
{
	$temp = explode(";", $_GET['temp']);
	$tempArray = [];
	$pgs = [];
	foreach($temp as $t)
	{
		$t1=explode(",", $t);
		$tempArray[]=$t1;
	}
	
	foreach($tempArray as $t)
	{
		$manipId = $t[0];
		$barcode = $t[2];
		$query = "SELECT id FROM pgs WHERE barcode='".$barcode."'";
		$resultat = mysql_query($query);
		$pgId = mysql_fetch_array($resultat);
		$dateId = $t[3];
		$dateId = explode('/',$dateId);
		$date = date_create($dateId[2].'-'.$dateId[1].'-'.$dateId[0]);
		if($pgId['id'] != null && array_search([$pgId['id'],$manipId],$pgs) === false)
		{
			mysql_query("INSERT INTO presences VALUES('',".$manipId.",".$pgId['id'].",'".date_format($date, 'Y-m-d H:i:s')."','".date("Y-m-d H:i:s")."')");
			$pgs[] = [$pgId['id'],$manipId]; //Verif double bucquage
		}

	}
	
	echo $_GET['jsoncallback'].'('.['ok'].')';
}

?>

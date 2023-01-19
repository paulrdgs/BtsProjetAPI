

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<title>Hauteur et débit des cours d'eau</title>
<meta name="author" content="Thierry Vilmus" />
<meta name="description" content="Evolution récente du niveau et du débit de la rivière dans ma commune" />
<meta name="keywords" content="quantité, eau, cours d'eau, rivière, fleuve, lac, eau superficielle, niveau, hauteur, débit, Hub'Eau, HubEau, Vigicrues, SCHAPI, évolution, commune, France" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="fr" />
<script language="JavaScript" type="text/javascript">
/**
 * Fonction de récupération des paramètres GET de la page
 * @return Array Tableau associatif contenant les paramètres GET
 */
function extractUrlParams(){	
	var t = location.search.substring(1).split('&');
	var f = [];
	for (var i=0; i<t.length; i++){
		var x = t[ i ].split('=');
		f[x[0]]=x[1];
	}
	return f;
}	
	function changeDpt() {
		self.location = 'hydro_tr.php?code_dpt=' + document.getElementById('code_dpt').value;
	}
	function changeStation() {
		self.location = 'hydro_tr.php?code_sta=' + document.getElementById('code_sta').value;
	}
</script>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="https://code.highcharts.com/stock/highstock.js"></script>
<script src="https://code.highcharts.com/stock/modules/exporting.js"></script>
<script src="https://code.highcharts.com/stock/modules/export-data.js"></script>



<style>
	body
	{
		background-color: black;
		margin: 0;
		padding: 0;
	}
	#none
	{
		margin-left: 8%;
	}
	
	a:link { text-decoration: none; color: #0000FF; background: transparent; }
	a:visited {  text-decoration: none; color: #0000FF; background: transparent; }
	a:hover { text-decoration: underline; color: #0000FF; background: #ffa; }
	a:active { color: #FF0000; background: transparent; }
	
	h1 {
		line-height:70px; 
		margin:auto; 
		text-align: center;
		color: white;
		font-style: italic;
	}

	#un, #deux, #trois, #quatre, #cinq, #six
	{
		display: none;
	}

	#none, #first, #second, #third, #fourth, #fifth, #sixth
	{
		width: 12%; 
		height: 30px;
	}

	#base
	{
		display: 'block';
	}

</style>
</head>

<body>

<div id="base">

	<h1>1max2pêche</h1>

	<?php 
	error_reporting(E_ALL & ~E_NOTICE &~E_WARNING); // à commenter en dev

	$depts = file('depts2017.csv'); // la 1ere ligne a l'indice 0. garder une ligne de titre et commencer à l'index 1 pour les recherches pour s'affranchir du BOM
	$nbdepts = count($depts);
	for ($i=1; $i < $nbdepts; $i++) { 
		$code_depts[$i] = explode (';',$depts[$i]);
		$dpt[$code_depts[$i][0]] = substr($code_depts[$i][1],0,-2);
	}	//les codes département sur 2 ou 3 caractères sont dans $code_depts[$i][0], le nom dans [1]

	if (isset($_GET["code_dpt"])) { 
		$code_dpt = htmlspecialchars($_GET["code_dpt"]); 
		if (!array_key_exists($code_dpt, $dpt)) { 
			$code_dpt = '01';
		}	
	} else { $code_dpt = '01'; }

	if (isset($_GET["code_sta"])) { 
		$code_sta = htmlspecialchars($_GET["code_sta"]); 
	}

	$couleur = array(1=>'#0000FF','#FF0000','#008000','#A9A9A9','#FFFF00','#00BFFF','#FF6347','#00FF00','#FFA500','#800080','#C71585','#FFC0CB','#00BFFF','#40E0D0','#8B4513','#CD853F','#C72C48','#9E0E40','#91283B','#6E0B14','#850606','#4E1609');

	?>
	<table style="width: 100%;">
		<tr>
			<td style="color: white; width: 50%;"><h3 style="margin-left: 70%; font-style: italic;">Département :</h3></td>
			<td style="color: white; width: 50%;"><h3 style="margin-left: 15%; font-style: italic;">Station de mesure :</h3></td>
		</tr>
		<tr>
			<td>
				<form name="form1">
					<select style="width: 50%; margin-left: 48%;" name="code_dpt" id="code_dpt" onchange="changeDpt();">
						<?php  
							// Choix d'abord du Département
							foreach ($dpt as $key => $val) {
								echo '<option style="text-align: center;"';
								if($code_dpt == $key){echo 'selected="selected"';}  
								echo ' value="'.$key.'">'.$key.' - '.$val.'</option>';
							}
						?>
					</select>

				</form>
			</td>

			<form action="hydro_tr.php" method="GET">
				<?php  
					$urlsta = "https://hubeau.eaufrance.fr/api/v1/hydrometrie/referentiel/stations?code_departement=$code_dpt&en_service=true&format=json&size=7000";
					$raw = file_get_contents($urlsta);
					$json = json_decode($raw,true);
					$nbsta = $json['count'];
					for ($i=0; $i < $nbsta; $i++) {
						$sta[$i] = $json['data'][$i];
					}	

					$ista = 0;
					foreach($sta as $ligne) {
						$ista++;
						$stacode[$ista] = $ligne['code_station'];
						$statexte[$ista] = $ligne['code_station'].' - '.$ligne['libelle_station'];
						$libelle_station[$ligne['code_station']] = $ligne['libelle_station'];
						$libelle_commune[$ligne['code_station']] = $ligne['libelle_commune'];
						$latitude_station[$ligne['code_station']] = $ligne['latitude_station'];
						$longitude_station[$ligne['code_station']] = $ligne['longitude_station'];
					}	

					echo '	<td><select style="width: 50%; margin-left: 2%;" name="code_sta">';
					foreach ($statexte as $key => $val) {
						echo '<option style="text-align: center;"';
						
						echo ' value="'.$stacode[$key].'">'.$val.'</option>';
					}
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan=2>
				<button style="width: 25%; margin-left: 37.5%; margin-top: 2%;" TYPE=SUBMIT>valider</button>
			</td>
		</tr>
		</form>
	</table>
</div>

<?php
	if(isset($_GET["change"]))
	{
		$_GET["code_sta"] = "";
		?>
			<script>
				document.getElementById("menu").style.display = "none";
				document.getElementById("zero").style.display = "none";
			</script>
		<?php
	}
?>
<?php 
$size = 1000;  // *** taille des pages de réponse *** 
if (isset($code_sta)) {
	$lib_sta = $libelle_station[$code_sta];
	$lib_comm = $libelle_commune[$code_sta];
	$lat = $latitude_station[$code_sta];
	$lon = $longitude_station[$code_sta];
	effacer();
	
	
	OneCall($lat, $lon);
	
	//graphique(1, 'H', "Hauteur");
	//graphique(2, 'Q', "Débit");
	
} // du isset

function effacer()
	{
		if ($_GET["code_sta"] != "")
		{
			?>
				<script>
					document.getElementById("base").style.display = "none";
				</script>
			<?php
		}
	}

function OneCall($lat, $lon) {

	$url = "https://api.openweathermap.org/data/2.5/onecall?lat=$lat&lon=$lon&units=metric&exclude=current,minutely,hourly,alerts&appid=a866a2353a9d3a2375bec487528d1524&lang=fr";
	$raw = file_get_contents($url);
	$json = json_decode($raw,true);
	$page = 0;
	for ($i=0; $i < $size; $i++) {
		if ($json['data'][$i]) { $jsondata[$page*$size+$i] = $json['data'][$i]; } 
		else { break; }
	}
	$tz =  $json['timezone_offset'];
	$val2 = array(
		'none',
		'first',
		'second',
		'third',
		'fourth',
		'fifth',
		'sixth',
	);
	?>
		<div id="menu" style="margin-top: 3%; width: 100%; background: radial-gradient(circle at 14%, rgba(91,43,194,1) 0%, rgba(0,0,0,1) 50%, rgba(0,0,0,1) 100%);">
			<?php	
			//radial-gradient(at 60%,red,yellow,green);
				for ($i=0; $i<7; $i++)
				{
					setlocale(LC_TIME, 'fr_FR');
					date_default_timezone_set('Europe/Paris');
					$demain = time() + 86400*$i;
					?>
						<button  style="border: none; background-color: transparent; color: white" id="<?php echo $val2[$i]; ?>" value="<?php echo $i; ?>" onmouseover="test(this.value)"><?php echo utf8_encode(ucfirst(strftime('%A %d', $demain))).' '.utf8_encode(ucfirst(strftime('%B', $demain))); ?></button>
						<script>
							function test(num)
							{						
								switch(num)
								{
									case '0': 	document.getElementById('zero').style.display = 'block';
												document.getElementById('un').style.display = 'none';
												document.getElementById('deux').style.display = 'none';
												document.getElementById('trois').style.display = 'none';
												document.getElementById('quatre').style.display = 'none';
												document.getElementById('cinq').style.display = 'none';
												document.getElementById('six').style.display = 'none';
												document.getElementById('menu').style.backgroundImage = 'radial-gradient(circle at 14%, rgba(91,43,194,1) 0%, rgba(0,0,0,1) 50%, rgba(0,0,0,1) 100%)';
												break;
									case '1': 	document.getElementById('zero').style.display = 'none';
												document.getElementById('un').style.display = 'block';
												document.getElementById('deux').style.display = 'none';
												document.getElementById('trois').style.display = 'none';
												document.getElementById('quatre').style.display = 'none';
												document.getElementById('cinq').style.display = 'none';
												document.getElementById('six').style.display = 'none';
												document.getElementById('menu').style.backgroundImage = 'radial-gradient(circle at 26%, rgba(91,43,194,1) 0%, rgba(0,0,0,1) 50%, rgba(0,0,0,1) 100%)';
												break;											
									case '2': 	document.getElementById('zero').style.display = 'none';
												document.getElementById('un').style.display = 'none';
												document.getElementById('deux').style.display = 'block';
												document.getElementById('trois').style.display = 'none';
												document.getElementById('quatre').style.display = 'none';
												document.getElementById('cinq').style.display = 'none';
												document.getElementById('six').style.display = 'none';	
												document.getElementById('menu').style.backgroundImage = 'radial-gradient(circle at 38%, rgba(91,43,194,1) 0%, rgba(0,0,0,1) 50%, rgba(0,0,0,1) 100%)';
												break;
									case '3': 	document.getElementById('zero').style.display = 'none';
												document.getElementById('un').style.display = 'none';
												document.getElementById('deux').style.display = 'none';
												document.getElementById('trois').style.display = 'block';
												document.getElementById('quatre').style.display = 'none';
												document.getElementById('cinq').style.display = 'none';
												document.getElementById('six').style.display = 'none';
												document.getElementById('menu').style.backgroundImage = 'radial-gradient(circle at 50%, rgba(91,43,194,1) 0%, rgba(0,0,0,1) 50%, rgba(0,0,0,1) 100%)';
												break;
									case '4': 	document.getElementById('zero').style.display = 'none';
												document.getElementById('un').style.display = 'none';
												document.getElementById('deux').style.display = 'none';
												document.getElementById('trois').style.display = 'none';
												document.getElementById('quatre').style.display = 'block';
												document.getElementById('cinq').style.display = 'none';
												document.getElementById('six').style.display = 'none';
												document.getElementById('menu').style.backgroundImage = 'radial-gradient(circle at 62%, rgba(91,43,194,1) 0%, rgba(0,0,0,1) 50%, rgba(0,0,0,1) 100%)';
												break;
									case '5': 	document.getElementById('zero').style.display = 'none';
												document.getElementById('un').style.display = 'none';
												document.getElementById('deux').style.display = 'none';
												document.getElementById('trois').style.display = 'none';
												document.getElementById('quatre').style.display = 'none';
												document.getElementById('cinq').style.display = 'block';
												document.getElementById('six').style.display = 'none';
												document.getElementById('menu').style.backgroundImage = 'radial-gradient(circle at 74%, rgba(91,43,194,1) 0%, rgba(0,0,0,1) 50%, rgba(0,0,0,1) 100%)';
												break;
									case '6': 	document.getElementById('zero').style.display = 'none';
												document.getElementById('un').style.display = 'none';
												document.getElementById('deux').style.display = 'none';
												document.getElementById('trois').style.display = 'none';
												document.getElementById('quatre').style.display = 'none';
												document.getElementById('cinq').style.display = 'none';
												document.getElementById('six').style.display = 'block';
												document.getElementById('menu').style.backgroundImage = 'radial-gradient(circle at 86%, rgba(91,43,194,1) 0%, rgba(0,0,0,1) 50%, rgba(0,0,0,1) 75%, rgba(0,0,0,1) 100%)';
												break;
								}
							}
						</script>
					<?php
				}
			?>
		</div>
	<?php	
	$val = array(
		'zero',
		'un',
		'deux',
		'trois',
		'quatre',
		'cinq',
		'six',
	);
	for ($i=0; $i<7; $i++)
	{
		?>
			<div id="<?php echo $val[$i]; ?>" style="margin-top: 5%; border: 1px solid white; background-color: #5B2BC2; width: 80%; margin-left:10%; border-radius: 50px;">
				<?php
				setlocale(LC_TIME, 'fr_FR');
				date_default_timezone_set('Europe/Paris');
				$demain = time() + 86400*$i;
				echo '<h2 style="font-style: italic; text-align: center; color: white;">'.utf8_encode(ucfirst(strftime('%A %d', $demain))).' '.utf8_encode(ucfirst(strftime('%B %Y', $demain))).'</h2>';
				?>
			
				<table style="width: 100%; border-collapse: collapse;">
					<tr style="">
						<td style="width: 20%; font-style: italic; text-align: center; color: white;"><?php echo '<h3>Ephémeride</h3>';  ?></td>
						<td style="width: 20%; font-style: italic; text-align: center; color: white;"><?php echo '<h3>Météo</h3>';  ?></td>
						<td style="width: 20%; font-style: italic; text-align: center; color: white;"><?php echo '<h3>Pluie</h3>';  ?></td>
						<td style="width: 20%; font-style: italic; text-align: center; color: white;"><?php echo '<h3>Vent</h3>';  ?></td>
						<td style="width: 20%; font-style: italic; text-align: center; color: white;"><?php echo '<h3>Lune</h3>';  ?></td>
					</tr>
					<tr style="">
						<td style="text-align: center; color: white; width: 20%;">
							<?php
								echo 'Lever de soleil: '.date('H:i',$json['daily'][$i]['sunrise']);?></br></br><?php
								echo 'Coucher de soleil: '.date('H:i',$json['daily'][$i]['sunset']);?></br></br><?php
								echo 'Pêche possible de '.date("H:i", strtotime("-30 minutes", $json['daily'][$i]['sunrise'])).' à '.date("H:i", strtotime("+30 minutes", $json['daily'][0]['sunset']));?></br></br><?php
							?>
						</td>
						<td style="text-align: center; color: white; width: 20%;">
							<?php
								echo 'Température: '.$json['daily'][$i]['temp']['day'].'°C';?></br></br><?php
								echo 'Ressenti: '.$json['daily'][$i]['feels_like']['day'].'°C';?></br></br><?php
								echo 'Température mini: '.$json['daily'][$i]['temp']['min'].'°C';?></br></br><?php
								echo 'Température max: '.$json['daily'][$i]['temp']['max'].'°C';?></br></br><?php
								echo 'Pression atmosphérique: '.$json['daily'][$i]['pressure'].'hPa';?></br></br><?php
								echo 'Couverture nuageuse: '.$json['daily'][$i]['clouds'].'%';?></br></br><?php
								echo 'Indice UV: '.$json['daily'][$i]['uvi'].'%';?></br><?php
							?>
						</td>
						<td style="text-align: center; color: white; width: 20%;">
							<?php
								echo 'Probabilité de pluie: '.$json['daily'][$i]['pop'].'%';?></br></br><?php
								if ($json['daily'][$i]['pop'] < 25)
								{
									echo 'Pluie: NON';?></br></br><?php
								}
								else
								{
									echo 'Pluie: '.$json['daily'][$i]['rain'];?></br></br><?php
								}
								echo 'Humidité: '.$json['daily'][$i]['humidity'].'%';?></br></br><?php
								echo 'Point de rosé: '.$json['daily'][$i]['dew_point'].'°C';?></br></br><?php
							?>
						</td>
						<td style="text-align: center; color: white; width: 20%;">
							<?php
								echo 'direction du vent: '.wind_cardinals($json['daily'][$i]['wind_deg']);?></br></br><?php
								echo 'Vitesse du vent: '.round($json['daily'][$i]['wind_speed']*3.6).'km/h';?></br></br><?php
								echo 'Rafales: '.round($json['daily'][$i]['wind_gust']*3.6).'km/h';?></br></br><?php
							?>
						</td>
						<td style="text-align: center; color: white; width: 20%;">
							<?php
								Solunar($lat, $lon, $tz);
							?>
						</td>

					</tr>
					<tr style="">
						<td colspan='5' style="color: white;">
							<div style="width: 50%; height: 15vh; position: float; float: left;">
								<?php
									echo '<img style="margin-left: 83%;" src="http://openweathermap.org/img/wn/'.$json['daily'][$i]['weather'][0]['icon'].'@2x.png">';
								?>
							</div>
							<div style="width: 50%; height: 15vh; position: float; float: left;">
								<?php
									echo '<p style="margin-top: 7vh">'.ucfirst($json['daily'][$i]['weather'][0]['description']).'</p>';
								?>
							</div>
						</td>
					</tr>
				</table>
			</div>
		<?php
	}
	?>
		<form action="hydro_tr.php" method="GET">
			<button style="width: 25%; margin-left: 37.5%; margin-top: 2%;" type=submit name="change">changer de station</button>
		</form>
	<?php
}

function wind_cardinals($deg) {
	$cardinal = 'N';
	$cardinalDirections = array(
		'N' => array(348.75, 360),
		'NNE' => array(11.25, 33.75),
		'NE' => array(33.75, 56.25),
		'ENE' => array(56.25, 78.75),
		'E' => array(78.75, 101.25),
		'ESE' => array(101.25, 123.75),
		'SE' => array(123.75, 146.25),
		'SSE' => array(146.25, 168.75),
		'S' => array(168.75, 191.25),
		'SSO' => array(191.25, 213.75),
		'SO' => array(213.75, 236.25),
		'OSO' => array(236.25, 258.75),
		'O' => array(258.75, 281.25),
		'ONO' => array(281.25, 303.75),
		'NO' => array(303.75, 326.25),
		'NNO' => array(326.25, 348.75)
	);
	foreach ($cardinalDirections as $dir => $angles) {
			if ($deg >= $angles[0] && $deg < $angles[1]) {
				$cardinal = $dir;
			}
		}
		return $cardinal;
}

function Solunar($lat, $lon, $tz)
{

	setlocale(LC_TIME, 'fr_FR');
	date_default_timezone_set('Europe/Paris');
	$dt = date("Ymd");
	
	$urllunar="https://api.solunar.org/solunar/$lat,$lon,$dt,$tz";

	// On get les resultat
    $raw = file_get_contents($urllunar);
    // Décode la chaine JSON
    $jslunar = json_decode($raw, true);
	//var_dump($jslunar);

	$test = strstr($jslunar['moonRise'], ':', true)-$tz+1;
	$test2 = strstr($jslunar['moonRise'], ':');
	echo 'Lever de lune: '.$test.$test2.'</br></br>';

	$test = strstr($jslunar['moonSet'], ':', true)-$tz+1;
	$test2 = strstr($jslunar['moonSet'], ':');
	echo 'Coucher de lune: '.$test.$test2.'</br></br>';

	$luneanglais = array(
		'New Moon',
		'Waxing Crescent',
		'First Quarter',
		'Waxing Gibbous',
		'Full Moon',
		'Waning Gibbous',
		'Third Quarter',
		'Waning Crescent',
	);
	$lunefrançais = array(
		'Nouvelle Lune',
		'Premier Croissant',
		'Premier Quartier',
		'Lune Gibbeuse Croissante',
		'Pleine Lune',
		'Lune Gibbeuse Décroissante',
		'Dernier Quartier',
		'Dernier Croissant',
	);

	for ($u=0; $u<9; $u++)
	{
		if($jslunar['moonPhase'] == $luneanglais[$u])
		{
			$lunaire = $lunefrançais[$u];
		}
	}

	echo round($jslunar['moonIllumination']*100).'% - '.$lunaire;

	
}

function graphique($il, $grandeur_hydro, $titre_graph) {
	global $code_sta, $size, $lib_sta, $lib_comm, $couleur;

	$url = "https://hubeau.eaufrance.fr/api/v1/hydrometrie/observations_tr?code_entite=$code_sta&grandeur_hydro=$grandeur_hydro&size=$size&sort=asc";
	$raw = file_get_contents($url);
	$json = json_decode($raw,true);
	$page = 0;
	for ($i=0; $i < $size; $i++) {
		if ($json['data'][$i]) { $jsondata[$page*$size+$i] = $json['data'][$i]; } else { break; }
	}	

	while ($json['next']) { 
		$page++;
		$url = $json['next'];
		$raw = file_get_contents($url);
		$json = json_decode($raw,true);
		for ($i=0; $i < $size; $i++) {
			if ($json['data'][$i]) { $jsondata[$page*$size+$i] = $json['data'][$i]; } else { break; }
		}	
	}	

	$idate = -1; // les tableaux de données doivent commencer à 0
	if (count($jsondata) > 0) {
		foreach($jsondata as $ligne) {
			$resultat_obs = $ligne['resultat_obs'];
			if (isset($resultat_obs)) {
				$idate++;
				$date_obs[$idate] = strtotime($ligne['date_obs']);
				$result[$idate] = $resultat_obs / 1000; // passage de mm en m ou en m3/s
			}	
		}
	}
	
	if ($idate > 0) {
			
			

echo "<script>
document.addEventListener('DOMContentLoaded', function () {
    var myChart = Highcharts.stockChart('cont$il', {";
?>

        rangeSelector: {
            buttons: [{
    type: 'day',
    count: 1,
    text: '1j'
}, {
    type: 'day',
    count: 3,
    text: '3j'
}, {
    type: 'day',
    count: 7,
    text: '7j'
}, {
    type: 'day',
    count: 14,
    text: '14j'
}, {
    type: 'all',
    text: 'Tout'
}]
        },
<?php
        echo "
		title: {
            text: '$sType'
        },

        series: [{
            name: '$titre_graph',
			colorIndex: ".($il-1).",
            data: [";

	for ($i=0; $i <= $idate; $i++) {
		echo '['.($date_obs[$i]*1000).','.$result[$i].']';  // x1000 pour highstock
		if ($i < $idate) { echo ','; } // pas de virgule pour la dernière mesure
	}	
?>	
	],
            tooltip: {
                valueDecimals: 2
            }
        }]
    });
});				
</script>

<?php				
	} else {
		echo "<p>Aucune mesure de $titre_graph n'a été trouvée pour la station $code_sta - $lib_sta";
	}
}
?>


</body>
</html>

<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Nils K. Windisch <windisch@sub.uni-goettingen.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('nkwlib')."class.tx_nkwlib.php");

/**
 * Plugin 'Multi Address Entry Map' for the 'nkwgmaps' extension.
 *
 * @author	Nils K. Windisch <windisch@sub.uni-goettingen.de>
 * @package	TYPO3
 * @subpackage	tx_nkwgmaps
 */
class tx_nkwgmaps_pi4 extends tx_nkwlib {
	var $prefixId      = 'tx_nkwgmaps_pi4';		// Same as class name
	var $scriptRelPath = 'pi4/class.tx_nkwgmaps_pi4.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'nkwgmaps';	// The extension key.
	var $pi_checkCHash = true;

	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexform();
		$lang = $this->getLanguage();

		$scale = array("3.3" => 13, "6.6" => 14, "13.2" => 15);		

		// FLEXFORM VALUES
		// ui options
		$conf["ff"]["navicontrol"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'navicontrol', 'uioptions');
		$conf["ff"]["maptypeid"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maptypeid', 'uioptions');
		$conf["ff"]["maptypecontrol"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maptypecontrol', 'uioptions');
		$conf["ff"]["sensor"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'sensor', 'uioptions');
		$conf["ff"]["mapcenterbutton"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'mapcenterbutton', 'uioptions');
		$conf["ff"]["zoom"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'zoom', 'uioptions');
		$conf["ff"]["scale"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'scale', 'uioptions');

		$conf["ff"]["addressbooksource"]["uid"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'source', 'addressdata');
		$conf["ff"]["popupoptions"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'popupoptions', 'addressdata');

		// get data from DB
		$cntMarker = 0;
		$addressbooksource_uids = explode(",",$conf["ff"]["addressbooksource"]["uid"]);
		foreach($addressbooksource_uids as $uid)	{
			$res0 = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tt_address","uid = '".$uid."'","","","");
			while($row0 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res0))
			{
				# $debug .= $row0["address"];
				$conf[$cntMarker]["address"] = $row0["address"].", ".$row0["zip"]." ".$row0["city"].", ".$row0["country"];
				$conf[$cntMarker]["popupcontent"] = $row0["last_name"];
				if ($row0["first_name"]) $conf[$cntMarker]["popupcontent"] = $row0["first_name"]." ".$row0["last_name"];
			}

			// get latlng
#			$this->dprint($conf[$cntMarker]["address"]);
			$geo = $this->geocodeAddress($conf[$cntMarker]["address"]);
			if ($geo["status"] == "OK")	{
				$conf[$cntMarker]["latlng"] = $geo["results"][0]["geometry"]["location"]["lat"].",".$geo["results"][0]["geometry"]["location"]["lng"];
				$lat[$cntMarker] = $geo["results"][0]["geometry"]["location"]["lat"];
				$lng[$cntMarker] = $geo["results"][0]["geometry"]["location"]["lng"];
#				$this->dprint("$i ".$lat[$cntMarker]." ".$lng[$cntMarker]."<br />");
			}	else	{
				$msg = "fail. could not resolve address";
				$fail = TRUE;
			}
			$cntMarker++;
		}
		
		/* calculate center-position of the map */ 
		$borders = array("l" => 180, "r" => -180, "b" => 90, "t" => -90);
		for($i=0; $i<$cntMarker; $i++)	{
#			$this->dprint("$i ".$lat[$i]." ".$lng[$i]."<br />");
			if($lat[$i] < $borders['l'])	$borders['l'] = $lat[$i];
			if($lat[$i] > $borders['r'])	$borders['r'] = $lat[$i];
			if($lng[$i] < $borders['b'])	$borders['b'] = $lng[$i];
			if($lng[$i] > $borders['t'])	$borders['t'] = $lng[$i];
		}
		if($cntMarker > 1)	{
#			$this->dprint($borders['l']." ".$borders['r']."<br />");
#			$this->dprint($borders['b']." ".$borders['t']."<br />");
			$dist = (6378.388) * acos(sin(deg2rad($borders['l'])) * sin(deg2rad($borders['r'])) + cos(deg2rad($borders['l'])) * cos(deg2rad($borders['r'])) * cos(deg2rad($borders['b']) - deg2rad($borders['b'])));
#			$this->dprint($dist);
			$latMean = round(($borders['l']+$borders['r'])/2,5);
			$lngMean = round(($borders['b']+$borders['t'])/2,5);
		}	else {
			$latMean = $lat[0];
			$lngMean = $lng[0];
		}
		$latlngCenter = $latMean.",".$lngMean;
		foreach($scale as $key => $value)	{
			if($key < $dist)	{
				$conf["ff"]["zoom"] = $value;
				break;
			}
		}		

#		$this->dprint($latlngCenter);

#		$this->dprint($conf["ff"]);

		if (!$fail)
		{

			// the div in which the map is displayed
			$tmp = "<div id='map_canvas' style='width:100%; height:500px'></div>";

##### JS START #####
		// JS to cnstruct the map
$js = "
<script type=\"text/javascript\" src=\"http://maps.google.com/maps/api/js?sensor=".$conf["ff"]["sensor"]."\"></script>
<script type=\"text/javascript\">
";
if ($conf["ff"]["mapcenterbutton"] == "true")
{
	$js .= "
	function HomeControl(controlDiv, map, latlng) {
		controlDiv.style.padding = '5px';
		var controlUI = document.createElement('DIV');
		controlUI.style.backgroundColor = 'white';
		controlUI.style.borderStyle = 'solid';
		controlUI.style.padding = '1px';
		controlUI.style.borderWidth = '1px';
		controlUI.style.cursor = 'pointer';
		controlUI.style.textAlign = 'center';
		controlUI.title = 'Click to set the map to Home';
		controlDiv.appendChild(controlUI);
		var controlText = document.createElement('DIV');
		controlText.style.fontFamily = 'Arial,sans-serif';
		controlText.style.fontSize = '12px';
		controlText.style.paddingLeft = '4px';
		controlText.style.paddingRight = '4px';
		controlText.innerHTML = '<b>Home</b>';
		controlUI.appendChild(controlText);
		google.maps.event.addDomListener(controlUI, 'click', function() {
			map.setCenter(latlng);
			map.setZoom(".$conf["ff"]["zoom"].");
		});
	}
	";
}
$js .= "
	function initialize() {
			var latlng = new google.maps.LatLng(".$latlngCenter.");";
			
for($i=0; $i<$cntMarker; $i++)	{
	$js .= "var latlng".$i." = new google.maps.LatLng(".$conf[$i]["latlng"].");";
	$jsAppend .= "
			var marker".$i." = new google.maps.Marker({
				position: latlng".$i.", 
				map: map, 
				title:'".$conf[$i]["popupcontent"]." - ".$conf[$i]["address"]."'
			});";
}

$js .= "var mapDiv = document.getElementById('map_canvas');
		var myOptions = {
			zoom: ".$conf["ff"]["zoom"].",
			center: latlng,
			scaleControl: ".$conf["ff"]["scale"].",
			mapTypeControl: true,
			mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.".$conf["ff"]["maptypecontrol"]."},
			navigationControl: true,
			navigationControlOptions: {style: google.maps.NavigationControlStyle.".$conf["ff"]["navicontrol"]."},
			mapTypeId: google.maps.MapTypeId.".$conf["ff"]["maptypeid"]."
		};
		var map = new google.maps.Map(mapDiv, myOptions);";
$js .= $jsAppend;

if ($conf["ff"]["mapcenterbutton"] == "true")
{
	$js .= "
		var homeControlDiv = document.createElement('DIV');
		var homeControl = new HomeControl(homeControlDiv, map, latlng);
		homeControlDiv.index = 1;
		map.controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv);
	";
}

# $js .= "var bounds = new GLatLngBounds;";  // Browserbug: GLatLngBounds is not defined
# set marker
for($i=0; $i<$cntMarker; $i++)	{
	if ($conf[$i]["popupcontent"])	{
		$js .= "
			var contentString = '".$conf[$i]["popupcontent"]."';
			var infowindow".$i." = new google.maps.InfoWindow({
				content: contentString
			});
		";

#		if ($conf["ff"]["popupoptions"] == "instant")
#			$js .= "infowindow".$i.".open(map,marker".$i.");";
		$js .= "
			google.maps.event.addListener(marker".$i.", 'click', function() {
				infowindow".$i.".open(map,marker".$i.");
			});
		";
#	    $js .= "bounds.extend(markers".$i.".point); alert(markers".$i.".point);";
	}
}
# $js .= "map.setZoom(map.getBoundsZoomLevel(bounds));";
$js .= "
	}
	initialize();
</script>
		";

##### JS END #####
		}
		else $tmp = "<p>".$msg."</p>";

		// return stuff
		$content = $tmp;
		if (!$fail) $content .= $js; 
		$content .= $debug;
	
		return $this->pi_wrapInBaseClass($content);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwgmaps/pi4/class.tx_nkwgmaps_pi4.php'])
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwgmaps/pi4/class.tx_nkwgmaps_pi4.php']);

?>
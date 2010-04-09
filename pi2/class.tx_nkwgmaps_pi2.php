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
 * Plugin 'Simple Map' for the 'nkwgmaps' extension.
 *
 * @author	Nils K. Windisch <windisch@sub.uni-goettingen.de>
 * @package	TYPO3
 * @subpackage	tx_nkwgmaps
 */
class tx_nkwgmaps_pi2 extends tx_nkwlib {
	var $prefixId      = 'tx_nkwgmaps_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_nkwgmaps_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'nkwgmaps';	// The extension key.
	var $pi_checkCHash = true;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexform();
		$lang = $this->getLanguage();

		// FLEXFORM VALUES
		// ui options
		$conf["ff"]["navicontrol"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'navicontrol', 'uioptions'); // get flexform values
		$conf["ff"]["maptypeid"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maptypeid', 'uioptions'); // get flexform values
		$conf["ff"]["maptypecontrol"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maptypecontrol', 'uioptions'); // get flexform values
		$conf["ff"]["sensor"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'sensor', 'uioptions'); // get flexform values
		$conf["ff"]["mapcenterbutton"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'mapcenterbutton', 'uioptions'); // get flexform values
		$conf["ff"]["zoom"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'zoom', 'uioptions'); // get flexform values
		$conf["ff"]["scale"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'scale', 'uioptions'); // get flexform values

		$conf["ff"]["addressbooksource"]["uid"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'source', 'addressdata'); // get flexform values
		$conf["ff"]["popupoptions"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'popupoptions', 'addressdata'); // get flexform values

		// get data from DB
		$res0 = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tt_address","uid = '".$conf["ff"]["addressbooksource"]["uid"]."'","","","");
		while($row0 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res0))
		{
			$conf["ff"]["address"] = $row0["address"].", ".$row0["zip"]." ".$row0["city"].", ".$row0["country"];
			$conf["ff"]["popupcontent"] = $row0["last_name"];
			if ($row0["first_name"]) $conf["ff"]["popupcontent"] = $row0["first_name"]." ".$row0["last_name"];
		}

		// get latlon
		$geo = $this->geocodeAddress($conf["ff"]["address"]);
		if ($geo["status"] == "OK")
			$conf["ff"]["latlon"] = $geo["results"][0]["geometry"]["location"]["lat"].",".$geo["results"][0]["geometry"]["location"]["lng"];
		else
		{
			$msg = "fail. could not resolve address";
			$fail = TRUE;
		}

#$this->dprint($conf["ff"]);

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
		controlUI.style.borderWidth = '2px';
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
		var latlng = new google.maps.LatLng(".$conf["ff"]["latlon"].");
		var mapDiv = document.getElementById('map_canvas');
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
		var map = new google.maps.Map(mapDiv, myOptions);
		var marker = new google.maps.Marker({
			position: latlng, 
			map: map, 
			title:'".$conf["ff"]["popupcontent"]." - ".$conf["ff"]["address"]."'
		});
";
if ($conf["ff"]["mapcenterbutton"] == "true")
{
	$js .= "
		var homeControlDiv = document.createElement('DIV');
		var homeControl = new HomeControl(homeControlDiv, map, latlng);
		homeControlDiv.index = 1;
		map.controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv);
	";
}
if ($conf["ff"]["popupcontent"])
{
	$js .= "
		var contentString = '".$conf["ff"]["popupcontent"]."';
		var infowindow = new google.maps.InfoWindow({
			content: contentString
		});
	";
	if ($conf["ff"]["popupoptions"] == "instant")
		$js .= "infowindow.open(map,marker);";
	$js .= "
		google.maps.event.addListener(marker, 'click', function() {
			infowindow.open(map,marker);
		});
	";
}
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
	
		return $this->pi_wrapInBaseClass($content);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwgmaps/pi2/class.tx_nkwgmaps_pi2.php'])
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwgmaps/pi2/class.tx_nkwgmaps_pi2.php']);

?>
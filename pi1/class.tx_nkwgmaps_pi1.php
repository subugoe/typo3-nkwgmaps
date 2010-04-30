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
class tx_nkwgmaps_pi1 extends tx_nkwlib {
	var $prefixId      = 'tx_nkwgmaps_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_nkwgmaps_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'nkwgmaps';	// The extension key.
	var $pi_checkCHash = true;

	function gmapsJS($conf)
	{
		$js = "<script type=\"text/javascript\" src=\"http://maps.google.com/maps/api/js?sensor=".$conf["ff"]["sensor"]."\"></script>";
		$js .= "<script type=\"text/javascript\">";

			// Home Button Function - don't touch
			$js .= "function HomeControl(controlDiv, map, latlng) {
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
				google.maps.event.addDomListener(controlUI,'click',function(){map.setCenter(latlng);map.setZoom(".$conf["ff"]["zoom"].");});";
			$js .= "}\n";

			$js .= "function initialize() {\n";

				// make map START //
				$js .= "var latlng = new google.maps.LatLng(".$conf["ff"]["latlon"].");\n";
				$js .= "var mapDiv = document.getElementById('".$conf["ff"]["mapName"]."');\n";
				$js .= "var myOptions = {
						zoom: ".$conf["ff"]["zoom"].",
						center: latlng,
						scaleControl: ".$conf["ff"]["scale"].",
						mapTypeControl: true,
						mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.".$conf["ff"]["maptypecontrol"]."},
						navigationControl: true,
						navigationControlOptions: {style: google.maps.NavigationControlStyle.".$conf["ff"]["navicontrol"]."},
						mapTypeId: google.maps.MapTypeId.".$conf["ff"]["maptypeid"]."
					};\n";
				$js .= "var map_".$conf["ff"]["mapName"]." = new google.maps.Map(mapDiv, myOptions);\n";
				// make map END //

				// home button stuff START //
				$js .= "var homeControlDiv = document.createElement('DIV');\n";
				$js .= "var homeControl = new HomeControl(homeControlDiv,map_".$conf["ff"]["mapName"].",latlng);\n";
				$js .= "homeControlDiv.index = 1;\n";
				$js .= "map_".$conf["ff"]["mapName"].".controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv);\n";
				// home button stuff END //

				// marker and popup START //
				$js .= "var marker = new google.maps.Marker({position: latlng, map: map_".$conf["ff"]["mapName"].", title:'".$conf["ff"]["address"]."'});\n";
				if ($conf["ff"]["popupcontent"])
				{
					$js .= "var contentString = '".$conf["ff"]["popupcontent"]."';\n";
					$js .= "var infowindow = new google.maps.InfoWindow({content:contentString});\n";
					if ($conf["ff"]["popupoptions"] == "instant") $js .= "infowindow.open(map_".$conf["ff"]["mapName"].",marker);\n";
					$js .= "google.maps.event.addListener(marker,'click',function(){infowindow.open(map_".$conf["ff"]["mapName"].",marker);});\n";
				}
				// marker and popup END //

			$js .= "}\n";

			$js .= "initialize();\n"; // go go go

		$js .= "</script>";
		return $js;
	}

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

		$conf["ff"] = array(
			"navicontrol" => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'navicontrol', 'uioptions'),
			"maptypeid" => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maptypeid', 'uioptions'),
			"maptypecontrol" => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maptypecontrol', 'uioptions'),
			"sensor" => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'sensor', 'uioptions'),
			"zoom" => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'zoom', 'uioptions'),
			"scale" => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'scale', 'uioptions'),
			"address" => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'address', 'addressdata'),
			"popupcontent" => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'popupcontent', 'addressdata'),
			"popupoptions" => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'popupoptions', 'addressdata')
		);
		$conf["ff"]["mapName"] = md5($conf["ff"]["address"]);

		// get latlon
		$geo = $this->geocodeAddress($conf["ff"]["address"]);
		if ($geo["status"] == "OK")
			$conf["ff"]["latlon"] = $geo["results"][0]["geometry"]["location"]["lat"].",".$geo["results"][0]["geometry"]["location"]["lng"];
		else
		{
			$msg = "fail. could not resolve address";
			$fail = TRUE;
		}

		if (!$fail)
		{
			// the div in which the map is displayed
			$tmp = "<div id='".$conf["ff"]["mapName"]."' style='width:100%; height:500px'></div>";
			$js = $this->gmapsJS($conf);
		}
		else $tmp = "<p>".$msg."</p>";

		$content = $tmp;
		if (!$fail) $content .= $js; 
	
		return $this->pi_wrapInBaseClass($content);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwgmaps/pi1/class.tx_nkwgmaps_pi1.php'])
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwgmaps/pi1/class.tx_nkwgmaps_pi1.php']);
?>
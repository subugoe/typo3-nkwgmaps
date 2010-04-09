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


/**
 * Plugin 'Simple Map' for the 'nkwgmaps' extension.
 *
 * @author	Nils K. Windisch <windisch@sub.uni-goettingen.de>
 * @package	TYPO3
 * @subpackage	tx_nkwgmaps
 */
class tx_nkwgmaps_pi3 extends tslib_pibase {
	var $prefixId      = 'tx_nkwgmaps_pi3';		// Same as class name
	var $scriptRelPath = 'pi3/class.tx_nkwgmaps_pi3.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'nkwgmaps';	// The extension key.
	var $pi_checkCHash = true;

	function geocodeAddress($str)
	{
		$str = ereg_replace(" ", "+", $str);
		$getThis = "http://maps.google.com/maps/api/geocode/json?address=".$str."&sensor=false";
		$json = file_get_contents($getThis);
		$tmp = json_decode($json, true);
		if ($tmp["status"] = "OK")
		{
			$return = $tmp["results"][0]["geometry"]["location"]["lat"].",".$tmp["results"][0]["geometry"]["location"]["lng"];
		}
		return $return;
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

		$conf["ff"]["navicontrol"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'navicontrol', 'uioptions'); // get flexform values
		$conf["ff"]["maptypecontrol"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maptypecontrol', 'uioptions'); // get flexform values
		$conf["ff"]["sensor"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'sensor', 'uioptions'); // get flexform values
		$conf["ff"]["zoom"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'zoom', 'uioptions'); // get flexform values
		if (!$conf["ff"]["sensor"]) $conf["ff"]["sensor"] = "false";
		$conf["ff"]["display"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'display', 'addresses'); // get flexform values
		$conf["ff"]["singleaddress"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'singleaddress', 'singleaddressoptions'); // get flexform values
		$conf["ff"]["singleaddresspopup"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'singleaddresspopup', 'singleaddressoptions'); // get flexform values
		$conf["ff"]["singleaddresspopupdisplay"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'singleaddresspopupdisplay', 'singleaddressoptions'); // get flexform values
		$conf["ff"]["addressbooksource"]["uid"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'source', 'addressbookoptions'); // get flexform values
		$conf["ff"]["addressgroupsource"]["uid"] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'source', 'addressgroupoptions'); // get flexform values

		#-34.397, 150.644
		
		// single
		if ($conf["ff"]["display"] == "single")
		{
			// get latlon
			if ($conf["ff"]["singleaddress"])
				$latlon = $this->geocodeAddress($conf["ff"]["singleaddress"]);
			else // fail
			{
				$msg = "no address given";
				$fail = TRUE;
			}
		}
		// addressbook single
		else if ($conf["ff"]["display"] == "addressbook")
		{
			if ($conf["ff"]["addressbooksource"])
			{
				// get data from DB
				$res0 = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tt_address","uid = '".$conf["ff"]["addressbooksource"]["uid"]."'","","","");
				while($row0 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res0))
				{
					$conf["ff"]["addressbooksource"]["search"] = $row0["address"].", ".$row0["city"].", ".$row0["country"];
					$conf["ff"]["singleaddresspopup"] = $row0["name"];
				}
				// get latlon
				if ($conf["ff"]["addressbooksource"]["search"])
					$latlon = $this->geocodeAddress($conf["ff"]["addressbooksource"]["search"]);
			}
			else // fail
			{
				$msg = "no address given";
				$fail = TRUE;
			}
		}
		else if ($conf["ff"]["display"] == "addressgroup")
		{
			if ($conf["ff"]["addressgroupsource"]["uid"])
			{
				// get data from DB
				$conf["ff"]["addressbooksource"]["search"] = array();
				$res0 = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tt_address","addressgroup = '".$conf["ff"]["addressgroupsource"]["uid"]."'","","","");
				while($row0 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res0))
				{

					$conf["ff"]["addressbooksource"]["search"][] = $row0["address"].", ".$row0["city"].", ".$row0["country"];
					#$conf["ff"]["singleaddresspopup"] = $row0["name"];
				}
				echo "<pre>";
				print_r($conf["ff"]["addressbooksource"]["search"]);
				echo "</pre>";
				$size = sizeof($conf["ff"]["addressbooksource"]["search"]);
				if ($size >= 1)
				{
					for($i=0;$i<$size;$i++)
						$latlon[$i] = $this->geocodeAddress($conf["ff"]["addressbooksource"]["search"][$i]);
					echo "<pre>";
					print_r($latlon);
					echo "</pre>";
				}
			}
			else // fail
			{
				$msg = "no address group given";
				$fail = TRUE;
			}
		}
		// directions
		else if ($conf["ff"]["display"] == "directions")
		{
		
		}



		echo "<pre>";
		print_r($conf["ff"]);
		echo "</pre>";



		// the div in which the map is displayed
		$tmp = "<div id='map_canvas' style='width:100%; height:500px'></div>";

##### JS START #####
		// JS to cnstruct the map
		$js = "
<script type=\"text/javascript\" src=\"http://maps.google.com/maps/api/js?sensor=".$conf["ff"]["sensor"]."\"></script>
<script type=\"text/javascript\">
	function initialize() {
		var latlng = new google.maps.LatLng(".$latlon.");
		var myOptions = {
			zoom: ".$conf["ff"]["zoom"].",
			center: latlng,
			scaleControl: true,
			mapTypeControl: true,
			mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.".$conf["ff"]["maptypecontrol"]."},
			navigationControl: true,
			navigationControlOptions: {style: google.maps.NavigationControlStyle.".$conf["ff"]["navicontrol"]."},
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		var map = new google.maps.Map(document.getElementById(\"map_canvas\"), myOptions);
		var myLatLng = new google.maps.LatLng(".$latlon.");
		var marker = new google.maps.Marker({
			position: myLatLng, 
			map: map, 
			title:'Hello World!'
		});
		";
/*
		if ($conf["ff"]["singleaddresspopup"])
		{
			$js .= "
		var contentString = '".$conf["ff"]["singleaddresspopup"]."';
		var infowindow = new google.maps.InfoWindow({
			content: contentString
		});
		";
		if ($conf["ff"]["singleaddresspopupdisplay"] == "instant")
		{
		$js .= "
		infowindow.open(map,marker);
		";
		}
		$js .= "
		google.maps.event.addListener(marker, 'click', function() {
			infowindow.open(map,marker);
		});
		";
		}
*/
		$js .= "
	}
	initialize();
</script>
		";
##### JS END #####

		// return stuff
		$content = $tmp;
		if ($fail != TRUE) $content .= $js; 
	
		return $this->pi_wrapInBaseClass($content);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwgmaps/pi3/class.tx_nkwgmaps_pi3.php'])
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwgmaps/pi3/class.tx_nkwgmaps_pi3.php']);

?>
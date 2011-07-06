<?php

/* * *************************************************************
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
 * ************************************************************* */
require_once(t3lib_extMgm::extPath('nkwlib') . 'class.tx_nkwlib.php');
require_once(PATH_tslib . 'class.tslib_pibase.php');

/**
 * Library for Google Maps Functions
 * 
 * @author Nils K. Windisch <windisch@sub.uni-goettingen.de>
 */
class tx_nkwgmaps extends tslib_pibase {

	/**
	 * load Gmaps-Library only once
	 * 
	 * @param array $conf
	 * @return string $js Javascript
	 */
	public static function loadGmapsLib($conf) {

		if ($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_nkwgmaps.']['loadedLib'] != 1) {
			if ($GLOBALS['TSFE']->sys_language_uid === 1)
				$js = '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor= ' . $conf['ff']['sensor'] . '&language=en"></script>';
			else {
				$js = '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=' . $conf['ff']['sensor'] . '&language=de"></script>';
			}
			$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_nkwgmaps.']['loadedLib'] = 1;
		}
		return $js;
	}

	/**
	 * Gmap Function
	 * 
	 * @param array $conf
	 * @return string 
	 */
	public static function showGMap($conf) {
		$js = self::loadGmapsLib($conf);
		$js .= '<script type="text/javascript">';
				// Home Button Function - don't touch
			$js .= "function HomeControl(controlDiv, map, latlng) {
				controlDiv.style.padding = '5px';
				var controlUI = document.createElement('DIV');
                                controlUI.setAttribute('style', '-moz-border-radius:2px 2px 2px 2px; -moz-box-shadow:2px 2px 3px rgba(0, 0, 0, 0.35); border-top-left-radius: 2px 2px; border-top-right-radius: 2px 2px; border-bottom-right-radius: 2px 2px; border-bottom-left-radius: 2px 2px; -webkit-box-shadow: rgba(0, 0, 0, 0.347656) 2px 2px 3px;', false);
                                controlUI.style.border = '1px solid rgb(169, 187, 223)';
                                controlUI.style.background = '-moz-linear-gradient(center top , rgb(254, 254, 254), rgb(243, 243, 243)) repeat scroll 0% 0% transparent';
                                // controlUI.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(rgb(254, 254, 254)), to(rgb(243, 243, 243)))';
				controlUI.style.backgroundColor = 'rgb(243, 243, 243)';
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
				google.maps.event.addDomListener(controlUI,'click',function(){map.setCenter(latlng);map.setZoom(" . $conf['ff']['zoom'] . ");});";
			$js .= "}\n";

            $js .= "function initialize() {\n";
					// make map START //
			$js .= "var latlng = new google.maps.LatLng(" . $conf['ff']['latlon'] . ");\n";
			$js .= "var mapDiv = document.getElementById('" . $conf['ff']['mapName'] . "');\n";
			$js .= "var myOptions = {
						zoom: " . $conf['ff']['zoom'] . ",
						center: latlng,
						scaleControl: " . $conf['ff']['scale'] . ",
						mapTypeControl: true,
						mapTypeControlOptions: {style: google.maps.MapTypeControlStyle." . $conf['ff']['maptypecontrol'] . "},
						navigationControl: true,
						navigationControlOptions: {style: google.maps.NavigationControlStyle." . $conf['ff']['navicontrol'] . "},
						mapTypeId: google.maps.MapTypeId." . $conf['ff']['maptypeid'] . "
					};\n";
				$js .= "var map_" . $conf['ff']['mapName'] . " = new google.maps.Map(mapDiv, myOptions);\n";
				// make map END //

				// home button stuff START //
				$js .= "var homeControlDiv = document.createElement('DIV');\n";
				$js .= "var homeControl = new HomeControl(homeControlDiv,map_" . $conf['ff']['mapName'] . ",latlng);\n";
				$js .= "homeControlDiv.index = 1;\n";
				$js .= "map_" . $conf['ff']['mapName'] . ".controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv);\n";
				// home button stuff END //

				// marker and popup START //
				$js .= "var marker = new google.maps.Marker({position: latlng, map: map_" . $conf['ff']['mapName'] . ", title:'" . $conf['ff']['address'] . "'});\n";
				if ($conf['ff']['popupcontent']) {
					$js .= "var contentString = '" . $conf['ff']['popupcontent'] . "';\n";
					$js .= "var infowindow = new google.maps.InfoWindow({content:contentString});\n";
					if ($conf['ff']['popupoptions'] == "instant") {
						$js .= "infowindow.open(map_" . $conf['ff']['mapName'] . ",marker);\n";
					}
					$js .= "google.maps.event.addListener(marker,'click',function(){infowindow.open(map_" . $conf['ff']['mapName'] . ",marker);});\n";
				}
				// marker and popup END //
			$js .= "}\n";
			$js .= "initialize();\n"; // go go go
		$js .= "</script>";
		return $js;
	}

	/**
	 * 
	 */
	public static function singleGmapsJS($conf) {
		$js = self::loadGmapsLib($conf);
		$js .= "<script type=\"text/javascript\">";
			// Home Button Function - don't touch
		$js .= "function HomeControl(controlDiv, map, latlng) {
				controlDiv.style.padding = '5px';
				var controlUI = document.createElement('DIV');
                                controlUI.setAttribute('style', '-moz-border-radius:2px 2px 2px 2px; -moz-box-shadow:2px 2px 3px rgba(0, 0, 0, 0.35); border-top-left-radius: 2px 2px; border-top-right-radius: 2px 2px; border-bottom-right-radius: 2px 2px; border-bottom-left-radius: 2px 2px; -webkit-box-shadow: rgba(0, 0, 0, 0.347656) 2px 2px 3px;', false);
                                controlUI.style.border = '1px solid rgb(169, 187, 223)';
                                controlUI.style.background = '-moz-linear-gradient(center top , rgb(254, 254, 254), rgb(243, 243, 243)) repeat scroll 0% 0% transparent';
                                // controlUI.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(rgb(254, 254, 254)), to(rgb(243, 243, 243)))';
				controlUI.style.backgroundColor = 'rgb(243, 243, 243)';
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
				google.maps.event.addDomListener(controlUI,'click',function(){map.setCenter(latlng);map.setZoom(" . $conf['ff']['zoom'] . ");});";
		$js .= "}\n";

		$js .= "function initialize() {\n";
			// make map START //
		$js .= "var latlng = new google.maps.LatLng(" . $conf['ff']['latlon'] . ");\n";
		$js .= "var mapDiv = document.getElementById('" . $conf['ff']['mapName'] . "');\n";
		$js .= "var myOptions = {
						zoom: " . $conf['ff']['zoom'] . ",
						center: latlng,
						scaleControl: " . $conf['ff']['scale'] . ",
						mapTypeControl: true,
						mapTypeControlOptions: {style: google.maps.MapTypeControlStyle." . $conf['ff']['maptypecontrol'] . "},
						navigationControl: true,
						navigationControlOptions: {style: google.maps.NavigationControlStyle." . $conf['ff']['navicontrol'] . "},
						mapTypeId: google.maps.MapTypeId." . $conf['ff']['maptypeid'] . "
					};\n";
		$js .= "var map_" . $conf['ff']['mapName'] . " = new google.maps.Map(mapDiv, myOptions);\n";

			// home button stuff START //
		$js .= "var homeControlDiv = document.createElement('DIV');\n";
		$js .= "var homeControl = new HomeControl(homeControlDiv,map_" . $conf['ff']['mapName'] . ",latlng);\n";
		$js .= "homeControlDiv.index = 1;\n";
		$js .= "map_" . $conf['ff']['mapName'] . ".controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv);\n";
			// home button stuff END //

			// marker and popup START //
		$js .= "var marker = new google.maps.Marker({position: latlng, map: map_" . $conf['ff']['mapName'] . ", title:'" . $conf['ff']['address'] . "'});\n";
		if ($conf['ff']['popupcontent']) {
			$js .= "var contentString = '" . $conf['ff']['popupcontent'] . "';\n";
			$js .= "var infowindow = new google.maps.InfoWindow({content:contentString});\n";
			if ($conf['ff']['popupoptions'] == "instant") {
				$js .= "infowindow.open(map_" . $conf['ff']['mapName'] . ",marker);\n";
			}
			$js .= "google.maps.event.addListener(marker,'click',function(){infowindow.open(map_" . $conf['ff']['mapName'] . ",marker);});\n";
		}
			// marker and popup END //
		$js .= "}\n";
			// go go go
		$js .= "initialize();\n";
		$js .= "</script>";
		return $js;
	}

	/**
	 * Mehrere Adressen auf einer Seite
	 * 
	 * @param array $conf
	 * @return string Javascript fuer Gmaps
	 */
	public static function multiGmapsJS($conf) {
		$js = self::loadGmapsLib($conf);
		$js .= "
			<script type=\"text/javascript\">
			   var bounds;";
		if ($conf['ff']['mapcenterbutton'] == 'true') {
			$js .= "
			function HomeControl(controlDiv, map, latlng) {
				controlDiv.style.padding = '5px';
				var controlUI = document.createElement('DIV');
                                controlUI.setAttribute('style', '-moz-border-radius:2px 2px 2px 2px; -moz-box-shadow:2px 2px 3px rgba(0, 0, 0, 0.35); border-top-left-radius: 2px 2px; border-top-right-radius: 2px 2px; border-bottom-right-radius: 2px 2px; border-bottom-left-radius: 2px 2px; -webkit-box-shadow: rgba(0, 0, 0, 0.347656) 2px 2px 3px;', false);
                                controlUI.style.border = '1px solid rgb(169, 187, 223)';
                                controlUI.style.background = '-moz-linear-gradient(center top , rgb(254, 254, 254), rgb(243, 243, 243)) repeat scroll 0% 0% transparent';
                                // controlUI.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(rgb(254, 254, 254)), to(rgb(243, 243, 243)))';
				controlUI.style.backgroundColor = 'rgb(243, 243, 243)';
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
					map.setCenter(bounds.getCenter());
					map.fitBounds(bounds);
					if(map.getZoom() > " . $conf['ff']['zoom'] . ") map.setZoom(" . $conf['ff']['zoom'] . ");
				});
			}
			";
		}
		$js .= "
			function initialize() {
				var latlng = new google.maps.LatLng(" . $conf['ff']['latlngCenter'] . ");";

			// improved routine: sum up all entries which have the same address in one marker
		for ($i = 0; $i < $conf['ff']['cntMarker']; $i++) {
			if (!$geocodes[$conf[$i]['latlng']]) {
				$geocodes[$conf[$i]['latlng']] = $conf[$i]['popupcontent'] . ' - ' . $conf[$i]['address'];
			} else {
				$geocodes[$conf[$i]['latlng']] = $conf[$i]['popupcontent'] . ', ' . $geocodes[$conf[$i]['latlng']];
			}
		}
		$j = 0;
		foreach ($geocodes as $key => $value) {
			$info = explode(' - ', $value);
			$js .= "
				var latlng" . $j . " = new google.maps.LatLng(" . $key . ");";
			$jsAppend .= "
				var marker" . $j . " = new google.maps.Marker({
					position: latlng" . $j . ", 
					map: map_" . $conf['ff']['mapName'] . ", 
					title:'" . $info[1] . "'
				});\n";
			$j++;
		}
		$conf['ff']['cntMarker'] = count($geocodes);

		$js .= "
				var mapDiv = document.getElementById('" . $conf['ff']['mapName'] . "');
				var myOptions = {
					zoom: " . $conf['ff']['zoom'] . ",
					center: latlng,
					scaleControl: " . $conf['ff']['scale'] . ",
					mapTypeControl: true,
					mapTypeControlOptions: {style: google.maps.MapTypeControlStyle." . $conf['ff']['maptypecontrol'] . "},
					navigationControl: true,
					navigationControlOptions: {style: google.maps.NavigationControlStyle." . $conf['ff']['navicontrol'] . "},
					mapTypeId: google.maps.MapTypeId." . $conf['ff']['maptypeid'] . "
				};
				var map_" . $conf['ff']['mapName'] . " = new google.maps.Map(mapDiv, myOptions);";
		$js .= $jsAppend;
		if ($conf['ff']['mapcenterbutton'] == 'true') {
			$js .= "
				var homeControlDiv = document.createElement('DIV');
				var homeControl = new HomeControl(homeControlDiv, map_" . $conf['ff']['mapName'] . ", latlng);
				homeControlDiv.index = 1;
				map_" . $conf['ff']['mapName'] . ".controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv);
			";
		}
		$js .= "
				bounds = new google.maps.LatLngBounds;";

			// improved routine: summarized markers
		$j = 0;
		foreach ($geocodes as $key => $value) {
			$info = explode(' - ', $value);
			$js .= "
				var contentString = '" . $info[0] . "';
				var infowindow" . $j . " = new google.maps.InfoWindow({
					content: contentString
				});
			";
				// Popups (Bubbles) are shown after initialization, if option is set
			if ($conf['ff']['popupoptions'] == 'instant') {
				$js .= "infowindow" . $j . ".open(map_" . $conf['ff']['mapName'] . ",marker" . $j . ");";
			}
			$js .= "
				google.maps.event.addListener(marker" . $j . ", 'click', function() {
					infowindow" . $j . ".open(map_" . $conf['ff']['mapName'] . ",marker" . $j . ");
				});
			";
			$js .= "bounds.extend(marker" . $j . ".position);";
			$j++;
		}

			// fit displayed map to markers
		$js .= "map_" . $conf['ff']['mapName'] . ".fitBounds(bounds);";
		$js .= "google.maps.event.addListener(map_" . $conf['ff']['mapName'] . ", 'zoom_changed', function() {
					if (map_" . $conf['ff']['mapName'] . ".getZoom() > " . $conf['ff']['zoom'] . " ) {
						map_" . $conf['ff']['mapName'] . ".setZoom(" . $conf['ff']['zoom'] . ");
					}
				});";

		$js .= "
			}
			initialize();
		</script>";
		return $js;
	}

	/**
	 * Routenplaner anzeigen
	 * 
	 * @param array $conf
	 * @return string Javascript fuer den Routenplaner
	 */
	public static function directions($conf) {
		$js = self::loadGmapsLib($conf);
		$js .= "
			<script type=\"text/javascript\">
			var directionDisplay;
			var directionsService = new google.maps.DirectionsService();
			var map_" . $conf['ff']['mapName'] . ";
			var latlng;";

		if ($conf['ff']['mapcenterbutton'] == 'true') {
			$js .= "
			function HomeControl(controlDiv, map, latlng) {
				controlDiv.style.padding = '5px';
				var controlUI = document.createElement('DIV');
                                controlUI.setAttribute('style', '-moz-border-radius:2px 2px 2px 2px; -moz-box-shadow:2px 2px 3px rgba(0, 0, 0, 0.35); border-top-left-radius: 2px 2px; border-top-right-radius: 2px 2px; border-bottom-right-radius: 2px 2px; border-bottom-left-radius: 2px 2px; -webkit-box-shadow: rgba(0, 0, 0, 0.347656) 2px 2px 3px;', false);
                                controlUI.style.border = '1px solid rgb(169, 187, 223)';
                                controlUI.style.background = '-moz-linear-gradient(center top , rgb(254, 254, 254), rgb(243, 243, 243)) repeat scroll 0% 0% transparent';
                                // controlUI.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(rgb(254, 254, 254)), to(rgb(243, 243, 243)))';
				controlUI.style.backgroundColor = 'rgb(243, 243, 243)';
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
				google.maps.event.addDomListener(controlUI,'click',function(){map.setCenter(latlng);map.setZoom(" . $conf['ff']['zoom'] . ");});
/*				google.maps.event.addDomListener(controlUI, 'click', function() {
					map.setCenter(bounds.getCenter());
					map.fitBounds(bounds);
					if(map.getZoom() > " . $conf['ff']['zoom'] . ") map.setZoom(" . $conf['ff']['zoom'] . ");
				});
*/			}
			";
		}
		$js .= "
			function initialize() {
			  directionsDisplay = new google.maps.DirectionsRenderer();
			  // latlng = new google.maps.LatLng(" . $conf['ff']['latlngCenter'] . ");
			  var myOptions = {
				center: latlng,
				scaleControl: " . $conf['ff']['scale'] . ",
				mapTypeControl: true,
				mapTypeControlOptions: {style: google.maps.MapTypeControlStyle." . $conf['ff']['maptypecontrol'] . "},
				navigationControl: true,
				navigationControlOptions: {style: google.maps.NavigationControlStyle." . $conf['ff']['navicontrol'] . "},
				mapTypeId: google.maps.MapTypeId." . $conf['ff']['maptypeid'] . ",
				zoom:" . $conf['ff']['zoom'] . ",
			  }
			  map_" . $conf['ff']['mapName'] . " = new google.maps.Map(document.getElementById('" . $conf['ff']['mapName'] . "'), myOptions);
			  directionsDisplay.setMap(map_" . $conf['ff']['mapName'] . ");";
		if ($conf['ff']['mapcenterbutton'] == "true") {
			$js .= "
				var homeControlDiv = document.createElement('DIV');
				var homeControl = new HomeControl(homeControlDiv, map_" . $conf['ff']['mapName'] . ", latlng);
				homeControlDiv.index = 1;
				map_" . $conf['ff']['mapName'] . ".controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv);";
		}
		$js .= "
			}

			function calcRoute() {
			  var start = '" . $conf['ff']['start'] . "';
			  var end = '" . $conf['ff']['end'] . "';
			  var request = {
				origin:start, 
				destination:end,
				travelMode: google.maps.DirectionsTravelMode." . $conf['ff']['travelmode'] . ",
				unitSystem: google.maps.DirectionsUnitSystem.METRIC
			};
			directionsService.route(request, function(result, status) {
				if (status == google.maps.DirectionsStatus.OK) {
					directionsDisplay.setDirections(result);
				}
			});
			}";
		$js .= "
			initialize();
			calcRoute();
			</script>";
		return $js;
	}

	/**
	 * directions-funktion with location plan
	 * 
	 * @param array $conf
	 * @return string Javascript
	 */
	public static function directionsWithSteps($conf) {
		$js = self::loadGmapsLib($conf);
		$js .= "
			<script type=\"text/javascript\">
			var directionDisplay;
			var directionsService = new google.maps.DirectionsService();
			var map_" . $conf['ff']['mapName'] . ";
			var latlng;
			var markerArray = [];
			var stepDisplay;";
		if ($conf['ff']['mapcenterbutton'] == 'true') {

                    $js .= "
			function HomeControl(controlDiv, map, latlng) {
				controlDiv.style.padding = '5px';
				var controlUI = document.createElement('DIV');
                                controlUI.setAttribute('style', '-moz-border-radius:2px 2px 2px 2px; -moz-box-shadow:2px 2px 3px rgba(0, 0, 0, 0.35); border-top-left-radius: 2px 2px; border-top-right-radius: 2px 2px; border-bottom-right-radius: 2px 2px; border-bottom-left-radius: 2px 2px; -webkit-box-shadow: rgba(0, 0, 0, 0.347656) 2px 2px 3px;', false);
                                controlUI.style.border = '1px solid rgb(169, 187, 223)';
                                controlUI.style.background = '-moz-linear-gradient(center top , rgb(254, 254, 254), rgb(243, 243, 243)) repeat scroll 0% 0% transparent';
                                // controlUI.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(rgb(254, 254, 254)), to(rgb(243, 243, 243)))';
				controlUI.style.backgroundColor = 'rgb(243, 243, 243)';
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
				google.maps.event.addDomListener(controlUI,'click',function(){map.setCenter(latlng);map.setZoom(" . $conf['ff']['zoom'] . ");});
/*				google.maps.event.addDomListener(controlUI, 'click', function() {
					map.setCenter(bounds.getCenter());
					map.fitBounds(bounds);
					if(map.getZoom() > " . $conf['ff']['zoom'] . ") map.setZoom(" . $conf['ff']['zoom'] . ");
				});
*/			}
			";
		}
		$js .= "
			function initialize() {
			  directionsDisplay = new google.maps.DirectionsRenderer();
                          latlng = new google.maps.LatLng(" . $conf['ff']['latlngCenter'] . ");
			  var myOptions = {
				center: latlng,
				scaleControl: " . $conf['ff']['scale'] . ",
				mapTypeControl: true,
				mapTypeControlOptions: {style: google.maps.MapTypeControlStyle." . $conf['ff']['maptypecontrol'] . "},
				navigationControl: true,
				navigationControlOptions: {style: google.maps.NavigationControlStyle." . $conf['ff']['navicontrol'] . "},
				mapTypeId: google.maps.MapTypeId." . $conf['ff']['maptypeid'] . ",
				zoom:" . $conf['ff']['zoom'] . ",
			}
			map_" . $conf['ff']['mapName'] . " = new google.maps.Map(document.getElementById('" . $conf['ff']['mapName'] . "'), myOptions);
			directionsDisplay.setMap(map_" . $conf['ff']['mapName'] . ");
			document.getElementById('directionsPanel').style.display = 'block';
			directionsDisplay.setPanel(document.getElementById('directionsPanel'));
			
				// Instantiate an info window to hold step text.
  			stepDisplay = new google.maps.InfoWindow();";
		if ($conf['ff']['mapcenterbutton'] == 'true') {
			$js .= "
				var homeControlDiv = document.createElement('DIV');
				var homeControl = new HomeControl(homeControlDiv, map_" . $conf['ff']['mapName'] . ", latlng);
				homeControlDiv.index = 1;
				map_" . $conf['ff']['mapName'] . ".controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv);";
		}
		$js .= "
			}

			function calcRoute() {
					// First, clear out any existing markerArray
					// from previous calculations.
				for (i = 0; i < markerArray.length; i++) {
				markerArray[i].setMap(null);
			}

			var start = '" . $conf['ff']['start'] . "';
			var end = '" . $conf['ff']['end'] . "';
			var request = {
				origin:start, 
				destination:end,
				travelMode: google.maps.DirectionsTravelMode." . $conf['ff']['travelmode'] . ",
					unitSystem: google.maps.DirectionsUnitSystem.METRIC,
					provideRouteAlternatives: true
			};
			directionsService.route(request, function(response, status) {
				if (status == google.maps.DirectionsStatus.OK) {
					// directionsDisplay.setDirections(result);
					var warnings = document.getElementById('warnings_panel');
					//warnings.innerHTML = \"\" + response.routes[0].warnings + \"\";
					directionsDisplay.setDirections(response);
					showSteps(response);

				}
			});
			}";
		$js .= "
			function showSteps(directionResult) {
				// For each step, place a marker, and add the text to the marker's
				// info window. Also attach the marker to an array so we
				// can keep track of it and remove it when calculating new
				// routes.
				var myRoute = directionResult.routes[0].legs[0];
			
			for (var i = 0; i < myRoute.steps.length; i++) {
				var marker = new google.maps.Marker({
					position: myRoute.steps[i].start_point, 
					map: map_" . $conf['ff']['mapName'] . "
				});
				attachInstructionText(marker, myRoute.steps[i].instructions);
				markerArray[i] = marker;
			}
			}
			
			function attachInstructionText(marker, text) {
				google.maps.event.addListener(marker, 'click', function() {
					stepDisplay.setContent(text);
					stepDisplay.open(map_" . $conf['ff']['mapName'] . ", marker);
				});
			}
			";

		$js .= "
			initialize();
			calcRoute();
			</script>";
		return $js;
	}

	/**
	 * Test routine 
	 * to fix missing controls problem, with multiple maps on one page
	 * tried to extend variable-names -> fails
	 * not fixed yet (24.06.2010)
	 * 
	 * @param unknown_type $conf
	 * @FIXME
	 * @return string Javascript
	 */
	public static function singleGmapsJStest($conf) {
		$ext = "_" . $conf['ff']['mapName'];
		$js = self::loadGmapsLib($conf);
		$js .= "<script type=\"text/javascript\">";
			// Home Button Function - don't touch
		$js .= "function HomeControl(controlDiv, map, latlng) {
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
				google.maps.event.addDomListener(controlUI,'click',function(){map.setCenter(latlng);map.setZoom(" . $conf['ff']['zoom'] . ");});";
		$js .= "}\n";

		$js .= "function initialize() {\n";
			// make map START
		$js .= "var latlng$ext = new google.maps.LatLng(" . $conf['ff']['latlon'] . ");\n";
		$js .= "var mapDiv$ext = document.getElementById('" . $conf['ff']['mapName'] . "');\n";
		$js .= "var myOptions$ext = {
						zoom: " . $conf['ff']['zoom'] . ",
						center: latlng$ext,
						scaleControl: " . $conf['ff']['scale'] . ",
						mapTypeControl: true,
						mapTypeControlOptions: {style: google.maps.MapTypeControlStyle." . $conf['ff']['maptypecontrol'] . "},
						navigationControl: true,
						navigationControlOptions: {style: google.maps.NavigationControlStyle." . $conf['ff']['navicontrol'] . "},
						mapTypeId: google.maps.MapTypeId." . $conf['ff']['maptypeid'] . "
					};\n";
		$js .= "var map$ext = new google.maps.Map(mapDiv$ext, myOptions$ext);\n";
			// make map END
			// home button stuff START
		$js .= "var homeControlDiv$ext = document.createElement('DIV');\n";
		$js .= "var homeControl$ext = new HomeControl(homeControlDiv$ext,map$ext,latlng$ext);\n";
		$js .= "homeControlDiv$ext.index = 1;\n";
		$js .= "map$ext.controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv$ext);\n";
			// home button stuff END
			// marker and popup START
		$js .= "var marker = new google.maps.Marker({position: latlng$ext, map: map$ext, title:'" . $conf['ff']['address'] . "'});\n";
		if ($conf['ff']['popupcontent']) {
			$js .= "var contentString = '" . $conf['ff']['popupcontent'] . "';\n";
			$js .= "var infowindow = new google.maps.InfoWindow({content:contentString});\n";
			if ($conf['ff']['popupoptions'] == 'instant') {
				$js .= "infowindow.open(map$ext,marker);\n";
			}
			$js .= "google.maps.event.addListener(marker,'click',function(){infowindow.open(map$ext,marker);});\n";
		}
			// marker and popup END
		$js .= "}\n";
			// go go go
		$js .= "initialize();\n";
		$js .= "</script>";
		return $js;
	}

}
?>
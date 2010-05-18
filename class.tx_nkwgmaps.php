<?php
require_once(t3lib_extMgm::extPath('nkwlib')."class.tx_nkwlib.php");

class tx_nkwgmaps extends tx_nkwlib {

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

}
?>
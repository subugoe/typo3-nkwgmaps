<?php
require_once(t3lib_extMgm::extPath('nkwlib')."class.tx_nkwlib.php");

class tx_nkwgmaps extends tx_nkwlib {

	function singleGmapsJS($conf)
	{
		$js = "<script type=\"text/javascript\" src=\"http://maps.google.com/maps/api/js?sensor=".$conf["ff"]["sensor"]."\"></script>";
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

	function multiGmapsJS($conf)
	{
		$js = "<script type=\"text/javascript\" src=\"http://maps.google.com/maps/api/js?sensor=".$conf["ff"]["sensor"]."\"></script>
			   <script type=\"text/javascript\">
			   var bounds;";

		if ($conf["ff"]["mapcenterbutton"] == "true")	{
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
					map.setCenter(bounds.getCenter());
					map.fitBounds(bounds);
					if(map.getZoom() > ".$conf["ff"]["zoom"].") map.setZoom(".$conf["ff"]["zoom"].");
				});
			}
			";
		}

		$js .= "
			function initialize() {
				var latlng = new google.maps.LatLng(".$conf["ff"]["latlngCenter"].");";

		# Set marker for each entry, also if addresses are equal => mulitple markers on one point possible
/*		for($i=0; $i<$conf["ff"]["cntMarker"]; $i++)	{
			$js .= "var latlng".$i." = new google.maps.LatLng(".$conf[$i]["latlng"].");\n";
			$jsAppend .= "
					var marker".$i." = new google.maps.Marker({
						position: latlng".$i.", 
						map: map_".$conf["ff"]["mapName"].", 
						title:'".$conf[$i]["popupcontent"]." - ".$conf[$i]["address"]."'
					});\n";
		}
*/		

		# improved routine: summarize all entries, which have the address in one marker
		for($i=0; $i<$conf["ff"]["cntMarker"]; $i++)	{
			if(!$geocodes[$conf[$i]["latlng"]]) $geocodes[$conf[$i]["latlng"]] = $conf[$i]["popupcontent"]." - ".$conf[$i]["address"];
			else	$geocodes[$conf[$i]["latlng"]] = $conf[$i]["popupcontent"].", ".$geocodes[$conf[$i]["latlng"]];
		}

		$j = 0;
		foreach($geocodes as $key => $value)	{
			$info = explode(" - ",$value);
			$js .= "
				var latlng".$j." = new google.maps.LatLng(".$key.");";
			$jsAppend .= "
				var marker".$j." = new google.maps.Marker({
					position: latlng".$j.", 
					map: map_".$conf["ff"]["mapName"].", 
					title:'".$info[1]."'
				});\n";
			$j++;
		}
		$conf["ff"]["cntMarker"] = count($geocodes);
		################################################################################

		$js .= "
				var mapDiv = document.getElementById('".$conf["ff"]["mapName"]."');
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
				var map_".$conf["ff"]["mapName"]." = new google.maps.Map(mapDiv, myOptions);";
		$js .= $jsAppend;

		if ($conf["ff"]["mapcenterbutton"] == "true")
		{
			$js .= "
				var homeControlDiv = document.createElement('DIV');
				var homeControl = new HomeControl(homeControlDiv, map_".$conf["ff"]["mapName"].", latlng);
				homeControlDiv.index = 1;
				map_".$conf["ff"]["mapName"].".controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv);
			";
		}

		$js .= "
				bounds = new google.maps.LatLngBounds;";

		# Set marker for each entry (look above)
/*		for($i=0; $i<$conf["ff"]["cntMarker"]; $i++)	{
			if ($conf[$i]["popupcontent"])	{
				$js .= "
					var contentString = '".$conf[$i]["popupcontent"]."';
					var infowindow".$i." = new google.maps.InfoWindow({
						content: contentString
					});
				";
		
				// Popups (Bubbles) are shown after initialization, if option is set
				if ($conf["ff"]["popupoptions"] == "instant")
					$js .= "infowindow".$i.".open(map_".$conf["ff"]["mapName"].",marker".$i.");";
				$js .= "
					google.maps.event.addListener(marker".$i.", 'click', function() {
						infowindow".$i.".open(map_".$conf["ff"]["mapName"].",marker".$i.");
					});
				";
				$js .= "bounds.extend(marker".$i.".position);";
			}
		}
*/		
		# improved routine: summarized markers
		$j = 0;
		foreach($geocodes as $key => $value)	{
			$info = explode(" - ",$value);
			$js .= "
				var contentString = '".$info[0]."';
				var infowindow".$j." = new google.maps.InfoWindow({
					content: contentString
				});
			";
	
			// Popups (Bubbles) are shown after initialization, if option is set
			if ($conf["ff"]["popupoptions"] == "instant")
				$js .= "infowindow".$j.".open(map_".$conf["ff"]["mapName"].",marker".$j.");";
			$js .= "
				google.maps.event.addListener(marker".$j.", 'click', function() {
					infowindow".$j.".open(map_".$conf["ff"]["mapName"].",marker".$j.");
				});
			";
			$js .= "bounds.extend(marker".$j.".position);";
			$j++;
		}
		######################################

		# fit displayed map to markers
		$js .= "map_".$conf["ff"]["mapName"].".fitBounds(bounds);";
		$js .= "google.maps.event.addListener(map_".$conf["ff"]["mapName"].", 'zoom_changed', function() {
					if (map_".$conf["ff"]["mapName"].".getZoom() > ".$conf["ff"]["zoom"]." ) {
						map_".$conf["ff"]["mapName"].".setZoom(".$conf["ff"]["zoom"].");
					}
				});";
		
		$js .= "
			}
			initialize();
		</script>";
		return $js;
	}
	
	function directions($conf)	{
		$js = "<script type=\"text/javascript\" src=\"http://maps.google.com/maps/api/js?sensor=".$conf["ff"]["sensor"]."\"></script>
			   <script type=\"text/javascript\">";
		$js .= "
			var directionDisplay;
			var directionsService = new google.maps.DirectionsService();
			var map_".$conf["ff"]["mapName"].";
			var latlng;";
			
		if ($conf["ff"]["mapcenterbutton"] == "true")	{
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
				google.maps.event.addDomListener(controlUI,'click',function(){map.setCenter(latlng);map.setZoom(".$conf["ff"]["zoom"].");});
			}
			";
		}
		
		$js .= "
			function initialize() {
			  directionsDisplay = new google.maps.DirectionsRenderer();
			  latlng = new google.maps.LatLng(51.53290, 9.93496);
			  var myOptions = {
				center: latlng,
				scaleControl: ".$conf["ff"]["scale"].",
				mapTypeControl: true,
				mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.".$conf["ff"]["maptypecontrol"]."},
				navigationControl: true,
				navigationControlOptions: {style: google.maps.NavigationControlStyle.".$conf["ff"]["navicontrol"]."},
				mapTypeId: google.maps.MapTypeId.".$conf["ff"]["maptypeid"].",
				zoom:".$conf["ff"]["zoom"].",
			  }
			  map_".$conf["ff"]["mapName"]." = new google.maps.Map(document.getElementById('".$conf["ff"]["mapName"]."'), myOptions);
			  directionsDisplay.setMap(map_".$conf["ff"]["mapName"].");";
		
		if ($conf["ff"]["mapcenterbutton"] == "true")	{
			$js .= "
				var homeControlDiv = document.createElement('DIV');
				var homeControl = new HomeControl(homeControlDiv, map_".$conf["ff"]["mapName"].", latlng);
				homeControlDiv.index = 1;
				map_".$conf["ff"]["mapName"].".controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv);";
		}
		$js .= "
			}
			  
			function calcRoute() {
			  var start = '".$conf["ff"]["start"]."';
			  var end = '".$conf["ff"]["end"]."';
			  var request = {
				origin:start, 
				destination:end,
				travelMode: google.maps.DirectionsTravelMode.".$conf["ff"]["travelmode"]." 
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

	# test routine to fix missing controls problem, with multiple maps on one page 
	# tried to extend variable-names -> fails
	# not fixed yet (24.06.2010)
	function singleGmapsJStest($conf)
	{
		$ext = "_".$conf["ff"]["mapName"];
		$js = "<script type=\"text/javascript\" src=\"http://maps.google.com/maps/api/js?sensor=".$conf["ff"]["sensor"]."\"></script>";
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
				google.maps.event.addDomListener(controlUI,'click',function(){map.setCenter(latlng);map.setZoom(".$conf["ff"]["zoom"].");});";
			$js .= "}\n";

			$js .= "function initialize() {\n";

				// make map START //
				$js .= "var latlng$ext = new google.maps.LatLng(".$conf["ff"]["latlon"].");\n";
				$js .= "var mapDiv$ext = document.getElementById('".$conf["ff"]["mapName"]."');\n";
				$js .= "var myOptions$ext = {
						zoom: ".$conf["ff"]["zoom"].",
						center: latlng$ext,
						scaleControl: ".$conf["ff"]["scale"].",
						mapTypeControl: true,
						mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.".$conf["ff"]["maptypecontrol"]."},
						navigationControl: true,
						navigationControlOptions: {style: google.maps.NavigationControlStyle.".$conf["ff"]["navicontrol"]."},
						mapTypeId: google.maps.MapTypeId.".$conf["ff"]["maptypeid"]."
					};\n";
				$js .= "var map$ext = new google.maps.Map(mapDiv$ext, myOptions$ext);\n";
				// make map END //

				// home button stuff START //
				$js .= "var homeControlDiv$ext = document.createElement('DIV');\n";
				$js .= "var homeControl$ext = new HomeControl(homeControlDiv$ext,map$ext,latlng$ext);\n";
				$js .= "homeControlDiv$ext.index = 1;\n";
				$js .= "map$ext.controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv$ext);\n";
				// home button stuff END //

				// marker and popup START //
				$js .= "var marker = new google.maps.Marker({position: latlng$ext, map: map$ext, title:'".$conf["ff"]["address"]."'});\n";
				if ($conf["ff"]["popupcontent"])
				{
					$js .= "var contentString = '".$conf["ff"]["popupcontent"]."';\n";
					$js .= "var infowindow = new google.maps.InfoWindow({content:contentString});\n";
					if ($conf["ff"]["popupoptions"] == "instant") $js .= "infowindow.open(map$ext,marker);\n";
					$js .= "google.maps.event.addListener(marker,'click',function(){infowindow.open(map$ext,marker);});\n";
				}
				// marker and popup END //

			$js .= "}\n";

			$js .= "initialize();\n"; // go go go

		$js .= "</script>";
		return $js;
	}

}
?>
<?php
header("Cache-Control: no-cache, must-revalidate, max-age=0 no-store");

session_start();

$_SESSION["validated"] = true;

if ($_SERVER["HTTP_X_FORWARDED_PROTO"] === "http") && strpos($_SERVER['HTTP_HOST'],'eta.') === false) {
	$redirect = "https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	header("Location: ".$redirect);
	exit();
}

if (strpos($_SERVER['HTTP_HOST'],'eta.') === false){
	if (filemtime('js/script.orig.js')>filemtime('js/script.min.js')){
		include_once("php/sc.php");
	}
}

?>
<!doctype html>
<html>

<head>
	<title>EV Highway Status - The alternate Rapid Charger status map for all your devices.</title>
	<meta name="description" content="An alternate, smartphone friendly, electric car (EV) charge station status map with route planner for the UK's Rapid Chargers."/>
	<meta charset="text/html; utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="default" />
	<meta name="apple-mobile-web-app-title" content="Rapid Map"/>
	<link rel="shortcut icon" href="img/favicon.ico">
	<link rel="apple-touch-icon" href="img/apple-icon-152x152.png">
	<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,400italic,600italic' rel='stylesheet' type='text/css'>
	<?php include_once('php/css_control.php');?>
	<script src="../js/sweetalert.min.js?v=1"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
	<?php 

	if(strpos($_SERVER['HTTP_HOST'],'eta.') == false ) {
		$timestamp = date('His',filemtime('js/script.min.js'));
		echo '<script src="js/script.min.js?uid=' . $timestamp . '"></script>
';
	} else {

		echo '<script src="js/script.orig.js"></script>
';
	}

	;?>
	<script src='https://maps.googleapis.com/maps/api/js?key=AIzaSyDwK9RMf6XoFyGXlAUDq07fIlR3YJhkLp0&libraries=places'></script>
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	<script type="text/javascript">google.charts.load('current', {packages: ['corechart']});</script>
	<?php 
	if (file_exists("php/analytics.php")) {
			include_once("php/analytics.php");
	}
	?>
	
</head>

<body>
	<div class="wrap">
		<nav class = "nav">
			<div class="dropdown">
  				<button class="dropbtn">Networks</button>
  				<div class="dropdown-content">
  					<a onmousedown="updateProviders('ecotricity')">    <img class="checkmark_pro" id="ecotricity" src="../img/checkmark.png" height="15" width="15"/>Ecotricity</a>
    				<a onmousedown="updateProviders('polar')">         <img class="checkmark_pro" id="polar" src="../img/checkmark.png" height="15" width="15"/>Polar</a>
    				<a onmousedown="updateProviders('cyc')">           <img class="checkmark_pro" id="cyc" src="../img/checkmark.png" height="15" width="15"/>CYC</a>
    				<a onmousedown="updateProviders('tesla')">         <img class="checkmark_pro" id="tesla" src="../img/checkmark.png" height="15" width="15"/>Tesla SC</a>
    				<a onmousedown="updateProviders('cpg')">           <img class="checkmark_pro" id="cpg" src="../img/checkmark.png" height="15" width="15"/>CPG/SSE</a>
  					<a onmousedown="updateProviders('nissan')">        <img class="checkmark_pro" id="nissan" src="../img/checkmark.png" height="15" width="15"/>Nissan</a>
  					<a onmousedown="updateProviders('ecarni')">        <img class="checkmark_pro" id="ecarni" src="../img/checkmark.png" height="15" width="15"/>ecar NI</a>
  					<a onmousedown="updateProviders('esbie')">         <img class="checkmark_pro" id="esbie" src="../img/checkmark.png" height="15" width="15"/>ESB IE</a>
  				</div>
			</div>
			<div class="dropdown">
  				<button class="dropbtn">Map Tools</button>
  				<div class="dropdown-content">
  					<a onclick="showRouteInputs()">Route Planner</a>
  					<a id="showlocation" onclick="getCurrentLocation()">Show Location</a>
  					<a id="heatmyleaf" onclick="heatMeUp()">Heat my LEAF</a>
  					<a id="heatmyleaf" onclick="routeData()">Route Settings</a>
  				</div>
			</div>
			<div class="dropdown">
  				<button class="dropbtn">Connector</button>
  				<div class="dropdown-content">
    				<a onmousedown="updateConnectors(2)">    <img class="checkmark_con" src="../img/checkmark.png" height="15" width="15"/>CCS</a>
    				<a onmousedown="updateConnectors(1)">    <img class="checkmark_con" src="../img/checkmark.png" height="15" width="15"/>CHAdeMO</a>
    				<a onmousedown="updateConnectors(3)">    <img class="checkmark_con" src="../img/checkmark.png" height="15" width="15"/>AC</a>
    				<a onmousedown="updateConnectors(7)">    <img class="checkmark_con" src="../img/checkmark.png" height="15" width="15"/>Tesla SC</a>
     			</div>
			</div>
			<div class="dropdown">
  				<button class="dropbtn" onclick="">Support</button>
  				<div class="dropdown-content">
  					<a id="about">About</a>
  					<a href="mailto:&#099;&#111;&#110;&#116;&#097;&#099;&#116;&#064;&#101;&#118;&#104;&#105;&#103;&#104;&#119;&#097;&#121;&#115;&#116;&#097;&#116;&#117;&#115;&#046;&#099;&#111;&#046;&#117;&#107;">Email</a>
  					<a href="https://twitter.com/EVHighwayMap" target="_blank">Twitter</a>
  					<a style="vertical-align:middle;" href="https://www.paypal.me/evhighway/2" target="_blank">Buy me a<img style="vertical-align:middle;padding-left:8px;" class="beer" src="../img/beer.png" height="37" width="25"/></a> 
  					
				</div>
			</div>
		</nav>	
		
		<div id="route">
			<form id="route-form">
				<input class="routeinput" type="input" placeholder="Starting from.." id="routestart"/>
				<img id="route-switch"style="vertical-align:middle;" src="img/switch.gif" height="25" width="30"/>
				<input class="routeinput" type="input" placeholder="Going to.." id="routeend"/>
				<button type="button" class="route-add" onclick="calcRoute(true)">Go</button>
				<input type="submit" style="visibility:hidden;"/>
			</form>
		</div>

		<div id="clear-route">
			<a onclick="clearRoute()">Clear route</a>
		</div>

		<div id="route-overview">
			<p>My Route</p>
			<div id="table-cont">
				<table id="waypoint-table" ></table>
				
			</div>
			<p>Click a pin, or location on map, to add a waypoint..</p>			
			<div id="waypoints-tab" onclick="toggleWaypoints.toggle()">
				<div id="waypoint-tab-inner">
					<p>Route</p>
				</div>
			</div>
		</div>

		<div class="carwings-login">
			<img id="close" src="img/dialog-close.svg" height="22" width="22" onclick="heatMeUp()"/>
			<img id="loading" src="img/loading_spinner.gif" height="40" width="40"/>
			<h3>Heat my LEAF up!</h3>
			<form onsubmit="event.preventDefault(); submitCarwingsAction()">
				<input placeholder="Carwings username" type="text" id="username" name="username">
				<input placeholder="Carwings password" type="password" id="password" name="password">
				<select id="carwings-options">
					<option value="ac_on">AC On</option>
					<option value="ac_off">AC Off</option>
					<option value="battery">Battery Status</option>
					<option value="start_charge">Charge Start</option>
				</select>
				<h5>Your credentials are never stored.</h5>
				<input id="carwings-submit" type="submit" value="Go">
				
			</form>
		</div>

		<div class="vehicle-energy-data">
			<img id="close" src="img/dialog-close.svg" height="22" width="22" onclick="routeData()"/>
			<div class="energy-cont">
				<h3>Route Data</h3>
				<h5>Modify the vehicle data and temperature for total route power calculations here.</h5>
				<table class="temperature">
				 <tr>
				 	<td>Temperature</td>
				 	<td><input type="number" step="1" max="40" min="-20" class="temperature-value"/></td>
				 </tr>
				</table>
				<table class="vehicle-data">
					<tr>
						<th>Speed (mph)</th>
						<th id="unit-title">Wh/mile</th>
					</tr>
					<?php
					$speeds = [25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75];
					for ($i=0; $i < count($speeds) ; $i++) { 
						echo '<tr>
						<td>'.$speeds[$i].'</td>
						<td><input type="number"  class="vehicle-data-row"/></td>
					</tr>
					';
					}
	?>				<tr style="text-align:center;">
						<td style="width:20%;"><b>Units:</b></td>
						<td style="width:80%;">
							<label for="whpm">Wh/mi</label>
							<input class="vehicle-unit" type="radio" name="unit" id="whpm" value="whpm"> 
							<label for="mpkwh">mi/kWh</label>
							<input class="vehicle-unit" type="radio" name="unit" id="mpkwh" value="mpkwh">
						</td>
					</tr>
				</table>
				<div class="buttons">
					<input onclick="setVehicleData('update')" type="submit" value="Store"/>
					<input onclick="setVehicleData('reset')" type="submit" value="Reset"/>	
				</div>
			</div>			
		</div>
		
		<div class="rssgen" >
			<p id="rssgen-text" onclick="requestUrl()">Generate RSS URL</p>
			<input id="rss-response" type="text"></input>
			<p id="rss-clear" onclick="rssClear()">Clear</p>
		</div>

		<div class="about">
			
			<img id="close" src="img/dialog-close.svg"/>
					
			<h2 class="about-heading">About EVHighwayStatus</h2>
			<div class="about-cont">
				<div class="who-menu">
					<h3 class="about-title menu-title">
						Who am I?
						<div class="expand-h">
						</div>
						<div class="expand-v">
						</div>
					</h3>
					<div class="who-menu-cont menu-cont">
						<p>
							Welcome to my website.  I am Andrew Lees; a self-taught web developer (for as long as this site has been live!) and electric vehicle enthusiast.  I created this website in November 2015 as a new EV owner to provide functionality that I wanted which at the time either wasn&#39;t available, or what was available was too complicated and not very userfriendly. What started as a simple replacement for the Ecoticity Electric Highway map, has now expanded to include complex route planning, dynamic RSS feed creation for Ecotricity charge points, charge stop suggestions based on a given battery capacity &amp; minimum desired charge level taking in to account route elevation variations, and total energy usage projections for any given route.  Wanting this information on the move I&#39;ve also made the website mobile optimised, whereas network providers&#39; maps were often unusable on a smartphone.
						</p>
						<p>
							Also, as a Nissan Leaf owner, I wanted to be able to remotely check the battery status and remotely start the car&#39;s heating in the winter. This was typically available from the Nissan mobile app; however the app was disabled for a few months at the beginning of 2016 due to a security flaw. Within a week of the app being disabled I created a solution via the site, which utilises the Nissan website login directly in the background but with a much simpler interface. This substitute tool still remains in place (pending future development) even though the app has since been updated. A side project if you will!
						</p>	
						<p>
							Last but not least, I always strive to find new and more efficient methods of achieving functionality of the website, I had no web development experience prior to setting out on this project, as as such am always aware that any one solution (as well as one&#39;s knowledge) is not exhaustive and can always be improved. I therefore truely welcome any suggestions to the website.  Any updates I make are published on the <a href="http://www.twitter.com/EVHighwayMap" target="_blank">@EVHighwayMap</a> Twitter feed. For Ecotricity Electric Highway users, I have also developed the <a href="http://www.twitter.com/EVHighwayStatus" target="_blank">@EVHighwayStatus</a> Twitter account which delivers status updates on charge locations on the Ecotricity Electric Highway.
						</p>
					</div>
				</div>
				<div class="about-menu">
					<h3 class="about-title menu-title">
						<div class="expand-h">
						</div>
						<div class="expand-v">
						</div>
						How do I use the site?
					</h3>
					<div class="about-menu-cont menu-cont">
						<p>
							The map shows the status and locations of (nearly all) rapid charge points across the UK.  You can filter these charge points by network under the Networks menu item; choose as many as you wish. To find out the cost of using the different networks, please visit the network providers&#39; websites directly.
	 					</p>
						<p>
							The colour of the pins reflect the current status of each charge point, as reported by each network: green means online, red is offline and blue is a planned charge point. The source of the status data is indicated within each charger pop-up window. 
						</p>
						<p>
							The description of each charge point shows the connector(s) available.  You can filter charge points by connector depending on your vehicle compatibility under the Connector menu; again, choose as many as you wish.
						</p>
					</div>
				</div>
				<div class="tools-menu">
					<h3 class="tools-title menu-title">
						<div class="expand-h">
						</div>
						<div class="expand-v">
						</div>
						What tools are available?
					</h3>
					<div class="tools-menu-cont menu-cont">
						<h4>Route Planner</h4>
						<p>
							With the route planner, you can plan a route between two destinations and the map will show you all the nearby charging points within your chosen radius. If you enable &#39;suggest charge stops&#39; in the My Route box, you&#39;ll be presented with a list of suggested charge stops on your route. These are based on the capacity of your battery entered and the minimum charge specified (this is the lowest charge you&#39;re comfortable getting to before stopping).
						</p>
						<p>
							If you want to add waypoints to your route, either click a location on the map, or if it&#39;s a charger you&#39;d like to add, just click on the location and select &#39;waypoint&#39; from the pop-up. Your route will be recalculated to include these waypoints.  
						</p>
						<p>
							There may be more than one route available, in which case you can select which you&#39;d like to show in the My Route box. Also, you can click and drag the proposed route if you&#39;d like to override a particular aspect of it. Chargers, suggestions and energy usuage will all be dynamically updated.
						</p>
						<p>
							Your routes will also show estimated energy consumption based on the altitude, this is below the chart. You can tweak the default energy efficiency at different speeds under Map Tools and Route Settings. The default is for a Gen 2 24kWh Leaf.
						</p>
						<h4>Status history</h4>
						<p>
							For Ecotricity Electric Highway locations you can view the status history that has been recorded against that location by clicking the <img src="img/history.png" width="20" height="20"/> icon. We&#39;ve been keeping a database of the status changes since November 2015 and as such anything since then will be shown in the pop-up. This may help give you an idea as to the reliabilty of a particular location.
						</p>
						<h4>Show my Location</h4>
						<p>
							This will show your location on the map and track your location whilst active. A circle around the arrow represents the accuracy of the your currently reported location.
						</p>
						<p>
							You can click on the arrow and add your location as either the start, or end of your route.
						</p>
						<h4>Heat my Leaf</h4>
						<p>
							This is the tool I created to allow access to the common requests to a Nissan Leaf. Just enter your Carwings username and password, choose from the dropdown the action you&#39;d like (AC on, AC off, battery info, or charge start) and hit go.
						</p>
						<p>
							Note: Your credentials are sent directly to Nissan&#39;s servers and aren&#39;t stored in any way. My PHP script just automates the login process to simplify the operation.
						</p>
						<h4>RSS Feeds</h4>
						<p>
							For all Ecotricity Electric Highway chargers, you&#39;re able to create your own customised RSS feed. You&#39;ll be given a unique URL which you can add to an RSS reader of your choosing. Every time the feed is accessed, you'll be given the latest status available for each of the chargers on your list.
						</p>
						<p>
							To add a location, click on a pin, select RSS, and add the connector you&#39;re interested in. Repeat this for as many as you like and then click &#39;Generate RSS URL&#39;. You'll then be presented with the unique URL to copy and paste as required.
						</p>
					</div>
				</div>
				<!-- <div class="faqs-menu">
					<h3 class="faqs-title menu-title">FAQs</h3>
					<div class="faqs-menu-cont menu-cont">
						<p>
							FAQs will go here..
						</p>
					</div>
				</div> -->
			</div>
		</div>

		 <div class="bubble">
			<p>Welcome. Please choose your connector.</p><img src="img/dialog-close.svg" height="22" width="22" onclick="toggleBubble(false)"/>
		</div>

		<div id="icons">
			<p>Icons by <a href="http://www.icons-land.com" target="_blank">Icons-Land </a></p>
		</div>

		<div id="updated">
			<p></p>
		</div>

		<div id="mapbase">
			<noscript>
				<div id="noscript">
					<p>Javascript is required to use this site.</p>
					<p>Please enable and retry.</p> 
				</div>
			</noscript>
		</div>
	</div>

	
</body>
</html>

<?php 
session_start();
if ( $_SESSION["device"] == "portable" ) {
	echo '<script>var device = "portable";</script>';
} else {
	echo '<script>var device = "computer";</script>';
}

?>

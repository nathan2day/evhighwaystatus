<?php

require("php/database_r2.php");
require(dirname(__DIR__)."/app/headers.php");

$betaActive = false;

session_start();

$_SESSION["beta"] = false;

if (isset($_GET["uid"])  && $betaActive){
	$result = checkEmailValidatedState(["user_id" => $_GET["uid"]]);
	if ($result === "yes" && $_GET["beta"] === "true"){
		$_SESSION["beta"] = true;

		if ( strpos($_SERVER["REQUEST_URI"],"beta") === false ){
			$redirect = "https://".$_SERVER["HTTP_HOST"]."/beta.php?".$_SERVER["QUERY_STRING"];
			header("Location: ".$redirect);
			exit();
		}
	}
}

$_SESSION["validated"] = true;

// if ($_SERVER["HTTP_X_FORWARDED_PROTO"] !== "https" && strpos($_SERVER['HTTP_HOST'],'etaz.') === false) {
// 	$redirect = "https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
// 	header("Location: ".$redirect);
// 	exit();
// }

if (strpos($_SERVER['HTTP_HOST'],'ew.') === false){
	if (!$_SESSION["beta"] || !$betaActive){
		if (filemtime('js/script.orig.js')>filemtime('js/script.min.js')){
			include_once("php/sc.php");
		}
	} else {
		if (filemtime('js/script.beta.orig.js')>filemtime('js/script.beta.min.js')){
			include_once("php/sc.php");
		}
	}
	
}

?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />

	<title>EVHighwayStatus - The alternate electric car charger status map for all your devices.</title>
	<meta name="description" content="An independent, alternate, smartphone friendly electric vehicle charge station status map for the UK's rapid chargers with advanced route planner. Created by an EV driver, for an EV driver."/>

	<!-- Twitter Card -->

	<meta property="twitter:card" content="summary_large_image"/>
	<meta property="twitter:site" content="evhighwaymap"/>
	<meta property="twitter:description" content="An independent, alternate, smartphone friendly electric vehicle charge station status map for the UK's rapid chargers with advanced route planner. Created by an EV driver, for an EV driver."/>
	<meta property="twitter:title" content="EVHighwayStatus - An EV driver's map"/>
	<meta property="twitter:image" content="https://evhighwaystatus.co.uk/img/car-wide.jpg"/>
	<meta property="twitter:image:alt" content="EVHighwayStatus Car Icon"/>

	<!-- Open Graph -->

	<meta property="og:site_name" content="EVHighwayStatus"/>
	<meta property="og:type" content="website"/>
	<meta property="og:url" content="https://evhighwaystatus.co.uk"/>
	<meta property="og:description" content="An independent, alternate, smartphone friendly electric vehicle charge station status map for the UK's rapid chargers with advanced route planner. Created by an EV driver, for an EV driver."/>
	<meta property="og:title" content="EVHighwayStatus - An EV driver's map"/>
	<meta property="og:image" content="https://evhighwaystatus.co.uk/img/car-wide.jpg"/>
	<meta property="og:image:alt" content="EVHighwayStatus Car Icon"/>

	<!-- Facebook -->

	<meta property="fb:app_id" content="260883924295945"/>

	<!-- iOS Setup -->
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="default" />
	<meta name="apple-mobile-web-app-title" content="EVHWS"/>

	<!-- Icons -->

	<link rel="apple-touch-icon-precomposed" sizes="57x57" href="img/apple-touch-icon-57x57.png" />
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="img/apple-touch-icon-114x114.png" />
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="img/apple-touch-icon-72x72.png" />
	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="img/apple-touch-icon-144x144.png" />
	<link rel="apple-touch-icon-precomposed" sizes="120x120" href="img/apple-touch-icon-120x120.png" />
	<link rel="apple-touch-icon-precomposed" sizes="152x152" href="img/apple-touch-icon-152x152.png" />
	<link rel="icon" type="image/png" href="img/favicon-32x32.png" sizes="32x32" />
	<link rel="icon" type="image/png" href="img/favicon-16x16.png" sizes="16x16" />
	<meta name="application-name" content="EVHighwayStatus"/>
	<meta name="msapplication-TileColor" content="#FFFFFF" />
	<meta name="msapplication-TileImage" content="img/mstile-144x144.png" />

	<!-- Fonts -->

	<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,400italic,600italic' rel='stylesheet' type='text/css'>
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

	<!-- Script & CSS -->

	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jQuery.mmenu/5.6.5/css/jquery.mmenu.all.min.css">
	<link rel="stylesheet" href="css/w3.css"/>
	<?php include_once('php/css_control.php');?>
	<script src="https://use.fontawesome.com/112f0c1659.js"></script>
	<script src="js/sweetalert.min.js?v=1"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery.mmenu/5.6.5/js/jquery.mmenu.all.min.js"></script>
    <script src="js/lock.min.js"></script>
    <script src="js/auth0-7.0.4.min.js"></script>
    <script src="js/moment.js"></script>
    	
	<?php
	
	if(strpos($_SERVER['HTTP_HOST'],'ew.') === false ) {
		if ($_SESSION["beta"]  && $betaActive){
			$timestamp = date('His',filemtime('js/script.beta.min.js'));
			echo '<script src="js/script.beta.min.js?uid=' . $timestamp . '"></script>
';
			
		} else {
			$timestamp = date('His',filemtime('js/script.min.js'));
			echo '<script src="js/script.min.js?uid=' . $timestamp . '"></script>
';
		}

		$timestamp = date('His',filemtime('js/alerts.js'));
			echo '<script src="js/alerts.js?uid=' . $timestamp . '"></script>
';
	} else {

		echo '<script src="js/script.orig.js"></script>
';
		$timestamp = date('His',filemtime('js/alerts.js'));
			echo '<script src="js/alerts.js?uid=' . $timestamp . '"></script>
';
	}
	?>
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
<div id="page">
	<div class="wrap">
		<button class="hamburger hamburger--collapse" type="button">
	  		<span class="hamburger-box">
	    		<span class="hamburger-inner"></span>
	  		</span>
		</button>
		<nav id = "menu">
			<ul>
				<span id='account'><i class="fa fa-user fa-fw"></i><span class="username">Guest</span><span class="logout">Login</span></span>
				<li class="car-icon">
					<span class="menu-icon-container"><i class="fa fa-car menu-icon"></i></span>
				</li>
				
				<li>
					<span id="routeplanner"><i class="fa fa-road fa-fw"></i>Route Planner</span>
				</li>
				<li><span><i class="fa fa-compass fa-fw" aria-hidden="true"></i>Map Tools</span>
					<ul>
	  					<li>
	  						<span id="showlocation"><i class="fa fa-location-arrow fa-fw" aria-hidden="true"></i>Show Location</span>
	  					</li>
	  					<li>
	  						<span id="heatmyleaf"><i class="fa fa-leaf fa-fw" aria-hidden="true"></i>Heat my LEAF</span>
	  					</li>
	  				</ul>
	  			</li>

     			<li>
     				<span><i class="fa fa-cog fa-fw" aria-hidden="true"></i>Setup</span>
  					<ul>
  						<li><span><i class="fa fa-car fa-fw" aria-hidden="true"></i>Choose Vehicle</span>
  							<ul class="manufacturers">
  							</ul>
  						<li><span><i class="fa fa-bolt fa-fw" aria-hidden="true"></i>Networks</span>
			  				<ul class="Vertical networks">
			  					<li><span data-value="ecotricity"><i class="fa fa-check fa-fw" aria-hidden="true"></i>Ecotricity</span></li>
			    				<li><span data-value="polar"><i class="fa fa-check fa-fw" aria-hidden="true"></i>Polar</span></li>
			    				<li><span data-value="cyc"><i class="fa fa-check fa-fw" aria-hidden="true"></i>CYC</span></li>
			    				<li><span data-value="tesla"><i class="fa fa-check fa-fw" aria-hidden="true"></i>Tesla</span></li>
			    				<li><span data-value="cpg"><i class="fa fa-check fa-fw" aria-hidden="true"></i>CPG/SSE</span></li>
			    				<li><span data-value="engenie"><i class="fa fa-check fa-fw" aria-hidden="true"></i>Engenie</span></li>
			  					<li><span data-value="nissan"><i class="fa fa-check fa-fw" aria-hidden="true"></i>Nissan</span></li>
			  					<li><span data-value="podpoint"><i class="fa fa-check fa-fw" aria-hidden="true"></i>PodPoint</span></li>
			  					<li><span data-value="ecarni"><i class="fa fa-check fa-fw" aria-hidden="true"></i>ecar NI</span></li>
			  					<li><span data-value="esbie"><i class="fa fa-check fa-fw" aria-hidden="true"></i>ESB IE</span></li>
	  						</ul>
	  					</li>
	    				<li><span><i class="fa fa-plug fa-fw" aria-hidden="true"></i>Connector</span>
  							<ul class="Vertical connectors">
			    				<li><span data-value="2"><i class="fa fa-check fa-fw" aria-hidden="true"></i>CCS</span></li>
			    				<li><span data-value="1"><i class="fa fa-check fa-fw" aria-hidden="true"></i>CHAdeMO</span></li>
			    				<li><span data-value="3"><i class="fa fa-check fa-fw" aria-hidden="true"></i>Type-2 (tethered)</span></li>
			    				<li><span data-value="4"><i class="fa fa-check fa-fw" aria-hidden="true"></i>Type-2 (socket)</span></li>
			    				<li><span data-value="7"><i class="fa fa-check fa-fw" aria-hidden="true"></i>Tesla</span></li>
     						</ul>
     					</li>
     					<li>
	  						<span id="wizard"><i class="fa fa-magic fa-fw" aria-hidden="true"></i>Setup Wizard</span>
	  					</li>
     					<li><span><i class="fa fa-cogs fa-fw" aria-hidden="true"></i>Adv. Settings</span>
	  						<ul>
	  							<li>
	  								<span id="route-settings"><i class="fa fa-dashboard fa-fw" aria-hidden="true"></i>Efficiency Figs.</span>
	  									<ul class="efficiency-settings">
	  										<li>
	  											<span class="menu-header">Temperature:</span>
		  									</li>
		  									<li>
												<span class="w3-row">
													<span  class="w3-col s2">&ensp;</span>
													<label class="w3-col s3" for="temperature">Temp:</label>
													<span  class="w3-col s1">&ensp;</span>
													<input class="w3-col s4 w3-center temperature-value" type="number" id="temperature"/>
													<span  class="w3-col s2">&ensp;</span>
												</span>
											</li>
	  										<li>
	  											<span class="menu-header">Customise efficiency:</span>
	  										</li>
<?php
					$speeds = [25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75];
					for ($i=0; $i < count($speeds) ; $i++) { 
			  echo '<li>
						<span class="w3-row">
							<span  class="w3-col s2">&ensp;</span>
							<label class="w3-col s3" for="speed_'.$speeds[$i].'">'.$speeds[$i].'mph:</label>
							<span  class="w3-col s1">&ensp;</span>
							<input class="w3-col s4 w3-center vehicle-data-row" type="number" id="speed_'.$speeds[$i].'"/>
							<span  class="w3-col s2">&ensp;</span>
						</span>
					</li>
					';
					}
	?>									<li>
	  										<span class="menu-header">Units:</span>
	  									</li>
										<li>
											<span class="menu-two-radios w3-center">
												<label for="whpm">Wh/mi</label>
												<input class="vehicle-unit" type="radio" name="unit" id="whpm" value="0"/> 
												<label for="mpkwh">mi/kWh</label>
												<input class="vehicle-unit" type="radio" name="unit" id="mpkwh" value="1"/>
											</span>
										</li>
										<li>
											<span class="Spacer"></span>
										</li>
										<li>
											<span class="w3-container w3-hover-opacity w3-red w3-border w3-center" id="reset-vehicle-data">Reset Defaults</span>
										</li>
									</ul>
	  							</li>
	  						</ul>
	  					</li>
     				</ul>
     			</li>

				<li><span><i class="fa fa-question fa-fw" aria-hidden="true"></i>Support</span>
					<ul>
	  					<li>
	  						<a href="mailto:&#099;&#111;&#110;&#116;&#097;&#099;&#116;&#064;&#101;&#118;&#104;&#105;&#103;&#104;&#119;&#097;&#121;&#115;&#116;&#097;&#116;&#117;&#115;&#046;&#099;&#111;&#046;&#117;&#107;"><i class="fa fa-envelope fa-fw" aria-hidden="true"></i>Email us</a>
	  					</li>
	  					<li>
	  						<a style="vertical-align:middle;" href="https://www.paypal.me/evhighway/2" target="_blank"><i class="fa fa-smile-o fa-fw" aria-hidden="true"></i>Buy me a<img alt="beer" style="vertical-align:middle;padding-left:8px;" class="beer" src="../img/beer.png" height="37" width="25"/></a>
	  					</li>
	  				</ul>
	  			</li>
	  				
	  			<li>
	  				<span id="about"><i class="fa fa-info fa-fw"></i>About</span>
	  			</li>	
			</ul>
		</nav>

		<div id="route-overview">
			
			<div class="route-overview-title">
				Route Planner
				<div class="mobile-triangle-cont">
					<i class="mobile-triangle fa fa-chevron-left" aria-hidden="true"></i>
				</div>
			</div>

			<div id="route-cont">
				<div class="route-inputs-container">
					<div class="route-input-cont">
						<i class="material-icons route-input-marker">place</i
						><input class="routeinput" autocomplete="off" autocorrect="off" type="text" placeholder="Choose a start point.." id="routestart"/>
						<i class="material-icons route-input-clear">clear</i>
						<span class="route-input-fade"></span>
						<i class="material-icons route-input-breadcrumbs">more_vert</i>
					</div>
					<div class="route-input-cont">
						<i class="material-icons route-input-marker">place</i
						><input class="routeinput" autocomplete="off" autocorrect="off" type="text" placeholder="Choose an end point.." id="routeend"/>
						<i class="material-icons route-input-clear">clear</i>
						<span class="route-input-fade"></span>
						<i class="material-icons route-input-breadcrumbs">more_vert</i>
					</div>
				</div>
				
				<div class="myroute-title route-options-title">
					My route	
					<div class="expand-h"></div>
					<div class="expand-v"></div>
				</div>
				<div class="directions-display-cont" style="width:100%;">
					<p class="add-pin">Click a pin, or location on map, to add a waypoint..</p>
					<div class="directions-display">
					</div>
				</div>	
			</div>	

			<div class="myroute-tab">
				<i class="tab-triangle fa fa-chevron-right" aria-hidden="true"></i>
			</div>

		</div>

		<div class="w3-modal carwings-login">
			<div class="w3-modal-content w3-animate-top">

				<header class="w3-container">
					<span class="w3-closebtn">&times;</span>
					<h2>Heat my LEAF up!</h2>
				</header>

				<div class="w3-container">
					<form onsubmit="event.preventDefault(); submitCarwingsAction()">
						<input class="w3-input" placeholder="Carwings username" type="text" id="username" name="username"/>
						<input class="w3-input" placeholder="Carwings password" type="password" id="password" name="password"/>
						<select id="carwings-options" class="w3-select">
							<option value="ac_on">AC On</option>
							<option value="ac_off">AC Off</option>
							<option value="battery">Battery Status</option>
							<option value="start_charge">Charge Start</option>
						</select>
						<p>Your credentials are never stored.</p>
						<div class="login-container w3-section">
							<input id="carwings-submit" type="submit" value="Go" class="w3-btn-block w3-green w3-bottombar w3-border-white w3-hover-blue w3-hover-none"/>
							<i id="loading" class="fa fa-refresh fa-spin fa-fw "></i>
						</div>
					</form>
				</div>
			</div>
		</div>
		
		<div class="rssgen"  >
			<p id="rssgen-text" onclick="requestUrl()">Generate RSS URL</p>
			<input id="rss-response" type="text"/>
			<p id="rss-clear" onclick="rssClear()">Clear</p>
		</div>

		<div class="w3-modal" id="about-menu">
			<div class="w3-modal-content w3-animate-top">
			
				<header class="w3-container">
					<span class="w3-closebtn">&times;</span>						
					<h2 class="about-heading">About EVHighwayStatus</h2>
				</header>

				<div class="w3-container w3-section">
					<div class="who-menu">
						<div class="about-title menu-title">
							<h3>Who am I?</h3>
							<div class="expand-h">
							</div>
							<div class="expand-v">
							</div>
						</div>
						<div class="who-menu-cont menu-cont">
							<p>
								Welcome to EVHighwayStatus.  I am Andrew Lees; a self-taught web developer (for as long as this site has been live!) and electric vehicle enthusiast.  I created this website in December 2015 as a new EV owner to provide functionality that I wanted which at the time either wasn&#39;t available, or what was available was too complicated and not very userfriendly.
							</p>
							<p>
								What started as a simple replacement for the Ecoticity Electric Highway map, has now expanded to include complex route planning, dynamic RSS feed creation for Ecotricity charge points, charge stop suggestions based on a given battery capacity &amp; minimum desired charge level taking in to account route elevation variations, and total energy usage projections for any given route.
							</p>
							<p>
								Wanting this information on the move I&#39;ve also made the website mobile optimised, whereas network providers&#39; maps were often unusable on a smartphone.
							</p>
							<p>
								Last but not least, I always strive to find new and more efficient methods of achieving functionality of the website, I had no web development experience prior to setting out on this project, as as such am always aware that any one solution (as well as one&#39;s knowledge) is not exhaustive and can always be improved.
							</p>
							<p>
								I truely welcome any suggestions or criticism to the website.  Any updates I make are published on the <a href="http://www.twitter.com/EVHighwayMap" target="_blank">@EVHighwayMap</a> Twitter feed. For Ecotricity Electric Highway users, I have also developed the <a href="http://www.twitter.com/EVHighwayStatus" target="_blank">@EVHighwayStatus</a> Twitter account which delivers status updates on charge locations on the Ecotricity Electric Highway.
							</p>
							<p>
								Enjoy!
							</p>	
						</div>
					</div>
					<div class="about-menu">
						<div class="about-title menu-title">
							<div class="expand-h">
							</div>
							<div class="expand-v">
							</div>
							<h3>How do I use the site?</h3>
						</div>
						<div class="about-menu-cont menu-cont">
							<p>
								The map shows the status and locations of (nearly all) rapid charge points across the UK.  You can filter these charge points by network under the Networks menu item; choose as many as you wish. To find out the cost of using the different networks, please visit the network providers&#39; websites directly.
		 					</p>
							<p>
								The colour of the pins reflect the current status of each charge point, as reported by each network: green means online, red is offline and blue is a planned charge point. The source of the status data is indicated within each charger pop-up window. 
							</p>
							<p>
								The description of each charge point shows the connector(s) available.  You can filter charge points by connector depending on your vehicle compatibility under Setup, Connector; again, choose as many as you wish.
							</p>
						</div>
					</div>
					<div class="tools-menu">
						<div class="tools-title menu-title">
							<div class="expand-h">
							</div>
							<div class="expand-v">
							</div>
							<h3>What tools are available?</h3>
						</div>
						<div class="tools-menu-cont menu-cont">
							<div class="w3-panel w3-leftbar w3-pale-green w3-border-green">
								<h4>Route Planner</h4>
							</div>
							<p>
								With the route planner, you can plan a route between two destinations and the map will show you all the nearby charging points within your chosen radius.
							</p>
							<p>
								 If you enable &#39;suggest charge stops&#39; in the My Route box, you&#39;ll be presented with a list of suggested charge stops on your route. These are based on the capacity of your battery entered and the minimum charge specified (this is the lowest charge you&#39;re comfortable getting to before stopping).
							</p>
							<p>
								If you want to add waypoints to your route, either click a location on the map, or if it&#39;s a charger you&#39;d like to add, just click on the location and select &#39;waypoint&#39; from the pop-up. Your route will be recalculated to include these waypoints.  
							</p>
							<p>
								There may be more than one route available, in which case you can select which you&#39;d like to show in the My Route box. Also, you can click and drag the proposed route if you&#39;d like to override a particular aspect of it. Chargers, suggestions and energy usuage will all be dynamically updated.
							</p>
							<p>
								Your routes will also show estimated energy consumption based on the altitude, this is below the chart. You can tweak the default energy efficiency at different speeds under Settings, Advance Settings. The default is for a Gen 2 24kWh Leaf.
							</p>
							<div class="w3-panel w3-leftbar w3-pale-green w3-border-green">
								<h4>Status History</h4>
							</div>
							<p>
								For Ecotricity Electric Highway and Charge Your Car locations, you can view the status history that has been recorded against that location by clicking the History button. We&#39;ve been keeping a database of the status changes since November 2015 for Ecotricity and July for CYC, and as such anything since then will be shown in the pop-up. This may help give you an idea as to the reliabilty of a particular location.
							</p>
							<div class="w3-panel w3-leftbar w3-pale-green w3-border-green">
								<h4>Location Services</h4>
							</div>
							<p>
								This will show your location on the map and track your location whilst active. A circle around the arrow represents the accuracy of the your currently reported location.
							</p>
							<p>
								You can click on the arrow and add your location as either the start, or end of your route.
							</p>
							<div class="w3-panel w3-leftbar w3-pale-green w3-border-green">
								<h4>Heat My LEAF</h4>
							</div>
							<p>
								This is the tool I created to allow access to the common requests to a Nissan Leaf. Just enter your Carwings username and password, choose from the dropdown the action you&#39;d like (AC on, AC off, battery info, or charge start) and hit go.
							</p>
							<p>
								Note: Your credentials are sent directly to Nissan&#39;s servers and aren&#39;t stored in any way. My PHP script just automates the login process to simplify the operation.
							</p>
							<div class="w3-panel w3-leftbar w3-pale-green w3-border-green">
								<h4>RSS Feeds (Available again soon)</h4>
							</div>
							<p>
								For all Ecotricity Electric Highway chargers, you&#39;re able to create your own customised RSS feed. You&#39;ll be given a unique URL which you can add to an RSS reader of your choosing. Every time the feed is accessed, you'll be given the latest status available for each of the chargers on your list.
							</p>
							<p>
								To add a location, click on a pin, select RSS, and add the connector you&#39;re interested in. Repeat this for as many as you like and then click &#39;Generate RSS URL&#39;. You'll then be presented with the unique URL to copy and paste as required.
							</p>
						</div>

						
					</div>

					<footer class="w3-container w3-section">

					</footer>
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
		</div>

		<div id="icons">
			<p class="w3-margin-0">Icons by <a href="http://www.icons-land.com" target="_blank">Icons-Land </a></p>
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

		<div id="map-mask" class="w3-display-middle">
			<div class="w3-center">
				<div class="spinner">
				  <div class="rect1"></div>
				  <div class="rect2"></div>
				  <div class="rect3"></div>
				  <div class="rect4"></div>
				  <div class="rect5"></div>
				</div>
				<span class="" id="map-loading"><span>
			</div>
		</div>

		<div class="social-container">
			 <a class="location-arrow"><i class="fa fa-location-arrow"></i></a>
			 <a class="w3-hide-small icon-link round facebook fill"><i class="fa fa-facebook"></i></a>
			 <a class="w3-hide-small icon-link round twitter fill"><i class="fa fa-twitter"></i></a>
			 <a class="w3-hide-small icon-link round google-plus fill"><i class="fa fa-google-plus"></i></a>

		</div>
	</div>
</div>
<?php 

if ( $_SESSION["device"] == "portable" ) {
	echo '<script>var device = "portable";</script>';
} else {
	echo '<script>var device = "computer";</script>';
}

?>	
</body>
</html>

//
// Runtime
//

var timeOut;
var allmarkers = [];
var map;
var openWindow;  
var activeMarker;
var directionsDisplay;
var directionsService; 
var iconListener;
var altRoutes = 0;
var curRoute = 0;
var GET = {};
var activeRouteInputElement = "";
var locationCircle = null;
var locationIcon = null;
var connectors = [];
var providerreq = {};
providerreq.providers = [];
var openRssWindow;
var c = { };

var route = {
	start:"",
	end:"",
	path: [],
	consumption:{
		total:{
			baseline:0,
			adjusted:0
		},
		waypoints:[]
	},
	clear: function(){
		this.start = "";
		this.end = "";
		this.path = [];
		this.consumption = {
			total:{
				baseline:0,
				adjusted:0
			},
			waypoints:[]
		};
		this.waypoints=[];
	}
};
route.waypoints = [];
c.targetChargers = [];
var firstRun = true;
var heatmeupopen = false;
var showingElevationInfo = false;
var showingRouteSettings = false;
var elevation;
var chart;
var elevResults = [];
var elevData;
var altInfoWindow;
var geocoder;

  

if(typeof(Storage) !== "undefined") {
    var storagePossible = true;
} else {
    var storagePossible = false;
}


//
// Display functions
//

var userController = function(){
	var lock;
	var auth0;
	var loggedIn = false;

	function getProfile(idtoken){
		lock.getProfile(idtoken,function(error,profile){
			if (error) {
				logout();
				login();
				return;
			}
			providersController.update(true);
			loggedIn = true;
			localStorage.id_token = idtoken;
			localStorage.userProfile = JSON.stringify(profile);
			processProfile(profile);
			renewToken(idtoken);	
		});
	}

	function processProfile(profile){
		//determine user's name
		if (profile.user_metadata.firstName) {
			var name = profile.user_metadata.firstName + " " + profile.user_metadata.lastName
		} else {
			var name = profile.name;
		}
		$(".username").html(name);
		$(".logout").html("Logout");

		if (profile.picture.indexOf("cdn.auth0.com") == -1) {
			$(".menu-icon-container").html('<img class="avatar" src="'+profile.picture+'"></img>')
									 .addClass("profile-picture");
		}
		//TODO twitter!
		if (!profile.email_verified) {
			$(".username").html(name + " (provisional)");
			$(".username").css({color:"red"});
			swal({
				title:"Welcome!",
				text:"Registration's complete. To unlock full functionality please verify your email address by following the link in the registration email.",
				type: "success",
				showConfirmButton: true,
				confirmButtonText: ""
			});
		} else {
			$(".username").html(name);
		}

		updateUrl();
	}

	function logout(){
		
		localStorage.id_token = "";
		localStorage.userProfile = "";
		$(".username").html("Guest");
		$(".logout").html("Login");

		$(".menu-icon-container").html('<i class="fa fa-car menu-icon"></i>')
								 .removeClass("profile-picture");
		loggedIn = false;	
	}

	function login(){
		lock.show();
	}

	function renewToken(id_token){
		auth0.renewIdToken(id_token, function (err, delegationResult) {
			if (err){
				return;
			}
  			localStorage.id_token = delegationResult.id_token;
		});
	}
	var imageUrl = window.location.protocol + "//" + window.location.hostname + "/img/Icon-App-60x60@3x.png";
	return {
		init: function(){
			lock = new Auth0Lock('2ZONj7AJBZPWqTAb1UrhWq8d4qZj7xwN', 'evhighwaystatus.eu.auth0.com',{
				theme: {
					logo: imageUrl,
					primaryColor: 'white'
				},
				//container: 'page',
				languageDictionary: {
					title: "Welcome!"
				},
				closable: true,
				auth: {
					redirect: true,
					responseType: 'token'
				},
				additionalSignUpFields: [{
					name: "firstName",
					placeholder: "First name",
					validator: function(name){
						return {
							valid: name.length > 1,
							hint: "Please enter your first name"
						};
					},
					icon: "https://evhighwaystatus.co.uk/img/user-shape.png"
				},
				{
					name: "lastName",
					placeholder: "Last name",
					validator: function(name){
						return {
							valid: name.length > 1,
							hint: "Please enter your last name"
						};
					},
					icon: "https://evhighwaystatus.co.uk/img/user-shape.png"
				}]
			});

			lock.on("authenticated",function(authResult){
				getProfile(authResult.idToken);	
								
			});

			lock.on("authorization_error", function(error) {
				logout();
			});

			auth0 = new Auth0({
			    domain:       'evhighwaystatus.eu.auth0.com',
			    clientID:     '2ZONj7AJBZPWqTAb1UrhWq8d4qZj7xwN',
			    callbackURL:  'https://evhighwaystatus.co.uk',
			    responseType: 'token'
			});

			if (urlParamsController.hashParams()["id_token"]) {
				//authenticated event should handle this
			} else if (localStorage.id_token &&
					   localStorage.id_token.length > 10) {
				getProfile(localStorage.id_token);
			} else {
				
			}

		},
		toggleLoginStatus: function(){
			if (localStorage.id_token) {
				var profile = JSON.parse(localStorage.userProfile);	
				logout();
				window.location.assign("https://evhighwaystatus.eu.auth0.com/v2/logout?returnTo=https://evhighwaystatus.co.uk&client_id="+profile.clientID);
				
			} else {
				login();
			}
		},
		isLoggedIn: function(){
			return loggedIn;
		}

	};
}();

var testAuthHeaders = function(){
	return {
		go: function(){
			$.ajax({
				url: "https://evhighwaystatus.co.uk/php/secured/authentication.php",
				method: "POST",
				crossDomain: true,
				beforeSend: function(xhr) {
					xhr.setRequestHeader("X-Authorization", 'Bearer '+localStorage.id_token);
				}		
			}).done(function(a,b,c){
				var car = a;
			}).always(function(d,e,f){
				var car = d;
			});
		}
	};
}();

var switchRoute = function() {
	
	return function(){
		var currentRoute = [];

		$(".routeinput").each(function(){
			currentRoute.push(this.value);
		});

		currentRoute.reverse();

		$(".routeinput").each(function(index){
			this.value = currentRoute[index];
		});
		
		calcRoute();
	};
}();

var navMenu = function(){

	var dropdwnVisible;

	return {
		initMobile: function(){
			var e = document.getElementsByClassName("dropbtn");
			for (var i = 0; i < e.length; i++) {
				e[i].addEventListener("mousedown",navMenu.showDropdown);
				//e[i].addEventListener("mouseleave",navMenu.showDropdown);

			};

			var e = document.getElementsByClassName("dropdown-content");
			for (var i = 0; i < e.length; i++) {
				//e[i].addEventListener("mouseover",navMenu.showDropdown);
				//e[i].addEventListener("mouseleave",navMenu.showDropdown);
				
			};

		},
		initDesktop: function(){

		},
		showDropdown: function(event){
			var b = document.getElementsByClassName("dropbtn");
			var e = document.getElementsByClassName("dropdown-content");

			for (var i = 0; i < e.length; i++) {
				if (b[i] === event.srcElement){
					if (dropdwnVisible == i){
						e[i].style.display = "none";
						dropdwnVisible = null;
					} else {
						e[i].style.display = "block";
						dropdwnVisible = i;
					}
				} else {
					e[i].style.display = "none";
				}
			}

		},
		hideDropdowns: function(){
			var e = document.getElementsByClassName("dropdown-content");
			for (var i = 0; i < e.length; i++) {
				e[i].style.display = "none";
				e[i].style.display = "";
			}
			dropdwnVisible = null;
		}		
	};
}();

function routeData(){
	if (showingRouteSettings){
		document.getElementById("vehicle-energy-data").style.top = "-550px";
		showingRouteSettings = false;
	} else {
		document.getElementById("vehicle-energy-data").style.top = "50px";
		showingRouteSettings = true;
	}
}

function tweetLinkInfoWindow(lat,lng){
	for (var i = 0; i < allmarkers.length; i++) {
		if (allmarkers[i].info.lat == parseFloat(lat) && allmarkers[i].info.lng == parseFloat(lng)){
			activeMarker = allmarkers[i];
			activeMarker.showInfoWindow();

		}
		
	};
}

function toggleElevation(){
	if (showingElevationInfo){
		document.getElementById("waypoints-elevation").style.display = "none";
		showingElevationInfo = false;
	} else {
		document.getElementById("waypoints-elevation").style.display = "block";
		showingElevationInfo = true;
	}
	
}

var toggleWaypoints = function(){
	var waypointsActive = true;
	var offset = 0;
	

	return {
		isActive: function(){
			return waypointsActive;
		},
		toggle: function(){
			waypointsActive = !waypointsActive;
			navMenu.hideDropdowns();

			offset = $("#route-overview").width() + Number($("#route-overview").css("margin-left").replace("px", "")) + $(".myroute-tab").width() ;




			if (waypointsActive){
				//hide menu
				$("#route-overview").css({left: offset * -1});
				//$("#route-overview").css({left: "auto", right: "100%"});
				$(".tab-triangle").removeClass("fa-chevron-left").addClass("fa-chevron-right")

				if (document.getElementById("routestart").value == ""){
					route.start = "Not yet added.";
				} else {
					route.start = document.getElementById("routestart").value;
				}

				if (document.getElementById("routeend").value == ""){
					route.end = "Not yet added.";
				} else {
					route.end = document.getElementById("routeend").value;
				}
			
			//updateWaypointTable();

			} else {
				//show the menu
				$("#route-overview").css({left: 0});
				//$("#route-overview").css({left: 0, right: "auto"});
				$(".tab-triangle").removeClass("fa-chevron-right").addClass("fa-chevron-left")
			
			}
		}
		
	};
}();

function setConnectorCheckmark(number){
	var checkmarks = document.getElementsByClassName("checkmark_con");

	for (var i = 0; i < checkmarks.length; i++) {
		if (i == number){
			checkmarks[i].style.visibility = "visible";
		} else {
			checkmarks[i].style.visibility = "hidden";
		}
	}  
} 

function heatMeUp(){

	if (heatmeupopen){
		document.getElementsByClassName("carwings-login")[0].style.top = "-550px";
		document.getElementById("loading").style.display = "none";
		heatmeupopen = false;
		navMenu.hideDropdowns();
	} else {
		document.getElementsByClassName("carwings-login")[0].style.top = "50px";
		heatmeupopen = true;
		navMenu.hideDropdowns();
	}

}

function toggleBubble(display){ 
	if (display)
		document.getElementsByClassName("bubble")[0].style.display = "inline-block";
	else {
		document.getElementsByClassName("bubble")[0].style.display = "none";
	}
} 

var routeInputs = function(){
	var visible = false;

	return {
		show: function(){
			if (!visible){
				// document.getElementById("route").style.display = "inline-block";
				document.getElementById("clear-route").style.display = "block";
				document.getElementById("route-overview").style.display = "block";
				navMenu.hideDropdowns();
				visible = true;
			}
		},
		hide: function(){
			// document.getElementById("route").style.display = "none";
			document.getElementById("clear-route").style.display = "none";
			document.getElementById("route-overview").style.display = "none";
			visible = false;
		},
		isActive: function(){
			return visible;
		}
	};
	
}();

function closeNavMenus(){
	var menus = document.getElementsByClassName("dropdown-content");
	for (var i = 0; i < menus.length; i++) {
		menus[i].style.display = "none";
		menus[i].style.display = "";
	}
}

//document ready

var isCordovaApp;

if (document.URL.indexOf('localhost') > -1) {
	isCordovaApp = true;
} else {
	isCordovaApp = document.URL.indexOf('http://') === -1 && document.URL.indexOf('https://') === -1;
}

if (isCordovaApp) {
	document.addEventListener('deviceready', function(){
		initialise();
	}, false);
} else {
	$(document).ready(initialise);
}

function initFacebook(){
	window.fbAsyncInit = function() {
    FB.init({
      appId      : '260883924295945',
      xfbml      : true,
      version    : 'v2.7'
    });
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "//connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));
}

function initialise(){

	urlParamsController.fetch();

	
	if (isCordovaApp) {
		$("#menu").addClass("cordova-app");
	}
	
	$(document).on("markersAdded",function(){
		mapMask.hide();
		document.getElementById("icons").style.visibility = "visible";
		document.getElementById("updated").style.visibility = "visible";

	});

	$(document).on("mapFirstInit",function(){
		setTimeout(userController.init,1000);
		providersController.update(true);
	});

	initFacebook();
	
	//GET for certificate issue in IE and Chrome.
	initalGetRequest();
	
	document.addEventListener('resume', function() {
		setTimeout(providersController.update,50);
	}, false);

	setUp();
	
	vehicleController.init();

	$("#menu").mmenu({
		"extensions": [
            "pageshadow"
         ]
	}, {
         // configuration
         offCanvas: {
            pageSelector: "#page"
         }
     });

	if (isCordovaApp) {
		new FastClick(document.body, {
 			excludeNode: '^pac-'
		});
	}

	var API = $("#menu").data( "mmenu" );

	$(".hamburger").click(function(){
		if ($("#menu").hasClass("mm-opened")) {
			API.close();
		} else {
			API.open();
		}
		
		$(this).blur();
		
	});

	API.bind("opening",function(){
		$(".hamburger").addClass("is-active");
	});

	API.bind("closing",function(){
		$(".hamburger").removeClass("is-active");
		setupWizard.stopProgress();
	});

		
	if (isCordovaApp) {
		$(".hamburger").addClass("cordova-app");
		navigator.splashscreen.hide();
	}
	$(".hamburger").css({"top": 0});

	
	$("#routeplanner").click(function(){
		API.close();
		setTimeout(function(){
			//$("#route-overview").show();
			toggleWaypoints.toggle();
		},410);
	});

	$('#route-settings').click(function(){
		API.close();
		setTimeout(function(){
			$("#vehicle-energy-data").show();
		},410);
	});

	$('#wizard').click(function(){
		API.close();
		setTimeout(function(){
			API.openPanel($("#mm-0"))
			setupWizard.firstVisit();	
		},410);
	});

	$("#heatmyleaf").click(function(){
		API.close();
		setTimeout(function(){
			$(".carwings-login").show();
		},410);
	})

	$("#about").click(function(){
		API.close();
		setTimeout(function(){
			$("#about-menu").show();
		},410);		
	});

	$("#showlocation").click(function(){
		API.close();
		setTimeout(function(){
			locationController.toggleWatch(true);
			//getCurrentLocation();
		},410);
	});

	$(".models span").click(function(e){
		$(".models span > i").fadeTo(50,0);
		$(this).children("i").fadeTo(50,1);
		//API.openPanel($("#mm-2"));

		//which manufacturer was clicked
		var manufacturer = Number($(this).attr("value"));

		//which model was clicked
		var models = $($(this).parents(".models"))
							  .find("span");

		var model = $(models).index(this);

		vehicleController.setVehicle(manufacturer,model);
						
		setupWizard.stopProgress();
				
	});

	$(".connectors span").click(function(e){
		connectorsController.update(Number($(this).attr("data-value")));
	});

	$(".networks span").click(function(e){
		$(this).toggleClass("network-selected");
		providersController.update();
		//providersController.update($(this).attr("data-value"));
	});

	setupWizard.setAPI(API);

	$(".vehicle-data-row").blur(function(){
		vehicleDataController.update();
	});	

	$(".logout").click(function(){
		API.close();
		setTimeout(function(){
			userController.toggleLoginStatus();
		},410);	
	})

	InitialiseMap();

};

var networkStatusController = function() {
	var networkIsOnline = false;
	return {
		onlineEvent: function(){
			networkIsOnline = true;
			networkFailure.hide();
		},
		offlineEvent: function(){
			networkIsOnline = false;
			networkFailure.show();
		},
		isOnline: function(){
			return networkIsOnline;
		}
	};
}();

function setUp(){

	if (device === "portable"){
		navMenu.initMobile();	
	}

	$(window).on("online",function(){
		networkStatusController.onlineEvent();
	});
	$(window).on("offline",function(){
		networkStatusController.offlineEvent();
	});

	vehicleDataController.init();
	

	document.getElementById("routestart").addEventListener("focus",function() {
		activeRouteInputElement = "start";
	});
	document.getElementById("routestart").addEventListener("blur",function() {
		setTimeout(function(){
			if (activeRouteInputElement != "end"){
				activeRouteInputElement = "";
			}
			if (document.getElementById("routestart").value == ""){
				route.start = "Not yet added.";
			} else {
				route.start = document.getElementById("routestart").value;
			}
			//updateWaypointTable();
		},100);
		
	});
		document.getElementById("routeend").addEventListener("focus",function() {
		activeRouteInputElement = "end";
	});
	// document.getElementById("routeend").addEventListener("blur",function() {
	// 	timeOut = setTimeout(function(){
	// 		if (activeRouteInputElement !== "start"){
	// 			activeRouteInputElement = "";
	// 		}
	// 		if (document.getElementById("routeend").value == ""){
	// 			route.end = "Not yet added.";
	// 		} else {
	// 			route.end = document.getElementById("routeend").value;
	// 			if (document.getElementById("routestart").value !== ""){
	// 				calcRoute();
	// 			}
	// 		}

	// 		//updateWaypointTable();
	// 	},250);
	
	// });

	if (urlParamsController.getParams()['src'] == "evhw"){
		connectorsController.update(1);
		connectorsController.update(2);
		connectorsController.update(3);
		connectorsController.update(4);
		connectorsController.update(7);
	} else {
		connectorsController.update("",true);
	}
	
	if (connectors == "none" || localStorage.newIntro === undefined){ 
		//We don't have a stored connector, so display message.
		localStorage.newIntro = true;
		setupWizard.firstVisit();
	} else {
		//We have a stored connector, so check whether the cookie message has been seen.
		if (getCookie("beer") == "" && !isCordovaApp) { //Have they seen the beer message alert?
			swal({
				title:"Just a quick one..",
				text:"You can now buy me a beer from the <b>Support</b> menu if you find this site useful :) <br><br>(This'll close in a second)",
				html:true,
				type: "info",
				timer: 6000
			});

			addCookie("beer","true",365);//update cookie so we don't display it again.
		}		
	}

	

	//add our event listeners

	$("#about-menu h3").click(function(e){
		if (this == e.target){
			$("#about-menu .menu-cont").eq($("#about-menu h3").index(this)).slideToggle();
			$("#about-menu .expand-v").eq($("#about-menu h3").index(this)).fadeToggle();			 
		}
	});

	$('.popup-window>i').click(function(e){
		$(e.target.parentNode).hide();
	});

	$('.w3-modal .w3-closebtn').click(function(e){
		$(e.target).parents(".w3-modal").hide();
		$("#loading").hide();
		$("#carwings-submit").prop("disabled",false);
		$("#carwings-submit").css({"opacity":1});
	});

	$(".myroute-title").click(function(){
		$(".directions-display-cont").slideToggle();
		$(".myroute-title > .expand-v").fadeToggle();
	});

	$(".myroute-tab").click(function(){
		toggleWaypoints.toggle();
	});
	
	$("#route-overview").css("left", ($("#route-overview").width() + Number($("#route-overview").css("margin-left").replace("px", "")) + $(".myroute-tab").width()) * -1).show();

	$(window).on("orientationchange resize", function() {
		if (toggleWaypoints.isActive()){
			$("#route-overview").css("left", ($("#route-overview").width() + Number($("#route-overview").css("margin-left").replace("px", "")) + $(".myroute-tab").width()) * -1);
		}
	});

	$('.about img').click(function(){
		$(".menu-cont").hide();
		$('.about').hide();
		$(".about .expand-v").show();

	});

	$(".mobile-triangle-cont").click(function(){
		toggleWaypoints.toggle();
	});

	$("#planner-close").click(function(){
		toggleWaypoints.toggle();
	});

	elementEventBinder.routeInputClear($(".route-input-clear"));
	elementEventBinder.routeInputs($(".routeinput"));

	$(".icon-link").prop("target","_blank").prop("href",function(index){
		if (index == 0) {return;}
		if (index == 1){
			return "https://www.facebook.com/EVHighwayStatus/";
		} else if (index == 2) {
			return "https://twitter.com/EVHighwayMap";
		} else {
			return "https://plus.google.com/communities/108139006036148477833";
		}
	});	

	$(".location-arrow").click(function(index){
		locationController.toggleWatch(true);
	});

	$(".vehicle-data-row").blur(function(){
		vehicleDataController.update();
	});

	$(".temperature-value").blur(function(){
		vehicleDataController.update();
	});

	$(".vehicle-unit").click(function(){
		if (this.checked){
			vehicleDataController.setUnit(parseFloat(this.value));
		}
	});


	$(".route-overview-title").click(function(){
		var amountToScroll = $("#route-cont").scrollTop();
		var i = 0;
		while (amountToScroll > 0) {
			setTimeout(scroll,i,amountToScroll - 1);
			//$("#route-cont").scrollTop(amountToScroll - 1);
			//amountToScroll = $("#route-cont").scrollTop();
			amountToScroll--;
			amountToScroll--;
			i++;
		}
		
	});

	function scroll(amount) {
		$("#route-cont").scrollTop(amount);
	}

	$("#reset-vehicle-data").click(function(){
		vehicleDataController.reset();
	});
}

var elementEventBinder = function(){
	return {
		routeInputs: function(elements) {
			$(elements).blur(function(){

				setTimeout(function(){
					var readyToCalc = true;
					$(".routeinput").each(function(){
						if (!this.value) {
							readyToCalc = false;
							return;
						}
					});

					if (readyToCalc) {
						calcRoute();
					}
				},100);
			});
		
		},
		routeInputClear: function(elements) {
			$(elements).click(function(){
				$(".routeinput").eq($(".route-input-clear").index(this)).val("");
			});
		}
	};
}();

var setupWizard = function(){
	var inProgress = false;
	var choosingNetworks = false;
	var choosingVehicle = false;
	var choosingConnector = false;
	var API;
	return {
		firstVisit: function(){
			inProgress = true;
			swal({
				title:"Welcome!",
				text:"A warm welcome to you - this is our Setup Wizard. Would you like help setting up a few parameters?",
				type: "info",
				showConfirmButton: true,
				showCancelButton: true,
				closeOnConfirm: false,
				closeOnCancel: false,
				confirmButtonText: "Sure!",
				cancelButtonText: "Skip"
			}, function(isConfirm){
				if (isConfirm){
					swal({
						title:"Setup",
						text:"To allow us to set the right EV connector and battery capacity for route planning purposes, please select your vehicle.",
						type: "info",
						showCancelButton: true,
						showConfirmButton: true,
						closeOnCancel: false,
						confirmButtonText: "Choose",
						cancelButtonText: "Skip"
					}, function (isConfirm){
						if (isConfirm){
							API.open();
							setTimeout(function(){
								API.openPanel($("#mm-3"));
							},500);
							choosingVehicle = true;
						} else {
							inProgress = false;
							swal({
								title:"Enjoy..",
								text:"No problem - you can find all our settings in our menu which is accessed via the 'burger' icon in the corner. ",
								type: "success",
								showConfirmButton: true,
								closeOnConfirm: false,
								confirmButtonText: "Okay"
							},function(){
								swal.close();
								if (getCookie("cookies") === "" && !isCordovaApp){
									cookieMessage();
									
								}
								$(document).trigger("showAlerts");
							});
							
							
						}
					});
				} else {
					inProgress = false;
					swal({
						title:"Enjoy..",
						text:"No problem - you can find all our settings in our menu which is accessed via the 'burger' icon in the corner. ",
						type: "success",
						closeOnConfirm: false,
						showConfirmButton: true,
						confirmButtonText: "Okay"
					},function(){
						swal.close();
						if (getCookie("cookies") === "" && !isCordovaApp){
							cookieMessage();
							
							$(document).trigger("showAlerts");
						}
					});
					
				}
				
			});
				
		},
		postVehicleSelection: function(vehicle){
			if (choosingVehicle){
				setTimeout(function(){
					API.close();
					setTimeout(function(){
						API.openPanel($("#mm-0"));
					},500);
				},250);

				choosingVehicle = false;
				
				var vehicle = vehicleController.getVehicle();

				//Did the user choose a valid vehicle? 

				if (vehicle.manufacturer !== "Other") {
					setTimeout(function(){
						swal({
							title:"Setup",
							text:"Okay, we've set your vehicle to the " + vehicle.manufacturer + " " + vehicle.model.modelName + ".\n\nThe " + vehicle.model.modelName + "'s primary connector is the " + getConnectorName(vehicle.model.connectors[0]) + " connector. Would you like the map to only display locations with this connector?\n\n(You can change this at any time)" , 
							type: "info",
							showConfirmButton: true,
							showCancelButton: true,
							closeOnConfirm: false,
							closeOnCancel: false,
							confirmButtonText: "Yes please",
							cancelButtonText: "Skip"
						}, function (isConfirm) {
							if (isConfirm){
								connectorsController.clear();
								connectorsController.update(vehicle.model.connectors[0]);
								//userGreeting.setupComplete();
							} else {
								
							}
							setupWizard.networkSelection();

						});

					},750);	

				} else {
					//User chose "other" vehicle
					setTimeout(function(){
						swal({
							title:"Setup",
							text:"Looks like we don't list your vehicle yet, sorry about that.<br><br>Please specify the battery capacity (kWh, usable) to use for route planning:" , 
							type: "input",
							inputPlaceholder: "for example: \"21.5\"",
							html: true,
							showConfirmButton: true,
							showCancelButton: false,
							closeOnConfirm: false,
							closeOnCancel: false,
							confirmButtonText: "Confirm",
							//cancelButtonText: "Skip"
						}, function (inputValue) {
							if (inputValue === "" || !(Number(inputValue) <= 100 && Number(inputValue) >= 1) ) {
								swal.showInputError("Please enter a value between 1 and 100");
								return false;
							}
							chargeSuggestionController.setCapacity(Number(inputValue)*1000);
							swal({
								title:"Setup",
								text:"Great! We've updated the planner to <b>"+Number(inputValue).toFixed(2)+"</b> kWh.<br><br>Next, please choose your vehicle's connector(s), just close the menu when you're done to continue." , 
								type: "success",
								html: true,
								showConfirmButton: true,
								showCancelButton: false,
								closeOnConfirm: true,
								closeOnCancel: false,
								confirmButtonText: "Choose",
								//cancelButtonText: "Skip"
							}, function (isConfirm) {
								API.open();
								setTimeout(function(){
									API.openPanel($("#mm-2"));
									setTimeout(function(){
										API.openPanel($("#mm-11"));
									},500);
								},500);
								choosingConnector = true;
							});
						});

					},750);
				}
			}
		},
		networkSelection: function(){
			var networkCount = $(".networks li").length;
			swal({
				title:"Setup",
				text:"Next up we have the network selection. We currently show rapid status data for " + networkCount + " providers in the UK.\n\nDo you want to show all of them, or choose a specific set?" , 
				type: "info",
				showConfirmButton: true,
				showCancelButton: true,
				closeOnConfirm: false,
				closeOnCancel: true,
				confirmButtonText: "All",
				cancelButtonText: "Choose now"
			}, function (isConfirm) {
				if (isConfirm){
					providersController.selectAll();
					setupWizard.setupComplete();
					inProgress = false;
				} else {
					choosingNetworks = true;
					API.open();
					setTimeout(function(){
						API.openPanel($("#mm-2"));
						setTimeout(function(){
							API.openPanel($("#mm-10"));
						},500);
					},500);
				}
				
			});					
		},
		postNetworkSelection: function(){
			setTimeout(function(){
				API.openPanel($("#mm-0"));
				setupWizard.setupComplete();
			},500);
			
			inProgress = false;
			choosingNetworks = false;
		},
		postConnectorSelection: function(){
			choosingConnector = false;
			API.closeAllPanels();
			var connectorNamesArray = connectorsController.getNameArray();
			var listString = "<ul>";
			connectorNamesArray.forEach(function(value){
				listString += "<li>"+value+"</li>";
			});
			listString += "</ul>";

			setTimeout(function(){
				swal({
					title:"Setup",
					text:"Okay, we've set the connector(s) to:<br>"+listString, 
					type: "info",
					html:true,
					showConfirmButton: true,
					//showCancelButton: true,
					closeOnConfirm: false,
					//closeOnCancel: true,
					confirmButtonText: "Next",
					//cancelButtonText: "I'll choose later"
				}, function(){
					setupWizard.networkSelection();
				});	
			},500);

			
		},
		setupComplete: function(){
			swal({
				title:"Complete",
				text:"You're all set.\n\nGo explore and enjoy our site. We hope you enjoy your stay. If you need any assistance, feel free to get in touch via the 'Support' link in the menu." , 
				type: "success",
				showConfirmButton: true,
				//showCancelButton: true,
				closeOnConfirm: true,
				//closeOnCancel: true,
				confirmButtonText: "Let's go!",
				//cancelButtonText: "I'll choose later"
			},function(){
				if (getCookie("cookies") === "" && !isCordovaApp){
					cookieMessage();
					
				}
				$(document).trigger("showAlerts");  
			});
			
		},
		setAPI: function(link){
			API = link;
		},
		stopProgress: function(){
			if (inProgress){
				if (choosingNetworks) {
					setupWizard.postNetworkSelection();
				} else if (choosingVehicle) {
					setupWizard.postVehicleSelection();
				} else if (choosingConnector) {
					setupWizard.postConnectorSelection();
				} else {

				}
			}
		}
	};
}();

function processRouteDirect() {
  	var start = document.getElementById("routestart").value;
  	var end = document.getElementById("routeend").value;

  	if ((start != "") && (end != "")){
  		calcRoute();
  	}
}

function setConnectorFromCookie(override) {
	
	if (override === undefined){
		var connector = getConnectorFromCookie();
	} else {
		var connector = override;
	}
	
	if (connector != false) {
		showMarkers(connector);
		
	} else {
		return false;
	} 
}

function getConnectorFromCookie(override) {

	if (override === undefined){
		var connector = getCookie("connector");	
	} else {
		var connector = override;
	}

	if (connector == "CCS") {
		setConnectorCheckmark(0);
		return "CCS";
	} else if (connector == "CHAdeMO" || connector == "CHAdeMO AC") {
		setConnectorCheckmark(1);
		return "CHAdeMO";
	} else if (connector == "AC") {
		setConnectorCheckmark(2);
		return "AC";
	} else if (connector == "AC Medium") {
		setConnectorCheckmark(2);
		return "AC";
	} else if (connector == "Tesla") {
		setConnectorCheckmark(3);
		return "Tesla";
	} else if (connector == "All") {
		setConnectorCheckmark(4);
		return "All";
	} else {
		return false;
	} 
}

//
// Math functions
//

function calcCordDistance(lat1, lng1, lat2, lng2) {
	var p = 0.017453292519943295;    // Math.PI / 180
	var c = Math.cos;
	var a = 0.5 - c((lat2 - lat1) * p)/2 + 
          c(lat1 * p) * c(lat2 * p) * 
          (1 - c((lng2 - lng1) * p))/2;

	return 12742 * Math.asin(Math.sqrt(a)); // 2 * R; R = 6371 km
}

function calcBearing(lat1,lng1,lat2,lng2){
	var y = Math.sin((lng2-lng1)*(Math.PI/180)) * Math.cos(lat2*(Math.PI/180));
	var x = Math.cos(lat1*(Math.PI/180))*Math.sin(lat2*(Math.PI/180)) -
        Math.sin(lat1*(Math.PI/180))*Math.cos(lat2*(Math.PI/180))*Math.cos((lng2-lng1)*(Math.PI/180));
	var brng = Math.atan2(y, x);

	return (brng * (180/Math.PI) + 360) % 360;
}

//
//	Vehicle function
//

function getConnectorName(id){
		var text;

		if (id == 1) {
			text = "CHAdeMO";
			return text;
		} else if (id == 2) {
			text = "CCS";
			return text;
		} else if (id == 3) {
			text = "Rapid AC";
			return text;
		} else if (id == 7) {
			text = "Tesla";
			return text;
		} else if (id == 6) {
			text = "AC Medium";
			return text;
		} else {
			return "";
		}
}

var vehicleController = function(){
	

	var vehicles = {
		0: {
			manufacturer: "BMW",
			models: {
				0: {
					modelName: "i3 (60Ah)",
					battery: {
						rated: 22,
						usable: 18.8
					},
					connectors: [
						2
					]
				},
				1: {		
					modelName: "i3 (94Ah)",
					battery: {
						rated: 33,
						usable: 27.2
					},
					connectors: [
						2
					]
				},
				2: {
					modelName: "i3 REx (60Ah)",
					battery: {
						rated: 22,
						usable: 18.8
					},
					connectors: [
						2 
					]

				},
				3: {		
					modelName: "i3 REx (94Ah)",
					battery: {
						rated: 33,
						usable: 27.2
					},
					connectors: [
						2
					]
				}

			}
			
		},
		1: {
			manufacturer: "Nissan",
			models: {
				0: {
					modelName: "LEAF (24kWh)",
					battery: {
						rated: 24,
						usable: 21.3
					},
					connectors: [
						1
					]
				},
				1: {
					modelName: "LEAF (30kWh)",
					battery: {
						rated: 30,
						usable: 28.5
					},
					connectors: [
						1
					]
				}	
			}
			
		},
		2: {
			manufacturer: "Renault",
			models: {
				0: {
					modelName: "Zoe Q210",
					battery: {
						rated: 26,
						usable: 22
					},
					connectors: [
						3
					]
				},
				1: {
					modelName: "Zoe R240",
					battery: {
						rated: 26,
						usable: 23.3
					},
					connectors: [
						3
					]
				}		
			}
			
		},
		3: {
			manufacturer: "Tesla",
			models: {
				0: {
					modelName: "Model S 60",
					battery: {
						rated: 60,
						usable: 53.4
					},
					connectors: [
						7,
						3
					]
				},
				1: {
					modelName: "Model S 70",
					battery: {
						rated: 70,
						usable: 62.3
					},
					connectors: [
						7,
						3
					]
				},
				2: {
					modelName: "Model S 80",
					battery: {
						rated: 80,
						usable: 71.2
					},
					connectors: [
						7,
						3
					]
				},
				3: {
					modelName: "Model S 85",
					battery: {
						rated: 85,
						usable: 75.6
					},
					connectors: [
						7,
						3
					]
				},
				4: {
					modelName: "Model S 90",
					battery: {
						rated: 90,
						usable: 80.1
					},
					connectors: [
						7, 
						3
					]
				}	
			}
			
		},
		4: {
			manufacturer: "Volkswagen",
			models: {
				0: {
					modelName: "e-Golf",
					battery: {
						rated: 24,
						usable: 21.5
					},
					connectors: [
						2
					]
				},
				1: {
					modelName: "e-Up",
					battery: {
						rated: 18.7,
						usable: 16.8
					},
					connectors: [
						2
					]
				}
	
			}
			
		},
		5: {
			manufacturer: "Other",
			models: {
				0: {
					modelName: "Not listed",
					battery: {
						rated: 10,
						usable: 10
					},
					connectors: [
						2
					]
				}
			}

		}
	};

	
	
	var faCheck = '<i class="fa fa-check fa-fw" aria-hidden="true"></i>';
	var currentManufacturer;
	var currentModel;

	return {
		init: function(id){
			var manufacturer = 0;
			
			while (vehicles[manufacturer]) {
				var thisManufacturer = $("<li>").append($("<span>").html(vehicles[manufacturer].manufacturer));
				var modelList = $("<ul>",{class: "models"})

				var model = 0;
				while (vehicles[manufacturer].models[model]) {
					$(modelList).append($("<li>").append($("<span>").html(faCheck + vehicles[manufacturer].models[model].modelName).attr("value",String(manufacturer))));
					model++;
				}

				$(thisManufacturer).append(modelList);
				$(".manufacturers").append(thisManufacturer);

				manufacturer++;
			}	
		},
		setVehicle: function(manufacturer,model){
			//set capacity for planner
			var batt = vehicles[manufacturer].models[model].battery.usable;
			chargeSuggestionController.setCapacity(batt*1000);
			$("#route-kwh-capacity").trigger("blur");
			currentManufacturer = manufacturer;
			currentModel = model;
		},
		getVehicle: function(){
			return {
				manufacturer: vehicles[currentManufacturer].manufacturer,
				model: vehicles[currentManufacturer].models[currentModel],
			};
		}
	};
}();

//
// Map functions
//

function AutoCompleteCustom(inputElement){
	//cache our results
	var results = [];

	//Our request
	var autoCompleteRequest = {};

	//Our field value
	var inputFieldContent = '';

	//Save our input element string
	//this.inputId = inputElement;

	//grab our element
	var inputElement = parseInputForElement(inputElement);

	var inputBlurTimeout;

	//Setup listeners

	//Call inputChanged on paste and keydown, with 100ms debounce
	$(inputElement).on("paste keydown click",function(){
		var timeout;
		return function(e){
			if (e.key == "Tab") {return;}
			if (e.type === "click") {
				if ($(".autocomplete-container").length) {
					clearView();
					return;
				}
			}
			clearTimeout(timeout);
			timeout = setTimeout(inputChanged,100,this);
		}
	}());

	//clear the view if user clicks away
	$(inputElement).on("blur",function(e){
		inputBlurTimeout = setTimeout(clearView,100);
	});

	//Our autocomplete functions

	function inputChanged(that){
		//get the map center
		var center = map.getCenter();

		//get span of bounds
		var bounds = map.getBounds();
		var radius = calcCordDistance(bounds.getNorthEast().lat(), bounds.getNorthEast().lng(),
									  bounds.getSouthWest().lat(), bounds.getSouthWest().lng()) / 2;

		inputFieldContent = $(that).val();

		// if (inputFieldContent === "") {
		// 	clearView();
		// 	return;
		// }
		
		autoCompleteRequest = {
			input: inputFieldContent,
			location: center.toUrlValue(),
			radius: radius * 1000
		};

		//Post the content for autocomplete suggestions
		ajaxHandler({
			url: "php/autocomplete.php",
			data: autoCompleteRequest,
			success: updateView
		});
		// $.post("https://evhighwaystatus.co.uk/php/autocomplete.php",JSON.stringify(autoCompleteRequest))
		//  .done(updateView)
		//  .fail(function(a,b,c){
		//   	var test = a;
		//  });
	}

	//create and display array of results

	function addResult(prediction){

		//Form the result
		var description = prediction.description;
		var matchedSubstring = description.substr(prediction.matched_substrings[0].offset, prediction.matched_substrings[0].length);
		var leftText = description.substr(0,prediction.matched_substrings[0].offset);
		var rightText = description.slice(prediction.matched_substrings[0].offset + prediction.matched_substrings[0].length);

		//bold up the matched string
		var resultString = leftText + "<b>" + matchedSubstring + "</b>" + rightText;

		//Split the result by "," so we can style the results
		var ar = resultString.split(",");

		ar.forEach(function(value,index){
			ar[index] = value.trim();
		});

		resultString = ar[0];

		if (ar.length > 1){
			ar = ar.splice(1);
			resultString = resultString + "<span>" + ar.join(", ") + "</span>";	
		}

		$resultRow = $("<div>",{class: "autocomplete-row"}).append('<div class="autocomplete-icon"><i class="fa fa-map-marker fa-fw"></i></div><div class="autocomplete-result">'+resultString+"</div>").click(function(e){
			$(inputElement).val(results.predictions[($(".autocomplete-row").index(e.delegateTarget))-1].description);
			//clearView();
			//clearTimeout(inputBlurTimeout);
			//$(inputElement).blur();
		});
		
		$(inputElement).siblings(".autocomplete-container").append($resultRow);
	}

	function addCurrentLocationResultRow() {
		$resultRow = $("<div>",{class: "autocomplete-row"}).append('<div class="autocomplete-icon"><i class="fa fa-location-arrow fa-fw"></i></div><div class="autocomplete-result">My current location</div>').click(function(e){
			$(inputElement).parent().append('<i class="fa fa-spinner fa-pulse fa-fw myposition-loading"></i>');
			locationController.requestLocationNow(function(position){
				geocodeReverseRequest({
					lat: position.coords.latitude, 
					lng: position.coords.longitude
				},function(results){
					$(inputElement).siblings(".myposition-loading").remove();
					$(inputElement).val(results[0].formatted_address);
				});
			},
			function(){
				$(inputElement).siblings(".myposition-loading").remove();
			});		
		});

		$(inputElement).siblings(".autocomplete-container").append($resultRow);
	}

	function updateView(data){
		results = data;

		//Don't update if we've cleared the input field before this response arrived
		if (!$(document.activeElement).is(inputElement)) {
			clearView();
			return;
		}

		$(inputElement).siblings(".autocomplete-container").remove();
		$container = $("<div>",{class: "autocomplete-container"});
		$(inputElement).after($container);

		addCurrentLocationResultRow();

		for (var i = 0; i < results.predictions.length; i++) {
			addResult(results.predictions[i]);
		};
	}

	function clearView(){
		$(inputElement).siblings(".autocomplete-container").remove();
	}

	function parseInputForElement(input) {
		if (typeof(input) === "string") {
			return $("#"+input);
		} else if ($.isArray(input)) {
			return input[0];
		} else {
			return input;
		}
	}
}



setTimeout(function(){
	
},5000);



function InitialiseMap() {

	urlParamsController.fetch();

	mapMask.show({
		type: "mapLoad",
		showAnimation: true,
	});

	var autoCompleteStart = new AutoCompleteCustom("routestart");
	var autoCompleteEnd = new AutoCompleteCustom("routeend");

	if (device == "computer") {
		var draggable = true;
		var zoomControl = true;
	} else {
		var draggable = false;
		var zoomControl = false;
	}

	geocoder = new google.maps.Geocoder;

	directionsService = new google.maps.DirectionsService();
	directionsDisplay = new google.maps.DirectionsRenderer({
		draggable: draggable,
		preserveViewport: false,
	    panel: $(".directions-display")[0] 
	});

	elevation = new google.maps.ElevationService();

	directionsDisplay.addListener('routeindex_changed', function(){
		var timout;
		return function(){
			clearTimeout(timout);
			timout = setTimeout(processRoute,50);
		};
	}());

	if (urlParamsController.getParams()['z'] > 0 && urlParamsController.getParams()['z'] < 21) {
		var zoom = parseFloat(urlParamsController.getParams()['z']);
	} 

	if (urlParamsController.getParams()['lng']> -12 && urlParamsController.getParams()['lng']< 3 && urlParamsController.getParams()['lat']> 48 && urlParamsController.getParams()['lat']< 60) {
		if (urlParamsController.getParams()['src'] == "evhw"){
			var center = {lat:parseFloat(urlParamsController.getParams()['lat'])+0.02, lng:parseFloat(urlParamsController.getParams()['lng'])};
		} else {
			var center = {lat:parseFloat(urlParamsController.getParams()['lat']), lng:parseFloat(urlParamsController.getParams()['lng'])};
		}
		
		if (typeof zoom == "undefined"){
			var zoom = 10;
		}

	} else {
		var center = {lat:55.1384569, lng:-4.085792};
		if (typeof zoom == "undefined"){
			var zoom = 6;
		}
	}

	var mapoptions = {
		center: center,
		zoom: zoom,
		zoomControl: zoomControl,
		streetViewControl: false,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		disableDefaultUI: true
	}

	map = new google.maps.Map(document.getElementById("mapbase"), mapoptions);

	google.maps.event.addListener(map, 'mousemove', function() {
    	if (device === "portable") {navMenu.hideDropdowns();}
	});

	iconListener = google.maps.event.addListener(map,"tilesloaded", function(){	
		$(document).trigger("mapFirstInit");
		
		google.maps.event.removeListener(iconListener);
	});

	google.maps.event.addListener(map,"tilesloaded", function(){
		var timeout;
		return function(){
			clearTimeout(timeout);
			//timeout = setTimeout(updateUrl,500);
		};
	}());

	google.maps.event.addListener(map,"dragend", function(){
		updateUrl();
	});

	var mapClickFunction = function(){
		var timeout;

		return {
			show: function(e){
				timeout = setTimeout(function(){

					if (!$('div[class^="infowindow"]').length){
						var loc = {
							lat: e.latLng.lat(),
							lng: e.latLng.lng()
						};

						mapWaypointPopup.setWindowPos(loc);
							geocodeReverseRequest(loc,function(result, status){
								mapWaypointPopup.updateResponse(processGeocodeResponse(result,status));
							});
					}

					if (openWindow != null){
			    		openWindow.close();
			    	}

			    	if (openRssWindow != null){
			    		openRssWindow.close();
			    	}

			    	mapWaypointPopup.close();
			    	navMenu.hideDropdowns();
			    	myLocationPopup.close();
			    	showHistoryInfoWindow.close();

				},300,e);
			},
			prevent: function(){
				clearTimeout(timeout)
			}
		}

	}();

	google.maps.event.addListener(map,"click", function(event){
		mapClickFunction.show(event);
	});

	google.maps.event.addListener(map,"dblclick", function(){
		mapClickFunction.prevent();
	});

	if (urlParamsController.getParams()["p"]!== undefined){
		providersController.update(urlParamsController.getParams()["p"].toLowerCase());
	} else {
		//
	}

}

var mapWaypointPopup = function(){
	var infoWindow;
	var geoResponse;
	var pos;
	var offset;
	var waypoint;

	return {
		open: function(){
			if (infoWindow !== undefined){
				infoWindow.close();
				infoWindow.setMap(null);
			}
						
			infoWindow = new google.maps.InfoWindow({
			    content: '<div class = "infowindow">'+
			    			'<h5 style="width:300px">'+geoResponse.title+'</h5>'+
			    			'<div class="w3-row w3-btn-bar">'+
			    			  '<span class="w3-btn w3-round w3-col s4 w3-border w3-border-white w3-green w3-hover-blue" onclick="mapWaypointPopup.setOrigin()">Origin</span>'+
			    			  '<span class="w3-btn w3-round w3-col s4 w3-border w3-border-white w3-green w3-hover-blue" onclick="mapWaypointPopup.setWaypoint()">Waypoint</span>'+
			    			  '<span class="w3-btn w3-round w3-col s4 w3-border w3-border-white w3-green w3-hover-blue" onclick="mapWaypointPopup.setDestination()">Destination</span>'+
			    			  '</div>'+
			    		  '</div>',
				position: pos,
			    zIndex: 5000,
			    maxWidth: 300,
			    pixelOffset: offset,
			    disableAutoPan: true
			});

			infoWindow.open(map);
			offset = new google.maps.Size(0, 0);
						
		},
		setOrigin: function(){
			//routeInputs.show();

			if (geoResponse.title !== "Set this pin as:"){
				$("#routestart").val(geoResponse.title);
				
			} else {
				$("#routestart").val(geoResponse.location.lat + ", " + geoResponse.location.lng);
				openWindow.close();
			}
			
			this.close();
			$("#routestart").blur();

		},
		setWaypoint: function(){
			waypoint = {
				name: geoResponse.title,
				lat: geoResponse.location.lat,
				lng: geoResponse.location.lng
			};

			// if (geoResponse.title === "Set this pin as:"){
			// 	waypoint.name = activeMarker.info.name;
			// 	openWindow.close();
			// }

			processWaypoints(waypoint);
			this.close();
		},
		setDestination: function(){
			//routeInputs.show();
			if (geoResponse.title !== "Set this pin as:"){
				$("#routeend").val(geoResponse.title);
			} else {
				$("#routeend").val(geoResponse.location.lat + ", " + geoResponse.location.lng);
				openWindow.close();
			}
			this.close();
			$("#routestart").blur();
		},
		updateResponse: function(response){
			geoResponse = response;
			this.open();
		},
		setWindowPos: function(position){
			pos = position;
		},
		close: function(){
			if (infoWindow !== undefined){
				infoWindow.close();
				infoWindow.setMap(null);
			}
		},
		setPseudoGeoResp: function(title,location){
			geoResponse = {
				title: title,
				location: location
			};

			offset = new google.maps.Size(activeMarker.offset, -95);

		}
	};
}();

function geocodeReverseRequest(latLng,callback){
	geocoder.geocode({location: latLng},callback);
}

function geocodeRequest(address,callback){
	geocoder.geocode({address: address},callback);
}

function processGeocodeResultForWaypoint(results,status){
	if (status === google.maps.GeocoderStatus.OK) {
		if (results[0]){
			var thisWaypoint = {};
			thisWaypoint.lat = parseFloat(results[0].geometry.location.lat());
			thisWaypoint.lng = parseFloat(results[0].geometry.location.lng());
			thisWaypoint.name = results[0].formatted_address;
			processWaypoints(thisWaypoint);
		} else {
			var thisWaypoint = {};
			thisWaypoint.lat = parseFloat(e.latLng.lat().toFixed(6));
			thisWaypoint.lng = parseFloat(e.latLng.lng().toFixed(6));
			thisWaypoint.name = 'Location: ' + thisWaypoint.lat + ', ' + thisWaypoint.lng;
			processWaypoints(thisWaypoint);
		}
	}
}

function processGeocodeResultForStart(results,status){
	// if (status === google.maps.GeocoderStatus.OK) {
	// 	if (results[0]){
	// 		var a = document.getElementById("routestart");
	// 		a.value = results[0].formatted_address;
	// 	} else {
	// 		var a = document.getElementById("routestart");
	// 		a.value = e.latLng.lat() + ', ' + e.latLng.lng();
	// 	}
	// }

	$("#routestart").val(processGeocodeResponse(results,status).title);
}
function processGeocodeResultForEnd(results,status){
	// if (status === google.maps.GeocoderStatus.OK) {
	// 	if (results[0]){
	// 		var a = document.getElementById("routeend");
	// 		a.value = results[0].formatted_address;
	// 	} else {
	// 		var a = document.getElementById("routeend");
	// 		a.value = e.latLng.lat() + ', ' + e.latLng.lng();
	// 	}
	// }

	$("#routeend").val(processGeocodeResponse(results,status).title);
}

function processGeocodeResponse(results,status){
	if (status === google.maps.GeocoderStatus.OK) {
		if (results[0]){
			return {
				title: results[0].formatted_address,
				location: {
					lat:results[0].geometry.location.lat(),
					lng:results[0].geometry.location.lng()
				}
			};
		} else {
			return {
				title: e.latLng.lat() + ', ' + e.latLng.lng(),
				location: e.latLng
			};
		}
	}
}
function oldDataGeneration(){
	
	var vehicleData = {
		temp: 20,
		whpm: [200,300,400,500,600,500,800,700,200,500,300]
	};
	localStorage.vehicleData = JSON.stringify(vehicleData);
	localStorage.unit = "mpkwh";
}
var vehicleDataController = function() { //(was setvehicledata)

	//private variables

	var vehicleData = {
		temperature: 20,
		efficiencyFigures : [
			[131,144,159,169,192,217,233,256,278,303,333],
			[7.63,6.94,6.29,5.92,5.21,4.61,4.29,3.91,3.6,3.3,3.0]
		],
		unit: 0,
		speeds: [25,30,35,40,45,50,55,60,65,70,75] 
	};



	//private functions
	function updateView(){
		$(".vehicle-data-row").each(function(index,element){
			if (vehicleData.unit === 0){
				$(element).val(vehicleData.efficiencyFigures[vehicleData.unit][index].toFixed(0))
						  .prop("step",1);
			} else {
				$(element).val(vehicleData.efficiencyFigures[vehicleData.unit][index].toFixed(2))
						  .prop("step",0.01);
			}
				
		});
		$(".temperature-value").each(function(index,element){
				$(element).val(vehicleData.temperature);
		});
		$(".vehicle-unit").each(function(index,element){
				if (index === vehicleData.unit) {
					this.checked = true;
				}
		});
	}

	//storage

	function storeData(){
		localStorage.vehicleData = JSON.stringify(vehicleData);
	}

	function retrieveData(){
		if (!localStorage.vehicleData){
			return;
		}
		vehicleData = JSON.parse(localStorage.vehicleData);
	}

	function upgradeHandler(){
		//Check if we need to upgrade data
		if (typeof(vehicleData.unit) === "undefined") {
			//Was a unit defined before?
			if (localStorage.unit) {
				//if so, update from string to new integer
				if (localStorage.unit === "mpkwh") {
					vehicleData.unit = 1;
				} else {
					vehicleData.unit = 0;
				}
			} else {
				//Nothing was specified, default to 0 (mpkwh)
				vehicleData.unit = 0;
			}
		}
		//Update the efficiency data if new style isn't present
		if (!vehicleData.efficiencyFigures) {
			vehicleData.efficiencyFigures = defaultEfficiencyFigures();

			//Did we have some figures stored?
			if (vehicleData.whpm) {
				vehicleData.efficiencyFigures[vehicleData.unit] = vehicleData.whpm;
				syncroniseFigures(vehicleData.unit);
			}
		}
		//update temp to new name if not set already, or default to default.
		if (!vehicleData.temperature && vehicleData.temp) {
			vehicleData.temperature = vehicleData.temp;
		}	
	}

	//data

	function syncroniseFigures(unit){
		var targetArrays = [];
		if (unit !== undefined) {
			var referenceUnit = unit;
		} else {
			var referenceUnit = vehicleData.unit;	
		}	

		//determine which to update

		for (var i = 0; i < vehicleData.efficiencyFigures.length; i++) {
			if (referenceUnit !== i) {
				targetArrays.push(i);
			}
		}

		//update each of them according to their relationship to the reference

		targetArrays.forEach(function(value,index){

			if (referenceUnit === 0) { //reference unit is watt hour per mile
				if (value === 1) {	   //we're updating the miles per kwh from watt hour per mile, so divide by 1
					for (var i = 0; i < vehicleData.efficiencyFigures[referenceUnit].length; i++) {
						vehicleData.efficiencyFigures[value][i] = 1000 / vehicleData.efficiencyFigures[referenceUnit][i];
					}	
				}
			} else if (referenceUnit === 1) { //reference unit is miles per killowatt hour
				if (value === 0) {	  		  //we're updating the watt hour per mile from mile per kiliwatt, so divide by 1
					for (var i = 0; i < vehicleData.efficiencyFigures[referenceUnit].length; i++) {
						vehicleData.efficiencyFigures[value][i] = 1000 / vehicleData.efficiencyFigures[referenceUnit][i];
					}	
				}
			} else {
				//units to add later
			}

		});
	}

	function defaultEfficiencyFigures(){
		var defaultEfficiencyFigures = [
				[131,144,159,169,192,217,233,256,278,303,333],
				[7.63,6.94,6.29,5.92,5.21,4.61,4.29,3.91,3.6,3.3,3.0]
			];
		return defaultEfficiencyFigures;
	}

	function defaultTemperature(){
		var defaultTemp = 20;
		return defaultTemp;
	}

	//public functions

	return {
		init: function(){
			retrieveData();
			upgradeHandler();
			storeData();
			updateView();
		},
		update: function(){
			$(".vehicle-data-row").each(function(index,element){
				vehicleData.efficiencyFigures[vehicleData.unit][index] = parseFloat($(element).val());
			});
			$(".temperature-value").each(function(index,element){
				vehicleData.temperature = parseFloat($(element).val());
			});
			syncroniseFigures();
			storeData();
		},
		reset: function(){
			vehicleData.efficiencyFigures = defaultEfficiencyFigures();
			vehicleData.temperature = defaultTemperature();
			vehicleData.unit = 0;
			storeData();
			updateView();
		},
		setUnit: function(unitInt){
			vehicleData.unit = unitInt;
			storeData();
			updateView();
		},
		get: function(){
			return vehicleData;
		}
	};
	
}();

var vehicleConsumpForSpeed = function(){
	var customWhmi = 0;	

	return {
		get: function(n){

			if (customWhmi > 0){
				return customWhmi;
			}
			var vehicleData = vehicleDataController.get();
			var speeds = vehicleData.speeds;

			var whMi  = vehicleData.efficiencyFigures[0];

			for (var i = 0; i < speeds.length - 1; i++) {

				if (n < speeds[0]){
					return whMi[i];
				}

				if (n > speeds[speeds.length-1]){
					return whMi[speeds.length-1];
				}

				if (n > speeds[i] && n < speeds[i+1]){
					if (speeds.indexOf(n) >= 0) {z=whMi[speeds.indexOf(n)];break;}
					var ratio = (n - speeds[i]) / (speeds[i+1] - speeds[i]);
					var y = (whMi[i+1] - whMi[i]) * ratio;
					var z = whMi[i] + y;
				}
				if (z > 0) {break;}
			}
			return z;
		},
		setOverride:function(mikwh){
			if (mikwh > 0) {
				customWhmi = (1000/mikwh);
			} else {
				customWhmi = 0;
			}
		}
	}; 
		
}();

function processElevationData(result,status){
	var ascent = 0;
	var descent = 0;
	var elChange = 0;
	var routeProgress = 0;	

	if (status === google.maps.ElevationStatus.OK){

		elevResults = [];

		result.forEach(function(val){
			elevResults.push(val);
		});


		if (!$(".charts-menu").length){
			$(".route-options").append($("<div>",{class: "charts-menu"}).append($("<div>",{class: "charts-title route-titles"}).text("Charts").append($("<div>",{class: "expand-h"})).append($("<div>",{class: "expand-v"}))));
			$(".charts-menu").append($("<div>",{id: "waypoints-elevation"}));

			$(".charts-title").click(function(){
				$("#waypoints-elevation").slideToggle();
				$(".route-options .expand-v").eq($(".route-titles").index(this)).fadeToggle();
			});
		}
		
		elevData = new google.visualization.DataTable();
		elevData.addColumn('number', 'Miles');
      	elevData.addColumn('number', 'Altitude');
      	elevData.addColumn({type: 'string', role: 'tooltip'});

		for (var i = 0; i < result.length - 1; i++) {
			var elChange = result[i+1].elevation - result[i].elevation;

			if (elChange > 0){
				ascent += elChange;
			} else {
				descent += Math.abs(elChange);
			}
		
			elevData.addRows([
        		[routeProgress, result[i].elevation, Math.ceil(routeProgress) + " miles in, " + Math.ceil(result[i].elevation) + " meters"]
      		]);

      		var distToLeg = calcCordDistance(result[i].location.lat(),result[i].location.lng(),result[i+1].location.lat(),result[i+1].location.lng())

			routeProgress +=  (distToLeg * 0.621371);
		}
		
		var ascentCon = (ascent / 300) * 1.5;
		var descentCon = (descent / 300) * 0.75;

		route.elevation = {
			start: Math.round(result[0].elevation),
			end: Math.round(result[result.length-1].elevation),
			ascent: Math.round(ascent),
			descent: Math.round(descent)
		};

		var totCon = route.consumption.total.baseline - descentCon + ascentCon;

		var temp = vehicleDataController.get();
		temp = temp.temperature;

		if (temp <=20){
			var tempAdjustTotCon = totCon * (1 + (((20 - temp) / 2) * 0.01));
		} else {
			var tempAdjustTotCon = totCon * (1 - (((20 - temp) / 4) * 0.01));
		}

		route.consumption.total.adjusted = tempAdjustTotCon;

		var options = {
  			animation: {
  				startup: true,
  				duration: 1000,
  				easing: 'out'
  			},
      		curveType: 'function',
      		//width: $(".charts-menu").width(),
      		//height: 200,
      		chartArea:{left:38,top:20,width:$(".charts-menu").width() - 38,height:160},
      		legend: {position: 'none'},
      		vAxis: { format:'#m'}
    	};
	
      	if (chart === undefined ){
      		chart = new google.visualization.AreaChart(document.getElementById("waypoints-elevation"));
     		
        	google.visualization.events.addListener(chart,'onmouseover',chartMouseover);
      		google.visualization.events.addListener(chart,'onmouseout',function(){
      			altInfoWindow.close();
      			altInfoWindow = undefined;
      		});
      		google.visualization.events.addListener(chart,'select',chartClick);

        	chart.draw(elevData, options);
      	} else {
      		chart.draw(elevData, options);
      	}
      	
        var innerText = "Total ascent: " + route.elevation.ascent + "m,  " + "Total descent: " + route.elevation.descent + "m" + "<br>est. Power: " + tempAdjustTotCon.toFixed(2) + "kWh.";

        if (document.getElementById("elevation-summary") == undefined){
        	var eleOverview = document.createElement("p");
        	eleOverview.innerHTML = innerText;
        	eleOverview.style.Text = "text-align:center;font-size:12px;padding-bottom:0;";
        	eleOverview.id = "elevation-summary";
        	var chartDiv = document.getElementById("waypoints-elevation");
        	$("#waypoints-elevation").append(eleOverview);
        	
        } else {
        	document.getElementById("elevation-summary").innerHTML = innerText;
        }	
	}
}

function chartClick(){
 	var e = chart.getSelection();
	if (e.length == 1 && e[0].row !== undefined && e[0].column !== undefined){
		var pos = {
			lat: elevResults[e[0].row].location.lat(),
			lng: elevResults[e[0].row].location.lng()
		};
		map.panTo(pos);
	}	
}

function chartMouseover(e){
	var pos = {};
	pos.lat = elevResults[e.row].location.lat();
	pos.lng = elevResults[e.row].location.lng();

	if (altInfoWindow == undefined){

		var infoWindow = new google.maps.InfoWindow({
		    content: elevData.getValue(e.row,e.column).toFixed(1) + 'm',
			position: pos,
		    zIndex: 5000
		});

		altInfoWindow = infoWindow;

		google.maps.event.addListener(altInfoWindow,'closeclick',function(){
			setTimeout(function(){altInfoWindow = undefined;},100);
		});

		altInfoWindow.open(map);
		infoWindow = undefined;
		
	} else {
		altInfoWindow.setPosition(pos);
		altInfoWindow.setContent(elevData.getValue(e.row,e.column).toFixed(1) + 'm');
	}
}

//
// Geolocation
//

var getGoogleMapsUrl = function(){

	var beforePostCode = 'https://maps.google.co.uk/maps?saddr=current+location&daddr=';
	var afterPostCode = '&mode=driving';

	return function(postCode){
		
		return beforePostCode + postCode.replace(" ","+") + afterPostCode;
	};

}();

var locationController = function() {

	//Our watch ID and options
	var locationWatchId;

	var locationOptions = {
		enableHighAccuracy: true,
		timeout: 10000,
		maximumAge: 0
	};

	//Our location visual elements
	var locationCircle;
	var locationIcon;

	//Watcher for snapping map to location
	var zoomSnapCount;

	//Current state of location
	var locationActive;
	var lastTimeStamp;

	var continuation = false;

	function drawLocationMarker(position,heading,accuracy) {

		//Our location triangle

		if (!locationIcon){
		
			locationIcon = new google.maps.Marker({
				position: position,
				icon: {
					path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
					anchor: new google.maps.Point(0,2.5),
					scale: 5,
					strokeWeight: 1,
					strokeColor: '#0038ff',
					fillColor: '#0038ff',
					fillOpacity: 1,
					rotation: heading
				},
				map: map
			});

			google.maps.event.addListener(locationIcon,'click',function(){
				var loc = locationIcon.getPosition();
				mapWaypointPopup.setWindowPos(loc);
				geocodeReverseRequest(loc,function(result, status){
					mapWaypointPopup.updateResponse(processGeocodeResponse(result,status));
				});
			});
				
		} else {
			locationIcon.setIcon({
					path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
					anchor: new google.maps.Point(0,2.5),
					scale: 5,
					strokeWeight: 1,
					strokeColor: '#0038ff',
					fillColor: '#0038ff',
					fillOpacity: 1,
					rotation: heading
			});

			locationIcon.setPosition(position);
		}	

		//Our location circle			

		if (!locationCircle){

			locationCircle = new google.maps.Circle({
				strokeColor: '#4d78ff',
				strokeOpacity: 0.6,
				strokeWeight: 3,
				fillColor: '#4d78ff',
				fillOpacity: 0.1,
				map: map,
				center: position,
				radius: accuracy
			});

		} else {
			locationCircle.setOptions({
				center: position,
				radius: accuracy
			});
		}
	}

	function locationSuccess(position) {

		

		locationActive = true;

		$("#showlocation").html(
			$("#showlocation").html().replace("Show","Hide")
		);

		if ((position.timestamp - lastTimeStamp) > 1500 || (typeof location.lastTimeStamp === "undefined")){

			lastTimeStamp = position.timestamp;

			var pos = {
				lat: position.coords.latitude,
				lng: position.coords.longitude 
			};

			if (location.zoomSnapCount < 2 && !continuation){
				map.setZoom(11);
				map.panTo(pos);
			}

			location.zoomSnapCount++;

			if (isNaN(position.coords.heading) || position.coords.heading === null ){
				var heading = 0;
			} else {
				var heading = position.coords.heading;
			}

			drawLocationMarker(pos,heading,position.coords.accuracy);	
		}
	}

	function locationError(error){

		var alertText;

		switch(error.code) {
	        case error.PERMISSION_DENIED:
	            alertText = "It looks like you denied the request. You need to accept this to show your location.";
	            continuation = false;
	            break;
	        case error.POSITION_UNAVAILABLE:
	        	continuation = false;
	            alertText = "Your location information is currently unavailable.";
	            break;
	        case error.TIMEOUT:
	        	//try reduced accuracy if we're not already
	        	if (isCordovaApp) {
	        		continuation = true;
	        		locationController.startWatch(true);
	        		return;
	        	}	        	
	            alertText = "The request to get your location timed out.";
	            break;
	        case error.UNKNOWN_ERROR:
	            alertText = "An unknown error occurred.";
	            continuation = false;
	            break;
		}

		locationActive = false;
		zoomSnapCount = 0;

		swal({
			title: "Sorry..",
			text: alertText,
			type: "error"
		});

		locationController.clearWatch();	    
	}

	return {
		startWatch: function(highaccuracy){						
			if (navigator.geolocation) {

				$(".location-arrow").addClass("oscillate");

				navigator.geolocation.clearWatch(locationWatchId);

				location.zoomSnapCount = 0;

				if (highaccuracy) {
					locationOptions.enableHighAccuracy = true;
				} else {
					locationOptions.enableHighAccuracy = false;
					locationOptions.timeout = 30000;
				}

				locationWatchId = navigator.geolocation.watchPosition(
					locationSuccess,
					locationError,
					locationOptions
				);

		    } else {
		    	swal({
		    		title: "Sorry..",
		    		text: "Geolocation doesn't appear to be available on this browser",
		    		type: "error"
		    	});
		    }
		},
		toggleWatch: function(highaccuracy) {
			if (locationActive) {
				locationController.clearWatch();	
			} else {
				locationController.startWatch(highaccuracy);
			}
		},
		requestLocationNow: function() {
			var result = 0;
			var accuracy = 999999;
			var storedPosition;
			var location;
			var clearWatchTimeout;
			var callbackTimeout;
			var lastPosition;
			var correctProximityCount = 0;
			return function(result,error) {
				location = navigator.geolocation.watchPosition(
					function(position){
						if (lastPosition) {
							//This is at least the second result received (lastPosition is object). 
							var difference = calcCordDistance(lastPosition.lat,lastPosition.lng,position.coords.latitude,position.coords.longitude);

							//If within 500 meters increment proximity count 
							if (difference < 0.5) {
								correctProximityCount++;
							} else {
								correctProximityCount = 0;
							}

							//If we get 3 consecutive results within 500m of eachother, end early.
							if (correctProximityCount === 3) {
								result(position);
								clearTimeout(callbackTimeout);
								clearTimeout(clearWatchTimeout);
								navigator.geolocation.clearWatch(location);
							}
						}

						//If the accuracy increases, store that position for returning
						if (accuracy > position.coords.accuracy) {
							accuracy = position.coords.accuracy;
							storedPosition = position;
						}

						//store this position for comparing next time around
						lastPosition = {
							lat: position.coords.latitude,
							lng: position.coords.longitude
						};

					},
					function(){
						error();
					},
					locationOptions
				);

				callbackTimeout = setTimeout(function(){
					result(storedPosition);
				},5000);	

				clearWatchTimeout = setTimeout(function(){
					navigator.geolocation.clearWatch(location);
				},5050);	
			};

		}(),
		clearWatch: function() {
			$(".location-arrow").removeClass("oscillate");
			continuation = false;
			locationActive = false;
			zoomSnapCount = 0;
			navigator.geolocation.clearWatch(locationWatchId);

			$("#showlocation").html($("#showlocation").html().replace("Hide","Show"));

			//Clear the map overlays
			if (locationCircle){
				locationCircle.setMap(null);
				locationCircle = null;
			}
			if (locationIcon){
				locationIcon.setMap(null);
				locationIcon = null;
			}

		}
	};
}();



var myLocationPopup = function(){

	var infoWindow;
	var pos;

	return {
		getPopup: function(){
			pos = locationIcon.getPosition();

			if (infoWindow !== undefined){
				infoWindow.close();
				infoWindow.setPosition(pos)
			} else {
				infoWindow = new google.maps.InfoWindow({
				    content: '<div class = "infowindow-small"><span class="add-to-route" onclick="myLocationPopup.addToOrigin()">Set as Origin</span><span class="add-to-route" onclick="myLocationPopup.addToDest()">Set as Destination</span></div>',
					position: pos,
				    zIndex: 5000,
				    disableAutoPan: true
				});
			}
			
			infoWindow.open(map);
		},
		addToOrigin: function(){
			geocodeReverseRequest(pos,function(results,status){
				if (status === google.maps.GeocoderStatus.OK){
					document.getElementById("routestart").value = results[0].formatted_address;
				} else {
					document.getElementById("routestart").value = pos.lat() + ', ' + pos.lng();
				}
				//routeInputs.show();
			});
			
		},
		addToDest: function(){
			geocodeReverseRequest(pos,function(results,status){
				if (status === google.maps.GeocoderStatus.OK){
					document.getElementById("routeend").value = results[0].formatted_address;
				} else {
					document.getElementById("routeend").value = pos.lat() + ', ' + pos.lng();
				}
				//routeInputs.show();
			});
		},
		close: function(){
			if (infoWindow !== undefined){
				infoWindow.close();
			} 
		}
	};
	
}();

var markerRouteDistanceWindow = function(){

	var distanceIntoRoute = 0;
	var infoWindow;

	var methods = {
		setDistanceIntoRoute: function(a){
			distanceIntoRoute = a.toFixed(0);
		},
		showDistancePopup: function(marker){
			if (infoWindow !== undefined){
			} else {
				infoWindow = new google.maps.InfoWindow({
				    content: distanceIntoRoute + ' mi',
				    zIndex: 5000,
				    pixelOffset: new google.maps.Size(marker.offset, 0),
				    disableAutoPan: true
				});
			}

			infoWindow.open(map,marker);
		},
		closeDistancePopup: function(){
			if (infoWindow !== undefined){
				infoWindow.close();
			}
		}
	};

	return methods;
};


//
// Route functions
//

function addWaypoints(){
	//waypointsActive = !waypointsActive;
	if (toggleWaypoints.isActive()) {
		document.getElementById("route-overview").style.display = "block";
	} else {
		document.getElementById("route-overview").style.display = "none";
	}
} 

function processWaypoints(theWaypoint,auto){
	var newEntry = true;
	for (var i = 0; i < route.waypoints.length; i++) {
		if (route.waypoints[i].lat == theWaypoint.lat && route.waypoints[i].lng == theWaypoint.lng) {
			route.waypoints.splice(i,1);
			newEntry = false;
		} 
	}

	if (newEntry) {
		if (route.waypoints.length > 7){
			swal({
	    		title: "Whoops..",
	    		text: "Sorry, we can't take more than 8 waypoints..",
	    		type: "info"
	    	});
		} else {
			route.waypoints.push(theWaypoint);
			addWaypointField(theWaypoint);
			
			return;
		}
	}
	
	if (auto === undefined){
		$("#routestart").blur();
	}
	
	
}

function addWaypointField(theWaypoint){

	var waypointFieldText = "";
	if (theWaypoint) {
		if (theWaypoint.name === "Set this pin as:") {
			waypointFieldText = theWaypoint.lat + ", " + theWaypoint.lng;
		} else {
			waypointFieldText = theWaypoint.name;
		}
	}
	$inputContainer = $("<div>", {class: "route-input-cont route-waypoint-cont"});		
	$inputField = $("<input>",{type: "input", class: "routeinput route-waypoint-input"}).val(waypointFieldText).attr("autocorrect","off").prop("autocomplete","off");
	elementEventBinder.routeInputs($inputField);
	
	autoCompleteManager.add($inputField);

	$removeCont = $("<div>",{class: "waypoint-remove-container"});

	$remove = $("<i>",{class: "material-icons waypoint-remove"}).append("clear").click(function(e){
		$(".route-waypoint-cont").eq($(".waypoint-remove").index(this)).slideUp(400,function(){
			removeWaypoint($(".waypoint-remove").index(this));
			this.remove();
			$("#routestart").blur();
		});
	});

	$removeCont.append($remove);

	$fade = $("<span>",{class:"route-input-fade"});

	$inputClear = $("<i>",{class: "material-icons route-input-clear"}).append("clear");
	elementEventBinder.routeInputClear($inputClear);

	$marker = $("<i>",{class: "material-icons route-input-marker"}).append("place");
	$breadcrumbs = $("<i>",{class: "material-icons route-input-breadcrumbs"}).append("more_vert");

	$(".route-input-cont").last()
						  .before($inputContainer.append($marker)
						  						 .append($inputField)
						  						 .append($fade)
						  						 .append($inputClear)
						  						 .append($removeCont)
						  						 .append($breadcrumbs));

	$inputContainer.slideDown(400, function(){
		if (theWaypoint) {
			$("#routestart").blur();	
		}
		
	});					  

}

var autoCompleteManager = function(){
	var autoCompleteArray = [];
	return {
		add: function(inputField){
			var autoComplete = new AutoCompleteCustom(inputField[0]);
			autoCompleteArray.push(autoComplete);
		},
		remove: function(n){
			autoCompleteArray.splice(n,1);
		}
	};
}();




function removeWaypoint(waypointNumber){
	route.waypoints.splice(waypointNumber,1);
	autoCompleteManager.remove(waypointNumber);
	
}

function clearTable(table){
	while (table.hasChildNodes()) {
    	table.removeChild(table.firstChild);
	}
}

var spinnerLoader = function(){
	var spinnerActive;
	var timeout;
	return {
		show: function(element){
			var spinHtml = '<div class="sk-circle1 sk-child"></div><div class="sk-circle2 sk-child"></div><div class="sk-circle3 sk-child"></div><div class="sk-circle4 sk-child"></div><div class="sk-circle5 sk-child"></div><div class="sk-circle6 sk-child"></div><div class="sk-circle7 sk-child"></div><div class="sk-circle8 sk-child"></div><div class="sk-circle9 sk-child"></div><div class="sk-circle10 sk-child"></div><div class="sk-circle11 sk-child"></div><div class="sk-circle12 sk-child"></div>';

			$spinner = $("<div>",{class:"sk-circle"}).html(spinHtml);
			
			$container = $("<div>",{class: "spinner-container"});

			$(element).after($container)
					  .after($spinner);

			spinnerActive = true;

			timeout = setTimeout(spinnerLoader.hide,10000);
		},
		hide: function(){
			clearTimeout(timeout);
			$(".sk-circle").remove();
			$(".spinner-container").remove();
			spinnerActive = false;
		},
		active: function(){
			return spinnerActive;
		}
	};
}();

var mapMask = function(){

	var errorTimeout;
	var active;

	function hideAfterTransitionEnd(e){
		console.log(e.originalEvent.propertyName + ": transition has ended");
		if (e.originalEvent.propertyName.indexOf("filter") > -1 || true) {
			$("#map-mask").off("transitionend",hideAfterTransitionEnd);
			$("#map-mask").hide();
		}
	}

	return {
		show: function(options) {
			if (active) {
				return;
			}
			active = true;
			$("#map-mask button").remove();
			$("#mapbase").addClass("map-loading");
			$("#map-mask").addClass("opacity-1");
		
			if (options.showAnimation) {
				$("#map-mask .spinner").show();
			} else {
				$("#map-mask .spinner").hide();
			}
			if (!options.text) {
				options.text = "";
			}
			$("#map-loading").html(options.text);
			$("#map-mask").show();
			
			errorTimeout = setTimeout(function(){
				$("#map-loading").html("Operation timed out..");
				$("#map-mask .spinner").hide();
				$("#map-loading").after($("<button>",{class: "w3-btn w3-margin-top w3-show w3-content w3-center w3-center w3-hover-none w3-green w3-hover-blue"}).append("Retry").click(function(){
					
					InitialiseMap();	
					
					active = false;
					mapMask.show({
						text: options.text,
						type: options.type,
						showAnimation: options.showAnimation
					});
				}));
			},10000); 

		},
		hide: function() {
			// if (!userController.isLoggedIn()){
			// 	return;
			// }
			active = false;
			clearTimeout(errorTimeout);
			
			$("#mapbase").removeClass("map-loading");						 
			$("#map-mask").removeClass("opacity-1").on("transitionend",hideAfterTransitionEnd);	
		}
		
	};	

}();

//mapMask.show("Map requires internet connection.");

function calcRoute(n) {

	if (!spinnerLoader.active()){
			spinnerLoader.show($(".wrap"));
	}

	//check if we're including reverse of route

	if ($("#return-onoffswitch").prop("checked") && n === undefined){
		returnJourney.getReturnRoute();
	} 

  	var start = document.getElementById("routestart").value;
  	var end = document.getElementById("routeend").value;
  	route.start = start;
  	route.end = end;

  	var gWaypoints = [];

  	var exp = /[0-9][.][0-9]{5,}/g;

  	$(".route-waypoint-input").each(function(index,element){
  		var validLatLng = $(element).val().match(/[-]?[0-9]{1,}[.][0-9]{5,}/g);
  		if (validLatLng !== null && validLatLng.length == 2){
  			gWaypoints.push({
  				location:{
  					lat: Number(validLatLng[0]),
  					lng: Number(validLatLng[1])
  			},
  			stopover: true
  			});
  		} else {
  			gWaypoints.push({
  				location: $(element).val(),
  			stopover: true
  			});
  		}
  	});

  	// for (var i = 0; i < route.waypoints.length; i++) {
  	// 	gWaypoints.push(
  	// 		{
  	// 		location:{
  	// 			lat: route.waypoints[i].lat,
  	// 			lng: route.waypoints[i].lng
  	// 		},
  	// 		stopover: true
  	// 		}
  	// 	);
  	// };

  	var request = {
    				origin:start,
    				destination:end,
    				waypoints: gWaypoints,
    				travelMode: google.maps.TravelMode.DRIVING,
    				provideRouteAlternatives: true,
    				unitSystem: google.maps.UnitSystem.IMPERIAL
  	};

  	directionsService.route(request, function(result, status) {

    	if (status === google.maps.DirectionsStatus.OK) {
			altRoutes = result.routes.length - 1;
			curRoute = 0;
			directionsDisplay.setOptions({
				preserveViewport: false
			});
			directionsDisplay.setDirections(result);
			directionsDisplay.setMap(map);
		
			
    	} else if (status === google.maps.DirectionsStatus.NOT_FOUND){
    		swal({
	    		title: "Sorry..",
	    		text: "Couldn't find one or more locations. Please check and retry.",
	    		type: "error"
	    	});
	    	spinnerLoader.hide();
    	} else if (status === google.maps.DirectionsStatus.ZERO_RESULTS){
    		swal({
	    		title: "Sorry..",
	    		text: "No route found. Please check and try again.",
	    		type: "error"
	    	});
	    	spinnerLoader.hide();
    	} else {
    		swal({
	    		title: "Sorry..",
	    		text: "Something went wrong. Please check and try again.",
	    		type: "error"
	    	});
	    	spinnerLoader.hide();
    	}
  	});
}

function toggleAlternateRoutes(direction){
	directionsDisplay.setOptions({preserveViewport: true});
	if (direction == 0){
		if (curRoute > 0 ){
			curRoute--;
		} else {
			curRoute = altRoutes;
		}
	} else {
		if (curRoute < altRoutes ){
			curRoute++;
		} else {
			curRoute = 0;
		}
	}
	
	directionsDisplay.setRouteIndex(curRoute);
	processRoute();
}

var processRoute = function(){

	return function(){		

		if ($("#routestart").val() === "" || $("#routeend").val() === ""){
			return;
		}

		if (!spinnerLoader.active()){
			spinnerLoader.show($(".wrap"));
		}

		var selector = document.getElementById("charger-radius");

		//updateWaypointTable();

		// if (directionsDisplay.getDirections().routes.length != (altRoutes + 1)){
		// 	altRoutes = directionsDisplay.getDirections().routes.length - 1;
		// 	curRoute = altRoutes;
		// }

		curRoute = directionsDisplay.getRouteIndex();


		var onroute;
		var route_path = directionsDisplay.getDirections().routes[curRoute].overview_path;
		route.path = route_path;
		var legs = directionsDisplay.getDirections().routes[curRoute].legs;
		var distance = 0;
		var time = 0;

		//get elevation data for this route

		//sample rate

		if (route_path.length < 100){
			var elReqSampRate = route_path.length;
		} else {
			var elReqSampRate = 100;
		}
		
		//build request 

		var elReq = {
			path: route_path,
			samples: elReqSampRate
		};

		//make the request and pass callback
		
		//elevation.getElevationAlongPath(elReq,processElevationData);

		//store the chosen radius if we can, else default to 2.

		if (storagePossible && localStorage.radius !== undefined){
			var radius = localStorage.radius;
		} else{
			var radius = 2;
		}

		if ($('#charger-radius').length){
			radius = $('#charger-radius').val();
			if (storagePossible){
				localStorage.radius = radius;
			}
		}

		//update route waypoint table 

		//generate row for each leg
		// for (var i = 0; i < legs.length; i++) {

		// 	//is this leg not the last of a multi-leg trip?
		// 	if (i < legs.length - 1){
		// 		var resultRow = document.createElement("tr");
		// 		resultRow.className = "waypoint-row-result";
		// 		var resultData = document.createElement("td");
		// 		resultData.innerHTML = getMilesFromKm(legs[i].distance.value) + " miles, " + getTimeFromSec(legs[i].duration.value);
		// 		resultData.setAttribute("colspan","3");
		// 		resultData.style.cssText = "text-align:center;"
		// 		resultRow.appendChild(resultData);
		// 		//var elementToGet = "waypoint-row-"+(i+1);
		// 		//var currentWaypointRow = document.getElementById(elementToGet);
		// 		$("#waypoint-row-"+(i+1)).after(resultRow);
		// 		//insertAfter(resultRow,currentWaypointRow);
				
		// 	} else { 
		// 		//this leg is the last

		// 		//Are there more than one leg? Add waypoint result as we added it above for single leg.
		// 		if (legs.length > 1) {
		// 			var resultRow = document.createElement("tr");
		// 			resultRow.className = "waypoint-row-result";
		// 			var resultData = document.createElement("td");
		// 			resultData.innerHTML = getMilesFromKm(legs[i].distance.value) + " miles, " + getTimeFromSec(legs[i].duration.value);
		// 			resultData.setAttribute("colspan","3");
		// 			resultData.style.cssText = "text-align:center;"
		// 			resultRow.appendChild(resultData);
		// 			var currentWaypointRow = document.getElementById("waypoint-row-end");
		// 			//insertAfter(resultRow,currentWaypointRow);
		// 			$("#waypoint-row-"+(i+1)).after(resultRow);
		// 		}
 
		// 		//continue with last leg
		// 		var resultRow = document.createElement("tr");
		// 		resultRow.className = "waypoint-row-result-overview";
		// 		var resultData = document.createElement("td");

		// 		for (var x = 0; x < legs.length; x++) {
		// 			distance += legs[x].distance.value;
		// 			time += legs[x].duration.value;
		// 		}

		// 		resultData.innerHTML = 'My route: ' + getMilesFromKm(distance) + " miles, " + getTimeFromSec(time) + " (excl. charging)";
		// 		resultData.setAttribute("colspan","3");
		// 		resultData.style.cssText = "text-align:center;padding-top:5px;"
		// 		resultRow.appendChild(resultData);

		// 		document.getElementById("waypoint-table").appendChild(resultRow);
			
		// 		var resultRow = document.createElement("tr");
		// 		resultRow.className = "waypoint-row-result-alternates";

		// 		var resultData = document.createElement("td");

		// 		var img = document.createElement("img");
		// 		img.className = "img-nav-alternate";
		// 		img.src = "img/nav_left.png";
		// 		img.height = "15";
		// 		img.width = "15";
		// 		img.setAttribute("onClick","toggleAlternateRoutes(0)");
		// 		resultData.appendChild(img);

		// 		var link = document.createElement("span");
		// 		link.innerHTML = 'Route ' + (curRoute + 1) + " of " + (altRoutes + 1);
		// 		resultData.appendChild(link);

		// 		img = document.createElement("img");
		// 		img.className = "img-nav-alternate";
		// 		img.src = "img/nav_right.png";
		// 		img.height = "15";
		// 		img.width = "15";
		// 		img.setAttribute("onClick","toggleAlternateRoutes(1)");
		// 		resultData.appendChild(img);

		// 		resultData.setAttribute("colspan","3");
		// 		resultData.style.cssText = "text-align:center;padding-top:5px;";

		// 		resultRow.appendChild(resultData);
		// 		document.getElementById("waypoint-table").appendChild(resultRow);

				//dropdown for radius from route for markers

				//route options

		if (!$('.route-options-cont').length) {

			//options container

			$('#route-cont').append($("<div>",{class: 'route-options'}).append($("<div>",{class: "route-options-title route-titles"}).text("Options").append($("<div>",{class: "expand-h"})).append($("<div>",{class: "expand-v"}))));
			$('.route-options').append($("<div>",{class: "route-options-cont"}));

			//option 0

			$(".route-options-cont").append($('<div>',{class:"route-option",id: "option-0"}));

			$("#option-0").append($('<div>',{class: "route-option-description"}).text("Suggest charge stops"))
						  .append($("<div>",{class: "route-option-input", id: "option-0-input"}));
		
			$("#option-0-input").append('<div class="onoffswitch"><input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="charge-suggest-checkbox" checked><label class="onoffswitch-label" for="charge-suggest-checkbox"><span class="onoffswitch-inner"></span><span class="onoffswitch-switch"></span></label></div>');

			//set switch value based on storage
			if (localStorage.chargeSuggest !== undefined){
					$("#charge-suggest-checkbox").prop("checked",localStorage.chargeSuggest === "true");
			} 

			//option 1

			$(".route-options-cont").append($('<div>',{class: "route-option", id:"option-1"}).append($('<div>',{class: "route-option-description"}).text("Show chargers within")));

			$sel = $("<select>",{id: "charger-radius",}).change(function(){
				updateMarkersOnMapFromRoute.update();
			});

			var listItems = [0.5,1,2,5,10,20,50];
			
			for (var l = 0; l < listItems.length; l++) {
				$opt = $("<option>");

				if (radius == listItems[l]){
					$opt.prop("selected",true);
				}

				var unit = ' mile';

				if (listItems[l] > 1){
					unit = ' miles';
				}

				$opt.text(listItems[l] + unit);
				$opt.val(listItems[l]);
				$sel.append($opt);
			}

			$("#option-1").append($("<div>",{class: "route-option-input", id: "option-1-input"}).append($sel).css({"text-align": "center"}));


			//option 2 and 3, battery and lower capacity

			$(".route-options-cont").append($('<div>',{class:"route-option",id: "option-2"}));
			$(".route-options-cont").append($('<div>',{class:"route-option",id: "option-21"}));
			$(".route-options-cont").append($('<div>',{class:"route-option",id: "option-3"}));

			$("#option-2").append($('<div>',{class: "route-option-description"}).text("Battery capacity"))
						  .append($("<div>",{class: "route-option-input", id: "option-2-input"}));

			$("#option-21").append($('<div>',{class: "route-option-description"}).text("Battery start level"))
						  .append($("<div>",{class: "route-option-input", id: "option-21-input"}));

			$("#option-3").append($('<div>',{class: "route-option-description"}).text("Battery min. level"))
						  .append($("<div>",{class: "route-option-input", id: "option-3-input"}));

			$("#option-2-input").append($("<div>",{class: "input-cont"}).append($("<input>",{class: "route-kwh", id: "route-kwh-capacity", type: "text"})).append($("<span>").text("kWh")));
			$("#option-21-input").append($("<div>",{class: "input-cont"}).append($("<input>",{class: "route-kwh", id: "route-kwh-start-capacity", type: "text"})).append($("<span>").text("%")));
			$("#option-3-input").append($("<div>",{class: "input-cont"}).append($("<input>",{class: "route-kwh", id: "route-kwh-low-limit", type: "text"})).append($("<span>").text("%")));


			//option 4 

			$(".route-options-cont").append($('<div>',{class:"route-option",id: "option-4"}));

			$("#option-4").append($('<div>',{class: "route-option-description"}).text("Custom mi/kWh?"))
						  .append($("<div>",{class: "route-option-input", id: "option-4-input"}));
		
			$("#option-4-input").append('<div class="onoffswitch"><input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="mikwh-onoffswitch"><label class="onoffswitch-label" for="mikwh-onoffswitch"><span class="onoffswitch-inner"></span><span class="onoffswitch-switch"></span></label></div>');


			//custom mi/kwh option

			$(".route-options-cont").append($('<div>',{class:"route-option",id: "option-5"}));

			$("#option-5").append($('<div>',{class: "route-option-description"}).text("Average efficiency"))
						  .append($("<div>",{class: "route-option-input", id: "option-5-input"}));
					
			$("#option-5-input").append($("<div>",{class: "input-cont"}).append($("<input>",{class: "route-kwh", id: "average-mi-kwh", type: "text"})).append($("<span>").text("mi/kWh")));

			//set switch value and visibility of entry field based on storage

			//check if user has used switch before
			if (localStorage.customMiKwhOption !== undefined){

				//they have used the switch so set it as it was
				$("#mikwh-onoffswitch").prop("checked",localStorage.customMiKwhOption === "true");

				//Was the switch on before?
				if (localStorage.customMiKwhOption === "true"){

					//Yes the switch was on.

					//Did they enter a value?
					if (localStorage.customMiKwh > 0){
						//The switch was on, and they had a value, so update to reflect this
						vehicleConsumpForSpeed.setOverride(Number(localStorage.customMiKwh));
						$("#average-mi-kwh").val(localStorage.customMiKwh);
					} else {
						//The switch was on, but no value was found. Set to default.
						vehicleConsumpForSpeed.setOverride(4.2);
						$("#average-mi-kwh").val(4.2);	
					}

					//The switch was on so show the input field
					
					$("#option-5").show();
				} else {

					//Nope, the switch wasn't on

					//Set the override to use speed relative data (set to 0)
					vehicleConsumpForSpeed.setOverride(0);

					//Now, the switch wasn't on, but did they enter a value previously?
					if (localStorage.customMiKwh > 0){
						//Yes, so update the value field with what was entered previously.
						$("#average-mi-kwh").val(localStorage.customMiKwh);
					} else {
						//The switch wasn't on, and there wasn't a value entered, so use defaults.
						vehicleConsumpForSpeed.setOverride(4.2);
						$("#average-mi-kwh").val(4.2);
					}
				}
				
			} else {
				//They haven't used the switch before
				//The switch is unchecked by default, so nothing to do here but set default value in input field
				$("#average-mi-kwh").val(4.2);

				//Set override to 0 just in case.
				vehicleConsumpForSpeed.setOverride(0);
			}

			//option 6 

			$(".route-options-cont").append($('<div>',{class:"route-option",id: "option-6"}));

			$("#option-6").append($('<div>',{class: "route-option-description"}).text("Include return journey"))
						  .append($("<div>",{class: "route-option-input", id: "option-6-input"}));
		
			$("#option-6-input").append('<div class="onoffswitch"><input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="return-onoffswitch"><label class="onoffswitch-label" for="return-onoffswitch"><span class="onoffswitch-inner"></span><span class="onoffswitch-switch"></span></label></div>');


			//option 7 

			$(".route-options-cont").append($('<div>',{class:"route-option",id: "option-7"}));

			$("#option-7").append($('<div>',{class: "w3-row"})  .append($("<div>",{class: "w3-col s12 w3-left-align"}).append($("<span>",{class:""}).html("Statuses to use for suggestions:")))
																.append($("<div>",{class: "w3-col s12 w3-left-align w3-padding-left w3-padding-right"})  .append($("<div>",{class: "w3-col s6"})
																									  														.append($("<div>").append($("<input>",{value:"online",type:"checkbox",class:"w3-check route-status-include"}))
																									  														.append($("<label>",{class:"w3-padding-left w3-validate"}).html("online")))
																									  														.append($("<div>").append($("<input>",{value:"planned", type:"checkbox",class:"w3-check route-status-include"}))
																									  														.append($("<label>",{class:"w3-padding-left w3-validate"}).html("planned")))
																									  														.append($("<div>").append($("<input>",{value:"unknown", type:"checkbox",class:"w3-check route-status-include"}))
																									  														.append($("<label>",{class:"w3-padding-left w3-validate"}).html("unknown")))
																									  													 )
																									  													.append($("<div>",{class: "w3-col s6"})
																									  														.append($("<div>").append($("<input>",{value:"occupied",type:"checkbox",class:"w3-check route-status-include"}))
																									  														.append($("<label>",{class:"w3-padding-left w3-validate"}).html("occupied")))
																									  														.append($("<div>").append($("<input>",{value:"offline",type:"checkbox",class:"w3-check route-status-include"}))
																									  														.append($("<label>",{class:"w3-padding-left w3-validate"}).html("offline")))
																									  													 )))

			//option functions

			//checkbox for charge suggest

			$("#charge-suggest-checkbox").change(function(){
				if (storagePossible) {localStorage.chargeSuggest = this.checked;}
				if (!this.checked) {
					setTimeout(function(){
						chargeSuggestionController.resetOpacity();
						chargeSuggestionController.clearResult();
						$("#charge-suggest-menu").hide();
					},450);
				} else {
					setTimeout(function(){
						$("#charge-suggest-menu").show();
					},330);
				}
				setTimeout(processRoute,350);
			});

			//checkbox for custom mi/kWh

			$("#mikwh-onoffswitch").change(function(){
				if (storagePossible) { localStorage.customMiKwhOption = this.checked;}

				setTimeout(function(){
					$("#option-5").slideToggle(function(){
						if ($("#mikwh-onoffswitch").prop("checked")){
							$("#average-mi-kwh").trigger("blur");
						} else {
							vehicleConsumpForSpeed.setOverride(0);
							processRoute();	
						}
					});
				},350);					
			
			});

			//checkbox for choosing which statuses to include in route calculation

			$(".route-status-include").change(function(){
				var timeout;

				return function () {
					clearTimeout(timeout);
	
					var routeStatusInclude = [];
					$(".route-status-include").each(function(index,element){
						if (this.checked) {
							routeStatusInclude.push(this.value);
							if (this.value == "occupied") {
								routeStatusInclude.push("in session");
							}
						}
					});
					chargeSuggestionController.setRouteStatusInclude(routeStatusInclude);
					localStorage.routeStatusInclude = JSON.stringify(routeStatusInclude);

					timeout = setTimeout(calcRoute,650);

				};

			}());

			//charger radius change

			$("#charger-radius").change(function(){
				processRoute();
			});
					
			
			//dropdown for each option title

			$(".route-options .route-options-title").click(function(e){
				if (e.target !== this){
					return
				}
				$(".route-options-cont").slideToggle();
				$(".route-options .expand-v").eq($(".route-titles").index(this)).fadeToggle();
			});

			//error checking and auto-process battery capacity					

			$("#route-kwh-capacity").blur(function(){

				if (!(Number(this.value) > 0)) {
					$("#option-2-input .error").remove();
					$("#option-2-input").append($("<span>",{class: "error"}).text("Oops!"));
					$(".route-kwh").eq(0).css({"border-color": "red"});
					return;
				}

				$(".route-kwh").eq(0).css({"border-color": ""});
				$("#option-2-input .error").remove();

				chargeSuggestionController.setCapacity(this.value * 1000);
				if (storagePossible) {
					localStorage.kwhCapacity = this.value;
				}

				$("#route-kwh-low-limit").trigger("blur");
				
			});

			//error checking and auto-process battery starting capacity					

			$("#route-kwh-start-capacity").blur(function(){

				if ( !(Number(this.value) > 0) || !(Number(this.value) < 101) ) {
					$("#option-21-input .error").remove();
					$("#option-21-input").append($("<span>",{class: "error"}).text("Oops!"));
					$(".route-kwh").eq(1).css({"border-color": "red"});
					return; 
				}

				$(".route-kwh").eq(1).css({"border-color": ""});
				$("#option-21-input .error").remove();

				chargeSuggestionController.setStartTankLevel(Number(this.value));
				if (storagePossible) {
					
					localStorage.kwhStartCapacity = this.value;
				}

				processRoute();
				
			});

			//error checking and auto-process battery low limit

			$("#route-kwh-low-limit").blur(function(){

				if (!(Number(this.value) > 0 && Number(this.value) < 100)) {
					$("#option-3-input .error").remove();
					$("#option-3-input").append($("<span>",{class: "error"}).text("Oops!"));
					$(".route-kwh").eq(2).css({"border-color": "red"});
					return;
				}

				$(".route-kwh").eq(2).css({"border-color": ""});
				$("#option-3-input .error").remove();

				chargeSuggestionController.setLowLimit(parseFloat(this.value));
				if (storagePossible) {
					
					localStorage.kwhLowLimit = this.value;
				}

				processRoute();
			});

			//custom mi-kwh values

			$("#average-mi-kwh").blur(function(){
				if (!(Number(this.value) > 0 && Number(this.value) < 10 && this.value.length < 4)) {
					$("#option-5-input .error").remove();
					if (this.value.length > 3){
						var text = "One decimal please!";
					} else {
						var text = "Oops!"
					}
					$("#option-5-input").append($("<span>",{class: "error"}).text(text));
					$(".route-kwh").eq(2).css({"border-color": "red"});
					return;
				}

				$(".route-kwh").eq(2).css({"border-color": ""});
				$("#option-5-input .error").remove();

				vehicleConsumpForSpeed.setOverride(parseFloat(this.value));

				if (storagePossible) {
					localStorage.customMiKwh = this.value;
				}

				processRoute();
				
			});

			//update route when reverse selected

			$("#return-onoffswitch").change(function(){
				if (storagePossible) { localStorage.reverseOption = this.checked;}

				setTimeout(function(){
					if ($("#return-onoffswitch").prop("checked")) {
						returnJourney.getReturnRoute();
					} else {
						returnJourney.clearReturnRoute();
					}
				},350);					
			
			});

			//setup minimum value and capacity from local storage if possible.

			if (storagePossible && localStorage.kwhCapacity !== undefined ){
				$("#route-kwh-capacity").val(parseFloat(localStorage.kwhCapacity));
				
				
				chargeSuggestionController.setCapacity(parseFloat(localStorage.kwhCapacity)*1000);
				

			} else {
				chargeSuggestionController.setCapacity(21000);
			
				$("#route-kwh-capacity").val(21);
				
			}

			if (storagePossible && localStorage.kwhLowLimit !== undefined ){
				
				$("#route-kwh-low-limit").val(parseFloat(localStorage.kwhLowLimit));
				
			
				chargeSuggestionController.setLowLimit(parseFloat(localStorage.kwhLowLimit));

			} else {
				
				chargeSuggestionController.setLowLimit(20);
				
				$("#route-kwh-low-limit").val(20);
			}

			//setup start capacity from local storage if possible.

			if (storagePossible && localStorage.kwhStartCapacity !== undefined ){
				$("#route-kwh-start-capacity").val(parseFloat(localStorage.kwhStartCapacity));
				chargeSuggestionController.setStartTankLevel(parseFloat(localStorage.kwhStartCapacity));
			} else {
				chargeSuggestionController.setStartTankLevel(80);
				$("#route-kwh-start-capacity").val(80);
				
			}

			//setup status to include local storage if possible

			if (storagePossible && localStorage.routeStatusInclude) {
				var routeStatusInclude = JSON.parse(localStorage.routeStatusInclude);

				$(".route-status-include").each(function(index,element){
					if (routeStatusInclude.indexOf(this.value) > -1) {
						$(this).prop("checked",true);
					} 
				});

				chargeSuggestionController.setRouteStatusInclude(routeStatusInclude);

			} else {
				$(".route-status-include").each(function(index,element){
					if (this.value == "online" || this.value == "occupied") {
						$(this).prop("checked",true);
					} 
				});
				var routeStatusInclude = ["online","occupied"];
				chargeSuggestionController.setRouteStatusInclude(routeStatusInclude);
				localStorage.routeStatusInclude = JSON.stringify(routeStatusInclude);
			}

			//add charge suggestions menu after the options menu

			var $div = $("<div>",{id: "charge-suggest-menu"});
			$(".route-options-cont").after($div);

			$p = $("<div>",{id: "suggest-title", class: "route-titles"}).text("Charge Suggestions").append($("<div>",{class: "expand-h"})).append($("<div>",{class: "expand-v"}));
			$("#charge-suggest-menu").append($p);

			$tableCont = $("<div>",{class: "charge-suggest-cont"});
			$("#charge-suggest-menu").append($tableCont);

			//functions

			//on clicking the title, expand container and update expand cross.

			$("#suggest-title").click(function(e){
				if (e.target !== this){
					return;
				}
				$(".charge-suggest-cont").slideToggle();
				$(".route-options .expand-v").eq($(".route-titles").index(this)).fadeToggle();
			});

			//as we expand this automatically, expand it.

			$("#route-cont .expand-v").eq(2).fadeToggle();	

			//$(".myroute-title .expand-v").fadeToggle();
			//$("#table-cont").

		}
			
		//show markers within radius

		updateMarkersOnMapFromRoute.update();
		
		calcEnergyConsumption(legs);

		if ($("#charge-suggest-checkbox").prop("checked")){
			chargeSuggestionController.get();
		} else {
			chargeSuggestionController.clearResult();
		}

		setTimeout(function(){
			spinnerLoader.hide();
			if (!$(".clear-route").length){
				$buttonBar = $("<div>",{class:"w3-btn-bar w3-col s10 clear-route"});
				$buttonBar.append($("<span>",{class: "w3-btn w3-round w3-border w3-col s4 w3-border-white w3-green w3-hover-blue"}).text("Clear").click(function(){
					clearRoute();
				}));
				$buttonBar.append($("<span>",{class: "w3-btn w3-round w3-border w3-col s4 w3-border-white w3-green w3-hover-blue"}).text("Reverse").click(function(){
					switchRoute();
				}));
				$buttonBar.append($("<span>",{class: "w3-btn w3-round w3-border w3-col s4 w3-border-white w3-green w3-hover-blue"}).text("Add Stop").click(function(){
					addWaypointField();
				}));
				$buttonBarContainer = $("<div>",{class: "w3-row"}).append($("<div>",{class:"w3-col s1"}).append("&ensp;"))
																  			   .append($buttonBar)
																  			   .append($("<div>",{class:"w3-col s1"}).append("&ensp;"));
				
				$(".route-inputs-container").after($buttonBarContainer);
			}
		},500);


		
    };

	
}();

var mapIdleEvent = function(){
	var e;
	return {
		add: function(){
			e = google.maps.event.addListener(map, 'idle', function(){

				spinnerLoader.hide();

				if (!toggleWaypoints.isActive() && device == "computer"){
					setTimeout(toggleWaypoints.toggle,500);
				}

				mapIdleEvent.remove();
			});
		},
		remove: function(){
			google.maps.event.removeListener(e);
		}		
	};
}();

var updateMarkersOnMapFromRoute = function(){

	var routeMarkers;

	return {
		update: function(){

			routeMarkers = [];

			var radius = document.getElementById("charger-radius").value;

			if (storagePossible){
				localStorage.radius = radius;
			}

			var route_path = route.path;
			var routeProgress = 0;

			for (var i = 0; i < allmarkers.length; i++) {

				var d = 0;

				onroute = false;

				for (var x = 0; x < route_path.length && !onroute; x++) {
					if ((calcCordDistance(route_path[x].lat(),route_path[x].lng(),allmarkers[i].position.lat(),allmarkers[i].position.lng()) < (Number(radius)*1.621371) )) {
						onroute = true;

						for (var z = 0; z < x; z++) {
							d += calcCordDistance(route_path[z].lat(),route_path[z].lng(),route_path[z+1].lat(),route_path[z+1].lng());
						}

						allmarkers[i].markerRouteDistanceWindow.setDistanceIntoRoute((d/1.60934));
						
					} 
				}



				for (var y = 0; y < allmarkers[i].info.connectors.length; y++) { //do this once
					if (connectors.indexOf(allmarkers[i].info.connectors[y].type.id) >= 0) {

						if (onroute) {
							allmarkers[i].setVisible(true);
							allmarkers[i].setOpacity(1);

						} else {
							allmarkers[i].setVisible(false);
						}

						if (onroute){
							routeMarkers.push(allmarkers[i]);
							break;
						}
					}	
				}
			}
		},
		getRouteMarkers: function(){
			return routeMarkers;
		}
	}
}();

var returnJourney = function(){
	var tempWaypoints = [];
	var tempDestination = "";
	var returnActive = false;
	return {
		getReturnRoute: function(isCallback){
			//Assume route has been entered already so set the origin as the destination
			if (isCallback === undefined){
				tempDestination = $("#routeend").val();

				geocodeRequest(tempDestination,function(result, status){
					returnJourney.getReturnRoute(processGeocodeResponse(result,status));
				});
				
			} else {

				//we have received the geocoded destination
				//update destination
				$("#routeend").val($("#routestart").val());

				tempWaypoints = [];

				//get the current waypoints and store them
				route.waypoints.forEach(function(val,ind){
					if (!val.autoWaypoint){
						tempWaypoints.push(val);
					}
				});

				route.waypoints = [];

				tempWaypoints.forEach(function(val){
					route.waypoints.push(val);
				});

				//add our destination as a waypoint				

				processWaypoints({
					name: isCallback.title,
					lat: isCallback.location.lat,
					lng: isCallback.location.lng,
					autoWaypoint: true
				},true);

				//add return journey waypoints back in reverse

				for (var i = tempWaypoints.length - 1; i > -1; i--) {
					route.waypoints.push(tempWaypoints[i]);
				};

				//calculate route
				calcRoute(true);
			}

		},
		isActive: function(){
			return returnActive;
		},
		clearReturnRoute: function(){
			$("#routeend").val(tempDestination);
			route.waypoints = [];
			tempWaypoints.forEach(function(val){
				route.waypoints.push(val);
			});
			calcRoute();
		}
	};

}();

var chargeSuggestionController = function(){
	var direction_result;
	var currentRoute;
	var totalEnergy;
	var lowLimit;
	var stepData;
	var pathData_1;
	var pathData_2;
	var pathCons;
	var pathDist;
	var stepEff;
	var allRouteChargers;
	var allRouteChargers_stored;
	var chargeSuggestions;
	var chargerFound;
	var overviewPath;
	var bounceListener;
	var routeEnergyOverall;
	var routeStatusInclude = [];
	

	function addToTotal(energy){
		totalEnergy+= energy;
	}
	function resetTotalEnergy(){
		totalEnergy = 0;
	}
	function markerBounce(){
		for (var i = 0; i < chargeSuggestions.length; i++) {
			chargeSuggestions[i].setAnimation(google.maps.Animation.BOUNCE);
		}
		setTimeout(function(){
			stopBounce();
			google.maps.event.removeListener(bounceListener);
		}
		,1400);
	}
	
	function stopBounce(){
		for (var i = 0; i < chargeSuggestions.length; i++) {
			chargeSuggestions[i].setAnimation(null);
		}
	}
	var energyToNextCharger = function(){
		var energy;
		var distance;
		var chargerFound;
		var info;
		var routeChargersSorted;

		return function(startPos){
			energy = 0;
			distance = 0;
			chargerFound = false;
			for (var i = startPos; i < routeEnergyOverall.length && !chargerFound; i++) {
				energy += routeEnergyOverall[i].energy;
				distance += calcCordDistance(routeEnergyOverall[i].start.lat,routeEnergyOverall[i].start.lng,routeEnergyOverall[i].end.lat,routeEnergyOverall[i].end.lng) * 0.621371;
				for (var c = 0; c < allRouteChargers.length && !chargerFound; c++) {
					if (calcCordDistance(routeEnergyOverall[i].end.lat,routeEnergyOverall[i].end.lng,allRouteChargers[c].info.lat,allRouteChargers[c].info.lng) * 0.621371 < 1) {
						chargerFound = true;
						info = allRouteChargers[c];
					}
				};
			};

			if (chargerFound) {
				return {
					type: "charger",
					energy: energy,
					charger: info,
					distance: distance,
					location: {
						lat: info.info.lat,
						lng: info.info.lng
					}
				};
			} else {
				return {
					type: "end",
					energy: energy,
					location: {
						lat: routeEnergyOverall[i].end.lat,
						lng: routeEnergyOverall[i].end.lng,
					}
				};
			}
		};

	}();
	var energyTank = function(){
		var tank;
		var energyTrip;
		var capacity;

		return {
			getEnergyLevel: function(){
				return tank;
			},
			useEnergy: function(wh){
				tank-= wh;
				energyTrip += wh;
			},
			addEnergy: function(wh){
				tank+= wh;
			},
			setCapacity: function(wh){
				capacity = wh;
				localStorage.kwhCapacity = wh/1000;
				$("#route-kwh-capacity").val(Number(wh/1000).toFixed(1));
			},
			getCapacity: function(){
				return capacity;
			},
			fillToLevel: function(wh){
				tank = wh;
			},
			fillToPercentage: function(percentage){
				tank = energyTank.getCapacity()*(percentage/100);
			},
			fillUp: function(){
				tank = capacity;
			},
			fillToEightyPercent: function(){
				tank = capacity * 0.8;
			},
			getEnergyTrip: function(){
				return energyTrip;
			},
			resetEnergyTrip: function(){
				energyTrip = 0;
			},
			isFull: function(){
				return tank >= capacity;
			}
		};
	}();

	function addSuggestion(obj){

		var eClass = "charge-suggest-info";

		if (obj.suggestion.charger.info.connectors[0].status === "pseudo"){
			var name = obj.suggestion.charger.info.name;
		} else {
			var name = obj.suggestion.charger.info.name.split(",")[0];
		}

		if (obj.suggestion.percentageOnArrival.toFixed(0)  < Number($("#route-kwh-low-limit").val())) {
			eClass += " transition-power-exceeded";
		}

		if (obj.suggestion.percentageOnArrival && obj.suggestion.percentageOnDeparture &&
			obj.suggestion.percentageOnArrival !== obj.suggestion.percentageOnDeparture) {
			var a = obj.suggestion.percentageOnArrival;
			a = a.toFixed(0)+"%";
			var d = obj.suggestion.percentageOnDeparture;
			d = d.toFixed(0)+"%";
			var text = 'Arr. ' + a + ' - Dep. ' + d;
		} else if (obj.suggestion.percentageOnArrival && obj.suggestion.percentageOnDeparture === undefined ) {
			var a = obj.suggestion.percentageOnArrival;
			a = a.toFixed(0)+"%";
			var text = 'Arr. ' + a;
		} else {
			var d = obj.suggestion.percentageOnDeparture;
			d = d.toFixed(0)+"%";
			var text = 'Dep. ' + d;
		}

		var referenceConnectorId = connectors[0];

		if (obj.suggestion.charger.info.connectors[0].status === "pseudo") {
			var suggestionColor = "w3-pale-green";
			var borderColor = "w3-border-green";
		} else {
			for (var i = 0; i < obj.suggestion.charger.info.connectors.length; i++) {
				if (obj.suggestion.charger.info.connectors[i].type.id == referenceConnectorId) {
					var referenceConnector = i;
					break;
				}
				
			};

			var referenceStatus = obj.suggestion.charger.info.connectors[referenceConnector].status;

			var suggestionColor = "w3-pale-green";
			var borderColor = "w3-border-green";

			if (referenceStatus == "offline") {
				suggestionColor = "w3-pale-red";
				borderColor = "w3-border-red";
			}

			if (referenceStatus == "unknown") {
				suggestionColor = "w3-light-grey";
				borderColor = "w3-border-grey";
			}
		}

		$row = $("<div>",{class: "charge-suggest-row w3-container w3-leftbar "+suggestionColor+" "+borderColor})
		$row.append($("<div>",{class: "charge-suggest-title"}).text(name))
			.append($("<div>",{class: eClass}).append($("<img>",{width: "16px",height: "16px",src: "img/plug.svg"})).append(text));										   														 
													   												    
		
		$(".charge-suggest-cont").append($row);
	}
	function addTransition(suggest){
		//$img = $("<img>").prop("src","img/three-dots.png")
		 				 //.prop("width","15");

		$img = $("<i>",{class: "material-icons"}).append("more_vert");

		if (arguments.length){
			var e = suggest.energy;
			e = e.toFixed(0)+"%";
			var d = suggest.distance;
			d = d.toFixed(1)+"mi";
			var eClass = "transition-power";

			if (suggest.energy > energyTank.getCapacity() - lowLimit){
				eClass += " transition-power-exceeded";
			}

			$row = $("<div>",{class: "charge-suggest-transition"}).append([$("<div>",{class: eClass}).text(d).css({width: "42%"}),$("<div>",{class: "charge-transition-icon"}).append($img),$("<div>",{class: eClass}).text(e).css({width: "42%"})]);
		} else {
			$row = $("<div>",{class: "charge-suggest-transition"}).append([$("<div>").css({width: "42%"}),$("<div>",{class: "charge-transition-icon"}).append($img),$("<div>").css({width: "42%"})]);
		}

		$(".charge-suggest-cont").append($row);
	}
	function startAddress(){
		var route = directionsDisplay.getDirections().routes[0];
		var start = route.legs[0].start_address;
		return $("#routestart").val();
	}
	function endAddress(){
		var route = directionsDisplay.getDirections().routes[0];
		var end = route.legs[route.legs.length-1].end_address;
		return $("#routeend").val();
	}

	return {
		get: function(){
			resetTotalEnergy(); 

			var waypointData = {
				start: {
					lat: 1,
					lng: 1
				},
				waypoints: [],
				end: {
					lat: 1,
					lng: 1
				}
			};


			energyTank.fillToPercentage(Number($("#route-kwh-start-capacity").val()));

			var c = updateMarkersOnMapFromRoute.getRouteMarkers();

			allRouteChargers = [];
			allRouteChargers_stored = [];
 			chargeSuggestions = [];
			routeChargersSorted = [];

			direction_result = directionsDisplay.getDirections();

			//for current route
			currentRoute = direction_result.routes[curRoute];
			overviewPath = currentRoute.overview_path;

			for (var i = 0; i < c.length; i++) {
				allRouteChargers.push(c[i]);
				allRouteChargers_stored.push(c[i]);
				
			};

			for (var i = 0; i < allRouteChargers.length; i++) {
				allRouteChargers[i].setOpacity(0.4);
			};

			//Are we receiving a response to the elevation response?
			if (arguments[0] !== undefined){
				//YES
				var elevationResult = arguments[0];
				var elevationStatus = arguments[1];
				processElevationData(arguments[0],arguments[1]);

			} else {
				//NO

				//get elevation data for this route and build request 
				var elReq = {
					path: overviewPath,
					samples: 500
				};

				//make the request and pass this function as callback to return later
				elevation.getElevationAlongPath(elReq,chargeSuggestionController.get);

				return;
			}

			//It should be healthy at this point - TODO what if it's not?

			if (elevationStatus === google.maps.ElevationStatus.OK){

				//find out ratio of total route for each step step
				var allSteps = [];

				//total distance
				var routeDistance = 0;

				//get total route distance
				currentRoute.legs.forEach(function(leg){
					routeDistance += leg.distance.value;
				});

				//create array of all steps and their ratio to route

				currentRoute.legs.forEach(function(leg){
					leg.steps.forEach(function(step){
						var stepRatioOfRoute = step.distance.value / routeDistance;
						allSteps.push({
								step: step,
								ratio: stepRatioOfRoute
						});	
					});
				});

				//determine how many elevation responses we have and what they count toward the main route in ratio terms
				
				//ratio
				var singleElevationResultRatioOfRoute = 1 / elevationResult.length;

				//for each result, add the energy effect. Starting at 1, so 0-1 energy stored at 1.

				elevationResult.forEach(function(result,index){

					var elChange = 0;
					var ascent = 0;
					var descent = 0;
					var ascentCon = 0;
					var decentCon = 0;

					if (index > 0) {
						elChange = result.elevation - elevationResult[index-1].elevation;

						if (elChange > 0){
							ascent += elChange;
						} else {
							descent += Math.abs(elChange);
						}

						ascentCon = (ascent / 300) * 1500;
						descentCon = (descent / 300) * 750;

						result.energy = ascentCon - descentCon;
					} else {
						result.energy = 0;
					}
				});

				//split the elevation data amongst steps
				allSteps.forEach(function(thisStep){
					var thisStepElevRatio = 0;
					thisStep.elevData = [];
					while (thisStepElevRatio + singleElevationResultRatioOfRoute <= thisStep.ratio) {
						thisStep.elevData.push(elevationResult[0]);
						elevationResult.splice(0,1);
						thisStepElevRatio += singleElevationResultRatioOfRoute;
					}
				});						

			}



			//generate an array of overall route path with energy used for each step/path

			routeEnergyOverall = [];
			var elevationPath = [];

			var dist = 0;
			var stepNo = 0;

			for (var a = 0; a < currentRoute.legs.length; a++){
				for (var b = 0; b < currentRoute.legs[a].steps.length; b++){
					stepEnergy = calcStepEnergyUsage(currentRoute.legs[a].steps[b]);
				
					//Include elevation data if all is okay

					var stepElevEnergy = 0;

					if (elevationStatus === google.maps.ElevationStatus.OK){

						allSteps[stepNo].elevData.forEach(function(step){
							stepElevEnergy += step.energy;
						});

					}

					for (var c = 1; c < currentRoute.legs[a].steps[b].path.length; c++) {

						pathEnergy = (stepEnergy / currentRoute.legs[a].steps[b].path.length);
						pathElevEnergy = (stepElevEnergy / currentRoute.legs[a].steps[b].path.length)

						elevationPath.push({
							lat: currentRoute.legs[a].steps[b].path[c].lat(),
							lng: currentRoute.legs[a].steps[b].path[c].lng()
						});


						var ambTemp = vehicleDataController.get().temperature;

						if (ambTemp <=20){
							var pathEnergyTempAdjust = pathEnergy * (1 + (((20 - ambTemp) / 2) * 0.01));
						} else {
							var pathEnergyTempAdjust = pathEnergy * (1 - (((20 - ambTemp) / 4) * 0.01));
						}

						dist = calcCordDistance(currentRoute.legs[a].steps[b].path[c-1].lat(),currentRoute.legs[a].steps[b].path[c-1].lng(),currentRoute.legs[a].steps[b].path[c].lat(),currentRoute.legs[a].steps[b].path[c].lng())*0.621371;

						routeEnergyOverall.push({
							energy: pathEnergyTempAdjust + pathElevEnergy,
							start: {
								lat: currentRoute.legs[a].steps[b].path[c-1].lat(),
								lng: currentRoute.legs[a].steps[b].path[c-1].lng()
							},
							end: {
								lat: currentRoute.legs[a].steps[b].path[c].lat(),
								lng: currentRoute.legs[a].steps[b].path[c].lng()	
							},
							distance: dist,
							startStep: {
								lat:currentRoute.legs[a].steps[b].start_location.lat(),
								lng:currentRoute.legs[a].steps[b].start_location.lng()
							},
							endStep: {
								lat:currentRoute.legs[a].steps[b].end_location.lat(),
								lng:currentRoute.legs[a].steps[b].end_location.lng()
							}
						});	


						if (a == 0 && b == 0 && c == 1) {

							allRouteChargers.push({
								info:{
									name: "Start",
									lat: currentRoute.legs[a].steps[b].path[c].lat(),
									lng: currentRoute.legs[a].steps[b].path[c].lng(),
									connectors: [{status:"pseudo"}]
								}
							});

						}

						if (a == currentRoute.legs.length-1 && b == currentRoute.legs[a].steps.length-1 && c == currentRoute.legs[a].steps[b].path.length-1) {
							//add start and end to allRouteChargers to allow calculations later on
							allRouteChargers.push({
								info:{
									name: "End",
									lat: currentRoute.legs[a].steps[b].path[c].lat(),
									lng: currentRoute.legs[a].steps[b].path[c].lng(),
									connectors: [{status:"pseudo"}]
								}
							});
						}

					};

					stepNo += 1;
				};
			};

			waypointData.start.lat = routeEnergyOverall[0].start.lat.toFixed(5);
			waypointData.start.lng = routeEnergyOverall[0].start.lng.toFixed(5);
			waypointData.end.lat = routeEnergyOverall[routeEnergyOverall.length-1].end.lat.toFixed(5);
			waypointData.end.lng = routeEnergyOverall[routeEnergyOverall.length-1].end.lng.toFixed(5);

			
			

			//Order all the route chargers along route (including multiples)

			var energy = 0;
			var dist = 0;
			var conns = [];
			var ongoingDist = 0;
			var lastDistanceFromHere, lastClosestCharger;

			allRouteChargers.forEach(function(charger,index){
				charger.lastRouteBearing = 0;
			});
			
			for (var i = 0; i < routeEnergyOverall.length; i++) {
				energy += routeEnergyOverall[i].energy;
				dist += routeEnergyOverall[i].distance;
				ongoingDist += routeEnergyOverall[i].distance;

				//find the closest charger to where we currently are

				var distanceFromHere = 9999;
				var closestCharger;
				var tempDistance = 0;
				
				allRouteChargers.forEach(function(charger,index){
					tempDistance = calcCordDistance(routeEnergyOverall[i].start.lat,routeEnergyOverall[i].start.lng,charger.info.lat,charger.info.lng);
					if (tempDistance < distanceFromHere) {
						if (routeChargersSorted.length > 0){
							if (routeChargersSorted[routeChargersSorted.length -1].charger.info.name !== charger.info.name){
								closestCharger = charger;
								distanceFromHere = tempDistance;
							}
						} else {
							closestCharger = charger;
							distanceFromHere = tempDistance;
						}
					}					
				});	

				//we now know which is the closest charger
				
				if (distanceFromHere > $("#charger-radius").val()*1.621371) {
					//Too far away so move along route
					continue;
				}
			

				//closer next time and no others closest?
				if (i < routeEnergyOverall.length - 1) {

					//check whether the closest charger is still this one next time
					var closestChargerNext;
					nextDistanceFromHere = 9999;
					tempDistance = 0;

					//find closest charger to next step
					allRouteChargers.forEach(function(charger,index){
						tempDistance = calcCordDistance(routeEnergyOverall[i+1].start.lat,routeEnergyOverall[i+1].start.lng,charger.info.lat,charger.info.lng);
						if (tempDistance < nextDistanceFromHere) {
							nextDistanceFromHere = tempDistance;
							closestChargerNext = charger;
						}					
					});
					
					var nextDistance = calcCordDistance(routeEnergyOverall[i+1].start.lat,routeEnergyOverall[i+1].start.lng,closestCharger.info.lat,closestCharger.info.lng);
					
					if (nextDistance < distanceFromHere && closestCharger.info.name === closestChargerNext.info.name) {
						continue;
					}	
				}

				// //closer last time?
				// if (i > 0) {
				// 	var lastDistance = calcCordDistance(routeEnergyOverall[i-1].start.lat,routeEnergyOverall[i-1].start.lng,closestCharger.info.lat,closestCharger.info.lng);
				// 	if (lastDistance < distanceFromHere ) {
				// 		continue;
				// 	}	
				// }
						
				//do we have another charger near by, and therefore need to check the side of road?
				var closestChargers = [];
				
				allRouteChargers.forEach(function(charger,index){
					tempDistance = calcCordDistance(closestCharger.info.lat,closestCharger.info.lng,charger.info.lat,charger.info.lng);
					if (tempDistance < 3 && tempDistance > 0) {
						closestChargers.push(charger);
					}					
				});	

				


				//we only have one other charger within 1.5km - enough to say motorway?

				if (closestChargers.length == 1 && closestCharger.info.name !== "End" && closestCharger.info.name !== "Start") {
					//is this charger on the correct side of the road
					var routeBearing = calcBearing(routeEnergyOverall[i].start.lat,routeEnergyOverall[i].start.lng,routeEnergyOverall[i].end.lat,routeEnergyOverall[i].end.lng);
					var bearingToCharger = calcBearing(routeEnergyOverall[i].start.lat,routeEnergyOverall[i].start.lng,closestCharger.info.lat,closestCharger.info.lng);
					var lowBearingLimit = bearing.minusDegrees(routeBearing,180);

					//Was our heading last we checked bearing similar to now?
					if (closestCharger.lastRouteBearing !== 0 && bearing.smallestDelta(routeBearing,closestCharger.lastRouteBearing) < 90) {
						continue;
					}

					if (bearing.antiClockwiseDegrees(routeBearing,bearingToCharger) > 180) {
						closestCharger.lastRouteBearing = routeBearing;
						continue;
					}

					// if ((bearingToCharger > routeBearing) || (bearingToCharger < lowBearingLimit)) {
					// 	closestCharger.lastRouteBearing = routeBearing;
					// 	continue;
					// }
				}				
				
				//Have we just added this charger?
				if (routeChargersSorted.length > 0){

					//When was this last added?
					var lastDistance = 0;
					routeChargersSorted.forEach(function(charger,index){
						if (charger.charger.info.name === closestCharger.info.name && lastDistance === 0) {
							lastDistance = charger.distIntoRoute;
						}
					});

				 	if (ongoingDist - lastDistance < 5){

				 		continue;
				 	}
				}				

				// Have we looped back around and found the start which we've already added?
				if (routeChargersSorted.length > 0){
				 	if (closestCharger.info.name === "Start"){
				 		continue;
				 	}
				}

				// Have we found the end on our first shot?
				if (routeChargersSorted.length === 0){
				 	if (closestCharger.info.name === "End"){
				 		continue;
				 	}
				}

				//Is the closest charger actually the end point? If so, wait until we're right next to it.
				if (routeChargersSorted.length > 0){
				 	if (closestCharger.info.name === "End" && i !== routeEnergyOverall.length - 1){
				 		continue;
				 	}
				}


				//Create array of this charger's connectors to find online statuses

				conns = [];

				closestCharger.info.connectors.forEach(function(con){
					if (con.status !== "pseudo"){
						conns.push(con.type.id);
					}
				});

				var conNo = -1;

				//find the location of the first valid connector 

				connectors.forEach(function(value){
					if (conns.indexOf(value) > -1 && conNo < 0)  {
						conNo = conns.indexOf(value);
					}
				});

				

				//Check if the chosen connector is online
				if (closestCharger.info.connectors[0].status === "pseudo" || routeStatusInclude.indexOf(closestCharger.info.connectors[conNo].status) > -1){

					// var info = new google.maps.InfoWindow({
					// 	position: {lat: routeEnergyOverall[i].start.lat, lng: routeEnergyOverall[i].start.lng},
					// 	content: closestCharger.info.name+"<br>"+routeBearing+"<br>"+bearingToCharger,
					// 	map: map
					// });

					//Yes! So add it to the list of sorted chargers
					routeChargersSorted.push({
						charger: closestCharger,
						energy: energy,
						distance: dist,
						distIntoRoute: ongoingDist
					});

					if (closestCharger.info.connectors[0].status !== "pseudo") {
						waypointData.waypoints.push({
							lat: closestCharger.info.lat.toFixed(5),
							lng: closestCharger.info.lng.toFixed(5),
							startStep: {
								lat: routeEnergyOverall[i].startStep.lat.toFixed(5),
								lng: routeEnergyOverall[i].startStep.lat.toFixed(5)
							},
							endStep: {
								lat: routeEnergyOverall[i].endStep.lat.toFixed(5),
								lng: routeEnergyOverall[i].endStep.lat.toFixed(5)
							}
						});
					}


					//clear counters

					energy = 0;
					dist = 0;								
					
				}

				//Not online, so continue.
				continue;	
				
			}

			// submitJSONToServer("php/waypoints.php",JSON.stringify(waypointData),function(response){
			// 	alert("Hello");
			// });

			
			//now find charge suggestions

			var energyNeeded = 0;
			
			var energy = 0;
			var dist = 0;
			var nextStop = 0;

			//need last leg in sorted list to calc
			for (var i = 0; i < routeChargersSorted.length; i++) {

				//total energy & distance so far
				energy += routeChargersSorted[i].energy;
				dist += routeChargersSorted[i].distance;

				//Consume energy required to get to this stop, we would have charged earlier if we needed to
				if (i > 0) {
					var toGetHere = routeChargersSorted[i].energy;
					energyTank.useEnergy(toGetHere);
				}
				
				//while we're not the first OR last stop (as we can't charge at and and define start charge.
				if (i < routeChargersSorted.length-1 && i == nextStop){
					
					//energy we got here with
					var weArrivedWith = energyTank.getEnergyLevel();

					routeChargersSorted[i].percentageOnArrival = (energyTank.getEnergyLevel()/energyTank.getCapacity()) * 100;

					//How many stops do can we get through whilst charge is less than max?
					
					energyNeeded = 0;

					var oldNextStop = nextStop;

					for (var z = 0; z < routeChargersSorted.length; z++) {

						if (z > i) { //for chargers ahead of us, if we can add that energy to the tank with spare do so
							if ( i > 0 && ((energyNeeded + routeChargersSorted[z].energy + lowLimit) <= (energyTank.getCapacity()*0.9))  ||
							     i == 0 && ((energyNeeded + routeChargersSorted[z].energy + lowLimit) <= energyTank.getEnergyLevel()) ) {
								energyNeeded += routeChargersSorted[z].energy;
								nextStop = z;
							} else {
								if (oldNextStop == nextStop) {
									nextStop = i + 1;
									energyNeeded = (energyTank.getCapacity() * 0.9) - lowLimit;
								}
								break;
							}
						}
					};
			
					var currentEnergy = energyTank.getEnergyLevel();

					if (i > 0) {
						energyTank.fillToLevel(energyNeeded+lowLimit);	
					}					

					//Update the level we've charged to
					routeChargersSorted[i].percentageOnDeparture = (energyTank.getEnergyLevel()/energyTank.getCapacity()) * 100;

					//add this stop to the chargers array
					chargeSuggestions.push({
						energy: (energy / energyTank.getCapacity()) * 100,
						distance: dist,
						suggestion: routeChargersSorted[i]
					});

					energy = 0;
					dist = 0;
					
				}					

				

				//add the final charger as a suggestion so we can render it with distance and energy
				if (i == (routeChargersSorted.length-1)){

					routeChargersSorted[i].percentageOnArrival = (energyTank.getEnergyLevel()/energyTank.getCapacity()) * 100;

					//add this stop to the chargers array
					chargeSuggestions.push({
						energy: (energy / energyTank.getCapacity()) * 100,
						distance: dist,
						suggestion: routeChargersSorted[i]
					});
				}
			}

			//We're done with start and end markrs for chargers so update with names

			chargeSuggestions[0].suggestion.charger.info.name = startAddress();
			chargeSuggestions[chargeSuggestions.length-1].suggestion.charger.info.name = endAddress();

			//set opacity on suggestions (except first and last as these should be start and end points and get comments
			var commentRequest = [];
			for (var i = 1; i < chargeSuggestions.length - 1; i++) {

				chargeSuggestions[i].suggestion.charger.setOpacity(1);

				commentRequest.push({
					operator_id: chargeSuggestions[i].suggestion.charger.info.provider_openid,
					lat: chargeSuggestions[i].suggestion.charger.info.lat,
					lng: chargeSuggestions[i].suggestion.charger.info.lng
				});

			};

			ajaxHandler({
				url: "php/OCM_comments.php",
				data: commentRequest,
				success: chargeSuggestionController.processComments
			});
		
    		//submitJSONToServer("php/OCM_comments.php",JSON.stringify(commentRequest),chargeSuggestionController.processComments);

			// bounceListener = google.maps.event.addListener(map, 'idle', function(){
			// 	setTimeout(function(){
			// 		chargeSuggestion.bounceMarkers();
			// 	},
			// 	500);
			// });

			chargeSuggestionController.renderResult();

		},
		setCapacity: function(wh){
			energyTank.setCapacity(wh);
		},
		setStartTankLevel: function(percentage){
			energyTank.fillToPercentage(percentage);
		},
		setLowLimit: function(limit){
			lowLimit = (energyTank.getCapacity()/100) * limit;
		},
		bounceMarkers: function(){
			markerBounce();
		},
		renderResult: function(){
			this.clearResult();

			// var $div = $("<div>",{id: "charge-suggest-menu"});
			// $(".route-options-cont").after($div);

			// $p = $("<p>",{id: "suggest-title"}).text("Charge Suggestions");
			// $("#charge-suggest-menu").append($p);

			// $tableCont = $("<div>",{class: "charge-suggest-cont"}).css({display: "none",width:"100%"});
			// $("#charge-suggest-menu").append($tableCont);


			// $("#suggest-title").click(function(e){
			// 	if (e.target !== this){
			// 		return;
			// 	}
			// 	$(".charge-suggest-cont").slideToggle();
			// });
			

			for (var i = 0 ; i < chargeSuggestions.length; i++) {
				addSuggestion(chargeSuggestions[i]);
				
				if (i < chargeSuggestions.length - 1){
					addTransition(chargeSuggestions[i+1]);
				}		
			};

			$(".charge-suggest-cont").append($("<div>",{class: "suggest-caution"}).text("Caution, this is currently in Beta. Check Plugshare before travel."));

			$(".charge-suggest-title").click(function(){
				var suggestNo = $.inArray(this,$(".charge-suggest-title"));
				if (suggestNo > 0 && suggestNo < $(".charge-suggest-title").length - 1){
					
					// map.panTo({
					// 	lat: chargeSuggestions[suggestNo].suggestion.charger.info.lat,
					// 	lng: chargeSuggestions[suggestNo].suggestion.charger.info.lng,
					// });
					chargeSuggestions[suggestNo].suggestion.charger.showInfoWindow(true);
					// alert(chargeSuggestion.getChargingTime(
					// 	energyTank.getCapacity(),
					// 	chargeSuggestions[suggestNo].suggestion.percentageOnArrival,
					// 	chargeSuggestions[suggestNo].suggestion.percentageOnDeparture,
					// 	50
					// ));
				}
				
			});
			


		},
		clearResult: function(){
			$(".charge-suggest-cont").children().remove();
		},
		resetOpacity: function(){
			if (allRouteChargers_stored.length){
				for (var i = 0; i < allRouteChargers_stored.length; i++) {
					allRouteChargers_stored[i].setOpacity(1);	
				};
			}
		}, 
		processComments: function(response){
			var commentRequest = response;

			$(".charge-suggest-title").after(function(index){
				var now = new Date();
				if (index > 0 && index < $(".charge-suggest-title").length - 1){

					var $userData = $("<div>",{class: "charge-suggest-userdata"});
					var $commCont = $("<div>",{class: "charge-suggest-comment-container"});

					//if there aren't any comments
					if (commentRequest[index - 1].comments === null){
						$userData.append($("<div>",{class: "comments-title"})
									.append($("<div>",{class: "expand-h-small no-comments"}))
									.append($("<div>",{class: "expand-v-small no-comments"}))
									.append($("<i>",{class: "material-icons comment-icon"}).append("warning"))
							 	 	.append($("<span>").append("No user comments"))
							 	 	.append($commCont)
							 	 );

					} else {

						$($commCont).append($("<span>").append("Comments powered by <a target='_blank' href='http://openchargemap.org/site/poi/details/" + commentRequest[index - 1].comments[0].ChargePointID +"'>OpenChargeMap</a>"));

						var lastCommentDate;
						var lastCheckinType;

						for (var i = 0; i < commentRequest[index - 1].comments.length; i++) {
							
							var aClass = "bubble charge-suggest-comment" + commentType(commentRequest[index - 1].comments[i].CheckinStatusTypeID);
							var date = new Date(commentRequest[index - 1].comments[i].DateCreated);
							if (!i){
								lastCommentDate = date;
								lastCheckinType = commentType(commentRequest[index - 1].comments[i].CheckinStatusTypeID);
							}
							var date = date.getDate() + " " + getActualMonth(date.getMonth()) + " " + date.getFullYear().toString().substring(2,4) + ":";

							var commentTitle = "";
							var userComment = commentRequest[index - 1].comments[i].Comment;

							if (commentRequest[index - 1].comments[i].CheckinStatusType === null) {
								commentTitle = commentRequest[index - 1].comments[i].CommentType.Title;
							} else {
								commentTitle = commentRequest[index - 1].comments[i].CheckinStatusType.Title;
							}

							if (commentTitle.indexOf("(") > 0){
								commentTitle = commentTitle.substring(0,commentTitle.indexOf("(")-1);
							}

							if (userComment) {

								var userName = commentRequest[index - 1].comments[i].UserName;

								if (userName) {
									userName = " - " + userName;
								} else {
									userName = "";
								}
							
								$commCont.append(
									$("<div>",{class: aClass}).append( $("<div>",{class: "comment-date"}).append(date))
															  .append( $("<div>",{class: "comment-text"}).append(commentTitle))
							 								  .append( $("<div>",{class: "user-comment collapsed-comment"}).append( $("<div>",{class: "quote-cont"}).append($("<i>",{class: "fa fa-quote-left"}))  )
							 								  															   .append($("<span>").append(userComment))
							 								  															   .append( $("<div>",{class: "quote-cont"}).append($("<i>",{class: "fa fa-quote-right"})) )
							 								  															   .append($("<span>").append(userName)) 
							 								  )				
						 		);
							} else {
								$commCont.append(
									$("<div>",{class: aClass}).append( $("<div>",{class: "comment-date"}).append(date))
															  .append( $("<div>",{class: "comment-text"}).append(commentTitle))
															  .append( $("<div>",{class: "user-comment collapsed-comment"}).append($("<span>").append('No comment left..'))
															  															   .append($("<span>").append(userName)) )
						 		);
							}

							
						};

						var daysSinceLastCheckin = daysBetweenDates(lastCommentDate,now);

						if (daysSinceLastCheckin > 7) {
							if (lastCheckinType == " comment-positive"){
								var imgSrc = "error_outline";
								var cmtTitle = "Last successful checkin " + daysSinceLastCheckin + " days ago";
							} else if (lastCheckinType == " comment-negative") {
								var imgSrc = "warning";
								var cmtTitle = "Unsuccessful checkin " + daysSinceLastCheckin + " days ago";
							} else {
								var imgSrc = "error_outline";
								var cmtTitle = "Neutral checkin " + daysSinceLastCheckin + " days ago";
							}

						} else {
							if (lastCheckinType == " comment-positive"){
								var imgSrc = "check";
								var cmtTitle = "Successful checkin " + daysSinceLastCheckin + " days ago";
							} else if (lastCheckinType == " comment-negative") {
								var imgSrc = "warning";
								var cmtTitle = "Unsuccessful checkin " + daysSinceLastCheckin + " days ago";
							} else {
								var imgSrc = "error_outline";
								var cmtTitle = "General checkin " + daysSinceLastCheckin + " days ago";
							}
						}
						//Add to the userdata-header first!
						$userData.append($("<div>",{class: "comments-title"})
								 	 .append($("<i>",{class: "material-icons comment-icon"}).append(imgSrc))
								 	 .append($("<span>").append(cmtTitle))
									 .append($("<div>",{class: "expand-h-small"}))
									 .append($("<div>",{class: "expand-v-small"}))
									 .click(function(e){
								 	 	$(".charge-suggest-comment-container").eq($(".comments-title").index(e.delegateTarget)).slideToggle();
								 	 	$(".comments-title > .expand-v-small").eq($(".comments-title").index(e.delegateTarget)).fadeToggle();
								 	 })
								 );

						$userData.append($commCont);

					}					

					return $userData;
				}

			});
		},
		getChargingTime: function(){

			var capacity = 0;
			var chargeTime = 0;
			var targetCap = 0;
			var powerInc = 0;

			function getChargingSpeed(p){
				if (p <= 0.75) {
					return 1;
				} else if (p <= 0.80) {
					return 0.8;
				} else if (p <= 0.85) {
					return 0.65;
				} else if (p <= 0.90) {
					return 0.35;
				} else if (p <= 0.95) {
					return 0.15;
				} else if (p <= 1.00) {
					return 0.05;
				} else {
					return 0.01;
				}
			}

			return function(capTotal, capStart, capEnd, chargeSpeed) {
				capacity = Number(capStart.toFixed(0))/100;
				targetCap = Number(capEnd.toFixed(0))/100;
				chargeTime = 0;
				powerInc = 0.01 * capTotal; //eg 0.01 of 20kW = 200w.

				while (capacity < targetCap) {
					capacity += 0.01;
					chargeTime+= (3600 / ((getChargingSpeed(capacity) * (chargeSpeed * 1000)) / powerInc));
				}

				return chargeTime / 60;

			};

		}(),
		setRouteStatusInclude: function(statuses){
			routeStatusInclude = statuses;
		}

	};

}();


function daysBetweenDates(firstDate,secondDate){
	var oneDay = 24*60*60*1000; 
	return diffDays = Math.round(Math.abs((firstDate.getTime() - secondDate.getTime())/(oneDay)));
}

function commentType(id){
	if (id == 10 || id == 15 || id == 210){
		return " comment-positive";
	} else if ( id == 120 || id == 130 || id == 160 || id == 30 || id == 22 || id == 20 || id == 25 || id == 50 || id == 40 || id == 200){
		return " comment-negative";
	} else {
		return " comment-neutral";
	}
}

function calcEnergyConsumption(legs){
	var routeCon = 0;
	var stepDist = 0;
	var stepSpeed = 0;
	var stepCon = 0;
	var legCon = 0;

	for (var l = 0; l < legs.length; l++) {
		legCon = 0;
		for (var s = 0; s < legs[l].steps.length; s++) {
			stepDist = (legs[l].steps[s].distance.value / 1000) * 0.621371;
			stepSpeed = stepDist / (legs[l].steps[s].duration.value / (60*60));
			reqSpeed = stepSpeed * 1.00;
			stepCon = (vehicleConsumpForSpeed.get(reqSpeed)/1000) * stepDist;
			legCon += stepCon;
		};

		routeCon += legCon;
		route.consumption.waypoints[l] = {baseline:legCon,adjusted:0};
	};

	route.consumption.total.baseline = routeCon;
}

var calcStepEnergyEfficiency = function(step){
	var stepDist = 0;
	var stepSpeed = 0;
	var stepEff = 0;

	return function(step){
		stepDist = (step.distance.value / 1000) * 0.621371;
		stepSpeed = stepDist / (step.duration.value / (60*60));
		reqSpeed = stepSpeed * 1.00;
		stepEff = vehicleConsumpForSpeed.get(reqSpeed);
		return stepEff;
	};
}();

var calcStepEnergyUsage = function(step){
	var stepDist = 0;
	var stepSpeed = 0;
	var stepEff = 0;

	return function(step){
		stepDist = (step.distance.value / 1000) * 0.621371;
		stepSpeed = stepDist / (step.duration.value / (60*60));
		reqSpeed = stepSpeed * 1.00;
		stepEff = vehicleConsumpForSpeed.get(reqSpeed);
		return stepEff * stepDist;
	};
}();

function getTimeFromSec(sec){
	var hour = Math.ceil(sec/(60*60)) - 1;

	var mins = Math.ceil((sec / 60) - (hour * 60));

	if (hour > 0){
		if (mins > 0){
			return hour + " hours " + mins + " mins";
		} else {
			return hour + " hours ";
		}
		
	} else {
		return mins + " mins";
	}
}

function getMilesFromKm(km){
	var distance = ((km / 1000) * 0.621371) ;
	distance = Math.ceil(distance);
	return distance;
}

function insertAfter(newNode, referenceNode) {
    referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
}
function clearRoute() {
	directionsDisplay.setMap(null);

	chargeSuggestionController.clearResult();

	if (chart !== undefined){
		chart.clearChart();
	}
	
	route.clear();
	$(".directions-display").html("");
	
	document.getElementById("routestart").value = "";
	document.getElementById("routeend").value = "";
	
	showMarkers();

	$(".route-waypoint-cont").remove();
}

//
// Map marker functions
//

function addMarker(location,direction) {

	var url;
	var offset;
	var shape;
	var zindex;
	var obstat = "";
	var pinDirection = "";
	var markertype = "";

	if (direction == "left") {
		pinDirection = "l";
		shape = {
					coords: [5,2,9,1,13,4,14,9,13,13,10,14,5,13,2,11,1,6,3,2,5,2],
					type: 'poly'
		};

		offset = -9;

		if (location.connectors[0].status == "offline") {
			url = "img/pl_64x64.png";
		} else if (location.connectors[0].status == "planned" || location.connectors[0].status == "unknown" || location.connectors[0].status == "occupied" || location.connectors[0].status == "in session") {
  			url = "img/bl_64x64.png";
  		} else {
  			url = "img/gl_64x64.png";
		}

	} else if (direction == "mid") {
		pinDirection = "m";
		shape = {
					coords: [16,0,21,2,22,8,20,13,14,12,10,8,11,2,16,0],
					type: 'poly'
		};

		offset = 0;

		if (location.connectors[0].status == "offline") {
			url = "img/pm_64x64.png";
		} else if (location.connectors[0].status == "planned" || location.connectors[0].status == "unknown") {
  			url = "img/bm_64x64.png";
  		} else {
  			url = "img/gm_64x64.png";
		}

    } else if (direction == "right") {
    	pinDirection = "r";
		shape = {
					coords: [28,1,31,5,31,11,28,15,21,15,18,10,18,5,23,1,28,1],
					type: 'poly'
		};

		offset = 7;

		if (location.connectors[0].status == "offline") {
			url = "img/pr_64x64.png";
			
		} else if (location.connectors[0].status == "planned" || location.connectors[0].status == "unknown") {
  			url = "img/br_64x64.png";
			
  		} else {
  			url = "img/gr_64x64.png";
		}				
    }

	if (location.connectors[0].status == "offline") {
		zindex = 8000 - (location.lat*100);
		obstat = "offline";
	} else if (location.connectors[0].status == "planned" || location.connectors[0].status == "unknown") {
  		zindex = 6000 - (location.lat*100);
  	} else {
  		zindex = 10000 - (location.lat*100);
	}

	var image = { 
		url: url,
		scaledSize: new google.maps.Size(32, 32),
		origin: new google.maps.Point(0, 0),
		anchor: new google.maps.Point(16, 32)
    };

	var marker = new google.maps.Marker({
		info: location,
		position: {lat: location.lat, lng: location.lng},
		map: map,
		icon: image,
		shape: shape,
		zIndex: zindex,
		visible: false,
		offset: offset,
		updateMarker: updateMarkerUrl,
		routeOptions: routeOptions,
		showInfoWindow: getMarkerInfoWindow,
		pinDirection: pinDirection,
		getChargerHistory: getChargerHistory,
		processChargerHistory: processChargerHistory,
		markerRouteDistanceWindow: markerRouteDistanceWindow()
	});

    google.maps.event.addListener(marker, 'click', getMarkerInfoWindow);
    
    google.maps.event.addListener(marker, 'click', function(){
    	activeMarker=this;
    	
    });
    // google.maps.event.addListener(marker, 'mouseover', function(){
    // 	var a = updateMarkersOnMapFromRoute.getRouteMarkers();
    // 	for (var i = 0; i < a.length; i++) {
    // 		a[i].markerRouteDistanceWindow.showDistancePopup(a[i]);
    // 	};
    
    // });
    // google.maps.event.addListener(marker, 'mouseout', function(){
    // 	var a = updateMarkersOnMapFromRoute.getRouteMarkers();
    // 	for (var i = 0; i < a.length; i++) {
    // 		a[i].markerRouteDistanceWindow.closeDistancePopup();
    // 	};
    // });

    marker.markerRouteDistanceWindow.setDistanceIntoRoute(allmarkers.length);
  
    allmarkers.push(marker);
}

function getChargerHistory(a){
	var p = a.info.provider;
	var lat = a.info.lat;
	var lng = a.info.lng;
	var j = {
		provider: p,
		lat: lat,
		lng: lng
	};

	ajaxHandler({
		url: "php/charger_history.php",
		data: j,
		success: processChargerHistory
	});

	//submitJSONToServer("php/charger_history.php",JSON.stringify(j),processChargerHistory);
}

function processChargerHistory(result){
	var a = result;
	activeMarker.chargerHistory = a;
}

function updateMarkerUrl(){
	var icon = this.getIcon();

	for (var i = 0; i < this.info.connectors.length; i++){
		if (this.info.connectors[i].type.id == connectors[0]) {
			if (this.pinDirection == "r") {
				if (this.info.connectors[i].status == "offline"){
					icon.url = "img/pr_64x64.png";
				} else if (this.info.connectors[i].status == "planned" || 
						   this.info.connectors[i].status == "unknown" ||
						   this.info.connectors[i].status == "occupied" ||
						   this.info.connectors[i].status == "in session") {
					icon.url = "img/br_64x64.png";
				} else {
					icon.url = "img/gr_64x64.png";
				}
				this.setIcon(icon);
				break;
			} else if (this.pinDirection == "m") {
				if (this.info.connectors[i].status == "offline"){
					icon.url = "img/pm_64x64.png";
				} else if (this.info.connectors[i].status == "planned" || 
						   this.info.connectors[i].status == "unknown" ||
						   this.info.connectors[i].status == "occupied" ||
						   this.info.connectors[i].status == "in session") {
					icon.url = "img/bm_64x64.png";
				} else {
					icon.url = "img/gm_64x64.png";
				}
				this.setIcon(icon);
				break;
			} else {
				if (this.info.connectors[i].status == "offline"){
					icon.url = "img/pl_64x64.png";
				} else if (this.info.connectors[i].status == "planned" || 
						   this.info.connectors[i].status == "unknown" ||
						   this.info.connectors[i].status == "occupied" ||
						   this.info.connectors[i].status == "in session") {
					icon.url = "img/bl_64x64.png";
				} else {
					icon.url = "img/gl_64x64.png";
				}
				this.setIcon(icon);
				break;
			}
		}
	}
	
	
  
}

function routeOptions(){

	//hereyouare
	mapWaypointPopup.setPseudoGeoResp("Set this pin as:",{lat:this.info.lat,lng:this.info.lng});
	mapWaypointPopup.setWindowPos({lat:this.info.lat,lng:this.info.lng});
	mapWaypointPopup.open();


	// if (document.getElementById("routestart").value == "" || document.getElementById("routeend").value == ""){
	// 	swal({
	// 		title: "Oops..",
	// 		text: "You need a start and end point before you can add a waypoint.",
	// 		type: "error"
	// 	});
	// } else {
	// 	var thisWaypoint = {};
	// 	thisWaypoint.name = this.info.name;
	// 	thisWaypoint.lat = this.info.lat;
	// 	thisWaypoint.lng = this.info.lng;
	// 	processWaypoints(thisWaypoint);
	// }	

	// if (openWindow != null){
	//     	openWindow.close();
	// }
}

function getMarkerInfoWindow(n){

	activeMarker = this;

	this.getChargerHistory(this);

	
	if (openRssWindow != null){
		openRssWindow.close();
	}
	if (openWindow != null){
		openWindow.close();
	}
	showHistoryInfoWindow.close();
	
	var content = infoWindowContent(this.info);

	var infoWindow = new google.maps.InfoWindow({
		getRssInfoWindow: showRssInfoWindow,
		getHistoryInfoWindow: showHistoryInfoWindow,
	    content: content,
	    maxWidth: 300,
	    pixelOffset: new google.maps.Size(this.offset, 0),
	    zIndex: 9,
	    disableAutoPan: false
	});

	google.maps.event.addListener(infoWindow,'closeclick',function(){
		showHistoryInfoWindow.close();
	});

	openWindow = infoWindow;
	openWindow.info = this.info;
	openWindow.open(map,this);
	delete infoWindow;
	
}

function showRssInfoWindow(){

	if (openRssWindow != null){
    		openRssWindow.close();
    }

	var content = 
	'<div class = "infowindow_rss">'+
	'<h3>Available Connectors</h3>'+
	'<table style="width:100%">'; 

	if (this.info.provider == "Ecotricity" || this.info.provider == "Polar") {
	    for (var i = 0; i < this.info.connectors.length; i++) {
	    	if (rssCheckChargerPresent(this.info,i)) {
	    		var rssActionText = "Remove";
	    	} else {
	    		var rssActionText = "Add";
	    	}
	    	content += '<tr><td style="width:60%"><h4>' + this.info.connectors[i].type.title + '</h4> </td> <td style="width:40%;text-align:center" > <a class="rss_cmd" onclick="rssAddRemove(' + i + ')">' + rssActionText + '</a></td></tr>';
	    }
	} else {
		content += '<tr><td style="width:100%;text-align:center;" ><h3> Sorry, not yet supported.</h3> </td> </tr>';
	}

	content+= "</table></div>";

	var infoWindow = new google.maps.InfoWindow({
		    content: content,
		    minWidth: 100,
		    pixelOffset: new google.maps.Size(activeMarker.offset + 75, -50),
		    zIndex: 10,
		    disableAutoPan: true
		});

	openRssWindow = infoWindow;
	openRssWindow.open(map,activeMarker);
	delete infoWindow;

}

var showHistoryInfoWindow = function(){

	var historyWindow;
	var infoWindowContent;
	var infoWindowContentHeight;

	function statusColour(status){
		if (status === 'online' || status === "in session"){
			return 'green';
		} else if (status === 'offline'){
			return 'red';
		} else {
			return 'black';
		}
	}

	return {
		show: function(){
			if (historyWindow !== undefined){
		    		historyWindow.close();
		    }
		    infoWindowContentHeight = $(".infowindow-content").css("height");
		    infoWindowContent = $(".infowindow-content").html();

		    var a = [];

		    for (var i = 0; i < activeMarker.info.connectors.length; i++) {
		    	if (activeMarker.info.connectors[i].isDual){
		    		a.push(activeMarker.info.connectors[i].type.title);
		    	}
		    };
		    
			var content = 
			// '<div class = "charger-history">'+
			'<h5 style="min-width:300px">Status History</h5>'+
			'<div class="charger-history-content">';

			
			// +
			// '<tr>'+
			// '<th>Date</th>'+
			// '<th>Connector</th>'+
			// '<th>State</th>'+
			// '</tr>'; 

			if (activeMarker.chargerHistory !== false){

				content += '<div class="w3-row">';

				for (var i = 0; i < activeMarker.chargerHistory.length; i++) {


					if (a.indexOf(activeMarker.chargerHistory[i].type) < 0){
						content += '<div class="w3-col s4">';

						//format is 2016-04-01 14:30:24

						var d = activeMarker.chargerHistory[i].date_time;
						var date = d.split(" ")[0];
						var time = d.split(" ")[1];
						var date = date.split("-");
						var time = time.split(":");

						content += date[2] + ' ' + (getActualMonth(Number(date[1])-1)) + ' ' + date[0].substring(2,4) + ' ' + time[0] + ":" + time[1]	;
						content += '</div>';

						content += '<div class="w3-col s4">';

						content += activeMarker.chargerHistory[i].type;
						content += '</div>';

						content += '<div class="w3-col s4 w3-text-' + statusColour(activeMarker.chargerHistory[i].new_status) + '">';

						content += activeMarker.chargerHistory[i].new_status;
						content += '</div>';
					}

				};

				content += '</div>';
			} else {
				content += '<div class="w3-col s12">Looks like we don'+String.fromCharCode(39)+'t have any historical data yet..</div>';
			}

			content+= "</div>";
			
			infoWindowContentHeight = $(".infowindow-content").css("height");
			$(".infowindow-content").css("height",infoWindowContentHeight)
									.css("overflow","auto")
									.html(content);
			$(".history-button").removeClass("w3-green").addClass("w3-blue");

			// // var infoWindow = new google.maps.InfoWindow({
			// 	    content: content,
			// 	    pixelOffset: new google.maps.Size(activeMarker.offset + 102, -63),
			// 	    zIndex: 10,
			// 	    maxWidth:350,
			// 	    disableAutoPan: false
			// 	});

			// historyWindow = infoWindow;
			// historyWindow.open(map,activeMarker);
		
		},
		close: function(){
			if (historyWindow !== undefined){
				historyWindow.close();	
			}
			$(".infowindow-content").html(infoWindowContent);
			$(".history-button").removeClass("w3-blue").addClass("w3-green");
		},
		toggle: function(){
			if (!$(".charger-history-content").length) {
				this.show();

			} else {
				this.close();
			}
		}
	};
	

		

}();

var connectorsController = function (){

	var timeout;

	var connectorIds = [
		{
			id: 2, 
			name: "CCS"
		},
		{
			id: 1, 
			name: "CHAdeMO"
		},
		{
			id: 3, 
			name: "AC (tethered)"
		},
		{
			id: 4, 
			name: "AC (socket)"
		},
		{
			id: 7, 
			name: "Tesla"
		}
	];

	return {
		update: function(cid,startup){	

			clearTimeout(timeout);		

			if (startup === undefined){
				//normal operation

				//initilise our array if we haven't got any connectors
				if (connectors == "none"){
					connectors = [];
				}

				var cLoc = connectors.indexOf(cid);

				//if the id being added exists, remove it. Else, add it.
				if (cLoc >= 0){
					connectors.splice(cLoc,1);
				} else {
					connectors.push(cid);
				}
				//If we can, store the users choice locally.
				if (storagePossible) {
			    	localStorage.connectors = JSON.stringify(connectors);
				}

			} else {
				//first execution after site load, autoload if you will
				if (storagePossible){
					if (localStorage.connectors !== undefined){
						connectors = JSON.parse(localStorage.connectors);
					} else {
						connectors = "none";
					}
				} else {
					connectorsController.update(1);
				}
			}

			updateConnectorCheckmarks();

			timeout = setTimeout(function(){
				showMarkers();
			},500);
		},
		getNameArray: function(){
			var currentConnectors = [];

			connectors.forEach(function(cValue,cIndex){
				var connectorName = "";
				connectorIds.forEach(function(idValue,idIndex){
					if (cValue == idValue.id) {
						connectorName = idValue.name;
					}
				});
				currentConnectors.push(connectorName);
			});

			return currentConnectors;
		},
		clear: function(){
			connectors = [];
		}

	};

}();




var getActualMonth = function(){
	var m = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];

	return function(e){
		return m[e];
	};
}();

function updateConnectorCheckmarks(){

	$(".connectors i").each(function(index,element){
		switch (index){
			case 0:
				if (connectors.indexOf(2) >= 0) {
					$(element).fadeTo(50,1);
				} else {
					$(element).fadeTo(50,0);
				}
				break;
			case 1:
				if (connectors.indexOf(1) >= 0) {
					$(element).fadeTo(50,1);
				} else {
					$(element).fadeTo(50,0);
				}
				break;
			case 2:
				if (connectors.indexOf(3) >= 0) {
					$(element).fadeTo(50,1);
				} else {
					$(element).fadeTo(50,0);
				}
				break;
			case 3:
				if (connectors.indexOf(4) >= 0) {
					$(element).fadeTo(50,1);
				} else {
					$(element).fadeTo(50,0);
				}
				break;
			case 4:
				if (connectors.indexOf(7) >= 0) {
					$(element).fadeTo(50,1);
				} else {
					$(element).fadeTo(50,0);
				}
				break;
			default:
				break;
		}
	});
}

function showMarkers(args) {

	for ( var i = 0; i < allmarkers.length; i++) {
		allmarkers[i].setVisible(false);
		allmarkers[i].setOpacity(1);

		var connectorcount = allmarkers[i].info.connectors.length;

		for ( var y = 0; y < connectorcount; y++) {

			if (connectors.indexOf(allmarkers[i].info.connectors[y].type.id) >= 0) {
				allmarkers[i].setVisible(true);
				allmarkers[i].updateMarker();
			}
		}
	}
	
}

function infoWindowContent (location){
	var status;
	var dual = false;
	var psurl = "http://api.plugshare.com/view/map?latitude=mylat&longitude=mylng&spanLat=0.003&spanLng=0.02";

	if (device == "computer"){
		var linkTarget = 'target="_blank"';
	} else {
		var linkTarget = 'target="_self"';
	}

	psurl = psurl.replace("mylat",location.lat);
	psurl = psurl.replace("mylng",location.lng);

	var content = 
	'<div class = "infowindow">';

	if (location.provider === "Ecotricity" || location.provider === "CYC"){
				//content += '<img class="history-img" src="img/history.png" width="20px" height="20px" onclick="openWindow.getHistoryInfoWindow.show()"/>';
	}

	content+= '<div class="w3-content w3-border w3-border-white infowindow-content">';
	
	content += '<h5 style="min-width:300px;">'+ location.name +' (' + location.provider + ')</h5>';

	if (location.postcode !== undefined && location.postcode !== null && location.postcode !== "") {
		//content+= '<span><a href="' + getGoogleMapsUrl(location.postcode) + '" ' + linkTarget + '>' + location.postcode + '</a></span>';
	} else {
		//content+= '</h5>';
	}

	

	for (var i = 0; i < location.connectors.length; i++) {
		
		if (location.connectors[i].isDual === true){
			var dual = true;
		}

		if (location.connectors[i].status == "unknown") {
			status = 'unknown';
			styleHTML = ' style="color: black" ';
		} else if (location.connectors[i].status == "planned") {
			status = 'planned';
			styleHTML = ' style="color: #2291ff" ';
		} else if (location.connectors[i].status == "offline") {
			status = 'offline';
			styleHTML = ' style="color: red" ';
		} else if (location.connectors[i].status == "occupied") {
			status = 'occupied';
			styleHTML = ' style="color: #2291ff" ';
		} else if (location.connectors[i].status == "in session") {
			status = 'in session';
			styleHTML = ' style="color: #2291ff" ';
		} else {
			status = 'online';
			styleHTML = ' style="color: green" ';
		}

		var pwrstr = location.connectors[i].power + " kW";

		if (location.connectors[i].socket == undefined){
			if (location.connectors[i].quantity !== undefined){
				var connstr = location.connectors[i].type.title + ': ' + location.connectors[i].quantity;
			} else {
				var connstr = location.connectors[i].type.title;
			}
		} else {
			var connstr = location.connectors[i].socket;
		}

		if (location.connectors[i].isDual === true){
			//status+= '*'
		} else {
		
		}
		

		content += '<div class="w3-row">'+
						 '<div class="w3-col s5 w3-left-align"><i class="fa fa-fw fa-plug"></i><p style="display:inline-block;">' + connstr + '</p></div>' +
		                 '<div class="w3-col s4 w3-center"><i class="fa fa-fw fa-bolt"></i><p style="display:inline-block;">' + pwrstr +   '</p></div>' + 
		                 '<div class="w3-col s3 w3-center"><p style="display:inline-block;"> ' + '<span ' + styleHTML + '>' + status + '</span></p></div>' + 
		          '</div>';
		
	}

	if (location.provider === "Ecotricity" && !location.isBeta){
		content += '<div class="w3-col s12 w3-padding-top w3-left-align w3-text-red"><p>Ecotricity status no longer live. Please check their app.</p></div>';	
	} else if (location.provider === "Ecotricity" && location.isBeta) {

		var lastUpdateTime = moment(location.lastHeartbeat);
					
		//var lastUpdateTime = lastUpdateTime.getHours() + ":" + lastUpdateTime.getMinutes() + " on " + lastUpdateTime.getDate() + " " + getActualMonth(lastUpdateTime.getMonth());

		content += '<div class="w3-col s12 w3-padding-top w3-left-align"><i class="fa fa-fw fa-heartbeat"></i><p>'+lastUpdateTime.calendar()+'</p></div>';
	} else {

	}
	

	//add source

	content += '<div class="w3-right-align w3-padding-bottom"><span class="">Data source:</span> <span class=""><a href="'+location.source.url+'" target="_blank">'+location.source.name+'</a></spanLng></div>';
	
	content += '</div>';
	
	//'<table>'+
	content +='<div class="w3-btn-bar">'+
	'<a class="w3-btn w3-round w3-green w3-hover-blue w3-col s4 w3-border w3-border-white" href="'+psurl+'" ' + linkTarget + '>Plugshare</a>'+
	'<span class="w3-btn w3-round w3-green w3-hover-blue w3-col s4 w3-border w3-border-white" onclick="activeMarker.routeOptions()">Route</span>' +
	'<span class="w3-btn w3-round w3-green w3-hover-blue w3-col s4 w3-border w3-border-white history-button" onclick="openWindow.getHistoryInfoWindow.toggle()">History</span>'+
	'</div>';
	
	//'<span class="w3-btn w3-col s4 w3-border-white" onclick="openWindow.getRssInfoWindow()">RSS</span>'+
	//'</div>';
	
	//'<a href="http://maps.google.com/?saddr=Current%20Location&daddr='+lat+','+lng+'">Get me there!</a>'+
	
	

	return content;
}

//
// Data management functions
//

function updateProviderCheckmarks(){
	$(".networks span").each(function(index,element){
		if ( providerreq.providers.indexOf($(element).attr("data-value")) >=0){
			$(element).children("i").fadeTo(50,1)
		} else {
			$(element).children("i").fadeTo(50,0)
		}
	});

}

var providersController = function(){

	var timeout;
	var selectedProviders = [];

	function upgradeHandler(){
		if (!localStorage.selectedProviders && localStorage.networks) {
			var oldNetworks = JSON.parse(localStorage.networks);
			oldNetworks.providers.forEach(function(value,index){
				if (value) {
					selectedProviders.push(value);
				}
			});
			localStorage.selectedProviders = JSON.stringify(selectedProviders);
		}
	}

	return {
		update: function(startup){
			//normal execution

			upgradeHandler();

			mapMask.show({
				type: "markerLoad",
				showAnimation: true
			});

			clearTimeout(timeout);

			if (startup === undefined){

				selectedProviders = [];

				$(".network-selected").each(function(){
					selectedProviders.push($(this).data("value"));
				});

				if(storagePossible) {
			    	localStorage.selectedProviders = JSON.stringify(selectedProviders);
				}

			} else {
				//first execution

				if (storagePossible && (localStorage.selectedProviders !== undefined)){
					selectedProviders = JSON.parse(localStorage.selectedProviders);
				} 

				$(".networks span").each(function(){
					if (selectedProviders.indexOf($(this).data("value")) >= 0) {
						$(this).addClass("network-selected");
					}
				});

				if (storagePossible) {
					//load from the cache whilst we wait for new data
			    	if (localStorage.data !== undefined){
			    		//loadStatusData(localStorage.data);
			    	}
				}
			}

			timeout = setTimeout(function(){
				ajaxHandler({
					url: "php/status_data.php",
					data: {providers: selectedProviders},
					success: loadStatusData
				});
				//submitJSONToServer("php/status_data.php",JSON.stringify({providers: selectedProviders}),loadStatusData);
			},750);

		},
		selectAll: function(){
			$(".networks span").each(function(index,element){
				$(this).addClass("network-selected");
				providersController.update();
			});
		},
		refresh: function(){
			providersController.update();
		}
	};    


}();


function loadStatusData(response) {

	//do we have a map to add markers?
	if (typeof(map) != 'object' || (typeof(map) == 'object' && !map.zoom)) {
		//No, so return (mask will prompt reload)
		return;
	}

	var co = response;
	var skipMarkerRefresh = false;

	//Is the data we have populated already the same as what we've received? If so, end. TODO

	// if (storagePossible && localStorage.data) {
	// 	var oldData = JSON.parse(localStorage.data);
	// 	if (oldData.last_updated === co.last_updated && oldData.locations.length == co.locations.length) {
	// 		skipMarkerRefresh = true;
	// 	}
	// }

	if (!skipMarkerRefresh || firstRun){

		$("#updated").html('Updated: ' + co.last_updated);

		firstRun = false;

		if (storagePossible){

			localStorage.data = JSON.stringify(response);
		}

		firstRun = false;

		if (allmarkers.length > 0){
			for (var i = 0; i < allmarkers.length; i++) {
				allmarkers[i].setMap(null);
			}
			allmarkers = [];
		}

		var closeproximity_A = false;
		var closeproximity_B = false;
		var radius = 0;

		for (var i = 0; i < co.locations.length; i++) {

			closeproximity_A = false;
			closeproximity_B = false;

			if (co.locations[i].provider == "Ecotricity"){
				radius = 0.2;
			} else if (co.locations[i].provider == "Polar"){
				radius = 0.1;
			} else {
				radius = 0.5;
			}

			for (var y = 0; y < co.locations.length; y++) {
				
				if (i != y) {
					if (calcCordDistance(co.locations[i].lat,co.locations[i].lng,co.locations[y].lat,co.locations[y].lng) < radius) {
						if (i < y) {
							closeproximity_A = true;
						} else {
							closeproximity_B = true;
						}	
					}
				}
			}

			if (closeproximity_A) {
				addMarker(co.locations[i],"left");	
			} else if (closeproximity_B) {
				addMarker(co.locations[i],"right");	
			} else {
				addMarker(co.locations[i],"mid");
			}
			
		}

		showMarkers();

		if (urlParamsController.getParams()['src'] == "evhw"){
			tweetLinkInfoWindow(urlParamsController.getParams()["lat"],urlParamsController.getParams()["lng"]);
		}

		setTimeout(function(){
			$(document).trigger("markersAdded");
		},750);
    			
	}

	mapMask.hide();
}

//
// Cookies and other goodies
//

function addCookie (cname, cvalue, dexp) {
	var d = new Date();
    d.setTime(d.getTime() + (dexp*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
	document.cookie = cname + "=" + cvalue + "; " +  expires;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return "";
}

var urlParamsController = function(){
	var getParameters = {};
	var hashParameters = {};
	var parts;
	return {
		fetch: function(){
			parts = window.location.search.substr(1).split("&");
			for (var i = 0; i < parts.length; i++) {
		    	var temp = parts[i].split("=");
		    	getParameters[decodeURIComponent(temp[0])] = decodeURIComponent(temp[1]);
			}
			parts = window.location.hash.substr(1).split("&");
			for (var i = 0; i < parts.length; i++) {
		    	var temp = parts[i].split("=");
		    	hashParameters[decodeURIComponent(temp[0])] = decodeURIComponent(temp[1]);
			}
		},
		getParams: function(){
			return getParameters;
		},
		hashParams: function(){
			return hashParameters;
		}
	};	
}();

function updateUrl(o){
	var center = map.getCenter();
	var lat = center.lat().toFixed(5);
	var lng = center.lng().toFixed(5);
	var z = map.getZoom();
	var path = window.location.pathname;
	history.replaceState(history.state,window.document.title,"/?lat="+lat+"&lng="+lng+"&z="+z);

	if (o !== undefined){
		history.replaceState(history.state,window.document.title,"/?lat="+lat+"&lng="+lng+"&z="+z +"&login=true");
	}
}

function gradient(d,a){
	return a/d;
}

//
// RSS Functions
//

function rssCheckChargerPresent(info, connector){
	for (var i = 0; i < c.targetChargers.length; i++) {
		if (info.provider == c.targetChargers[i].provider && info.lat == c.targetChargers[i].lat &&
			info.lng == c.targetChargers[i].lng && info.connectors[connector].type.title == c.targetChargers[i].type ) {
			return true;
		} 
	}
	return false;
}

function rssAddRemove(connector){
	document.getElementsByClassName("rssgen")[0].style.display = "inline-block";
	document.getElementById("rssgen-text").setAttribute("onclick", "requestUrl()");
	document.getElementById("rssgen-text").style.cursor = "pointer";

	var newentry = true;

	var cInfo = {
		name: activeMarker.info.name,
		provider: activeMarker.info.provider,
		lat: activeMarker.info.lat,
		lng: activeMarker.info.lng,
		type: activeMarker.info.connectors[connector].type.title,
		status: "new"
	};

	if (c.targetChargers.length > 0){
		for (var i = 0; i < c.targetChargers.length && newentry; i++) {
			if (cInfo.provider == c.targetChargers[i].provider && cInfo.lat == c.targetChargers[i].lat &&
				cInfo.lng == c.targetChargers[i].lng && cInfo.type == c.targetChargers[i].type ) {
				document.getElementsByClassName("rss_cmd")[connector].innerHTML = "Add";
				c.targetChargers.splice(i,1);
				document.getElementById("rssgen-text").innerHTML = "Click to generate RSS feed URL. You've added " + c.targetChargers.length + " station(s) so far.";
				if (c.targetChargers.length == 0){
					rssClear();
				}
				return;
			};
		}
		c.targetChargers.push(cInfo);
		document.getElementsByClassName("rss_cmd")[connector].innerHTML = "Remove";
		
	} else {
		c.targetChargers.push(cInfo);
		document.getElementsByClassName("rss_cmd")[connector].innerHTML = "Remove";
	}

	document.getElementById("rssgen-text").innerHTML = "Click to generate RSS feed URL. You've added " + c.targetChargers.length + " station(s) so far.";
	
}

function requestUrl(){
	submitJSONToServer("php/rss_process.php",
						JSON.stringify(c),
						processRssServerResponse
	);
}

function rssClear(){
	c.targetChargers = [];
	// rsstargets = [];
	document.getElementById("rss-response").style.display = "none";
	//document.getElementsByClassName("rssgen")[0].style.left = "calc(50% - " + (document.getElementsByClassName("rssgen")[0].offsetWidth)/2 + "px)";
	document.getElementsByClassName("rssgen")[0].style.display = "none";
	document.getElementById("rss-response").value = "";
	document.getElementById("rssgen-text").setAttribute("onclick", "requestUrl()");
	document.getElementById("rssgen-text").style.cursor = "pointer";
	document.getElementsByClassName("rssgen")[0].style.top = "85%";

}

function initalGetRequest(){
	var xhttp = new XMLHttpRequest();
	var url;
	if (isCordovaApp) {
		url = "https://evhighwaystatus.co.uk/php/heat_me_up.php";
	} else {
		url = "php/heat_me_up.php"
	}
	 xhttp.onreadystatechange = function() { 
        if (xhttp.readyState == 4 && xhttp.status == 200){
  		//nothing to see here
  		}
    }
    xhttp.open("GET",url,true);
    xhttp.send();
}

function submitCarwingsAction(){
	updateUrl(true);
	if (typeof(ga) != 'undefined') {
		ga('send', 'event', 'Carwings', document.getElementById("carwings-options").value, 'HeatMyLeaf');
	}
	$("#loading").show();
	$("#carwings-submit").prop("disabled",true);
	$("#carwings-submit").css({"opacity":0});

	var ajaxData = 'username='+$("#username").val()+'&password='+$("#password").val()+'&action='+$("#carwings-options").val();

	ajaxHandler({
		url: "php/heat_me_up.php",
		data: ajaxData,
		success: processCarwingsResponse,
		always: function(){
			$("#loading").hide();
			$("#carwings-submit").prop("disabled",false);
			$("#carwings-submit").css({"opacity":1});
		}
	});


	// var xhttp = new XMLHttpRequest();
	//  xhttp.onreadystatechange = function() { 
 //        if (xhttp.readyState == 4 && xhttp.status == 200)
 //            processCarwingsResponse(xhttp.responseText);
 //    };
 //    xhttp.open("POST","https://evhighwaystatus.co.uk/php/heat_me_up.php",true);
 //    xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
 //    xhttp.send('username='+document.getElementById("username").value+'&password='+document.getElementById("password").value+'&action='+document.getElementById("carwings-options").value);
}





function processCarwingsResponse(response){

	var r = response;
	var message = "";
	var type = "";
	var title = ""; 

	switch(r.status){ 

		case "login_fail":
			title = "Login Failed";
			type = "error";
			message = "Looks like those Carwings credentials aren't valid..";
			break; 

		case "action_success":
			title = "Success!";
			type = "success";

			switch (r.request){
				case "ac_on":
					message = "Hi " + r.user_id + "!<br><br>Your LEAF's now warming as we speak..";
					break;

				case "ac_off":
					message = "Hi " + r.user_id + "!<br><br>Your LEAF's AC has been turned off..";
					break;

				case "battery":
					message = "Hi " + r.user_id + "! Here's your current battery info..<br><br>";
					message+= "Range without AC: <b>" + r.leaf_info.range.ac_off + "</b> miles<br><br>";
					message+= "Range with AC: <b>" + r.leaf_info.range.ac_on + "</b> miles<br><br>";
					message+= "Bars: <b>" + r.leaf_info.bars + "/12</b>";
					break;

				case "start_charge":
					message = "Hi " + r.user_id + "!<br><br>Your LEAF's charging has now been started..";
					break;

				default:
					break
			}
			
			//heatMeUp();
			break;

		case "request_timeout":
			title = "Timeout";
			type = "info";
			message = "Hi " + r.user_id + "!<br><br>The request was issued successfully, but confirmation wasn't received in a timely manor..";
			//heatMeUp();
			break;	

		case "carwings_update_fail":
			title = "Failed";
			type = "error";
			message = "Hi " + r.user_id + "!<br><br>We logged in OK, but the command failed. Maybe wait and try again.";
			break;
	}

	swal({
		title: title,
		html: true,
		type: type,
		text: message
	});

}

var networkFailure = function() {
	return {
		show: function(){
			if (!$(".network-failure-alert").length) {
					$alert = $("<div>",{class: "w3-container w3-card-8 w3-animate-top w3-red w3-top network-failure-alert", style: "z-index:5; position:absolute;"}).append($("<span>",{class:"w3-closebtn"}).append("&times;").click(function(){
							this.parentNode.remove();
						})).append("<p>Network connection unavailable</p>");
					$(".wrap").append($alert);
				}
			//mapMask.hide();
		},
		hide: function(){
			$(".network-failure-alert").remove();
		}
	};
	
}();

function ajaxHandler(settings) {
	var ajaxUrl;

	if (isCordovaApp) {
		ajaxUrl = "https://evhighwaystatus.co.uk/" + settings.url;
	} else {
		ajaxUrl = settings.url;
	}

	if (typeof(settings.data) === 'string') {
		var ajaxData = settings.data;
	} else {
		var ajaxData = JSON.stringify(settings.data);
	}	 

	$.ajax({
		method: 'post',
		url: ajaxUrl,
		beforeSend: function(xhr) {
			if (isCordovaApp) {
				xhr.setRequestHeader('xAppAuth', '8;iLY3AZ1m7,?[pUKM0!+E7h44;u2W81dWl<(mf85kevN0J-MN^V6P1F47VTE77');
			}
			if (typeof(settings.data) === 'string') {
				xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			}
		},
		data: ajaxData
	}).done(function(data,b,c){
		settings.success(data);
	}).fail(function(a,b,c){
		networkFailure.show();
		if (settings.fail) {
			settings.fail();
		}
	}).always(function(a,b,c){
		if (settings.always) {
			settings.always();
		}
	});
}



function processRssServerResponse(response){
	if (response.length < 60) {
		var boxContent = response;
	} else {
		var boxContent = "Sorry, something went wrong.";
	}

	document.getElementById("rss-response").value = boxContent;
	document.getElementById("rss-response").style.display = "block";
	//document.getElementsByClassName("rssgen")[0].style.left = "calc(50% - " + (document.getElementsByClassName("rssgen")[0].offsetWidth)/2 + "px)";
	document.getElementById("rssgen-text").innerHTML = "Here's your unique RSS URL:";
	document.getElementById("rssgen-text").setAttribute("onclick", "");
	document.getElementById("rssgen-text").style.cursor = "default";
	document.getElementsByClassName("rssgen")[0].style.top = "45%";
	
}
// Popup
function cookieMessage(){

	$alert = $("<div>",{class: "w3-container w3-light-grey w3-bottom w3-card-8", style: "z-index:5;"}).append($("<span>",{class:"w3-closebtn"}).append("&times;").click(function(){
		this.parentNode.style.display = "none";
		addCookie("cookies",true,365);
	})).append("<p>Just so you know, we use cookies to enhance your browsing experience and provide website statistics.</p>");

	$(".wrap").append($alert);												  
		
}

var bearing = function(){
	
	return {
		minusDegrees: function (bearing,degrees){
			var a = bearing;
			for (var i = 0; i < degrees; i++) {
				a-= 1;
				if (a <= -1){
					a = 359;
				}
			};

			return a;
		},
		addDegrees: function (bearing, degrees){
			var a = bearing;
			for (var i = 0; i < degrees; i++) {
				a+= 1;
				if (a >= 360){
					a = 0;
				}
			};

			return a;
		},
		antiClockwiseDegrees: function(bearing,target){
			var a = bearing;
			var delta = 0;

			while (Math.abs(a - target) > 1) {
				a = this.minusDegrees(a,1);
				delta++;
			}

			return delta;
		}, 
		smallestDelta: function(bearing1, bearing2){
			var a = bearing1;
			var delta1 = 0;
			var delta2 = 0;

			while (Math.abs(bearing2 - a) > 1) {
				a = this.addDegrees(a,1);
				delta1++;
			}

			a = bearing1;

			while (Math.abs(bearing2 - a) > 1) {
				a = this.minusDegrees(a,1);
				delta2++;
			}

			if (delta1 < delta2) {
				return delta1;
			} else {
				return delta2;
			}
		}
	};
}();

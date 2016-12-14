
$(document).ready(function(){
	var alertController = function(){

		var shownAlerts = {
			alerts: []
		};

		if (localStorage.shownAlerts) {
			shownAlerts = JSON.parse(localStorage.shownAlerts);
		}

		
		var alerts = [
		//{
		//	id: 1,
		//	priority: "green",
		//	title: "Ecotricity",
		//	body: "We're happy to announce that you now have the option to see the same status information for the Ecotricity Electric Highway as you can on the app. To do so, go checkout the \"Networks\" menu and choose the new option. Like all good things though, we can't say exactly how long this will last, but rest assured we'll keep doing all we can to get EV drivers like you the data you need. Enjoy!"
		//}
		];

		function recordDisplayedAlert(id) {
			shownAlerts.alerts.push(id);
			localStorage.shownAlerts = JSON.stringify(shownAlerts);
		}

		function checkAlertShown(id) {
			if (shownAlerts.alerts.indexOf(id) > -1) {
				return true;
			}
			return false;
		}

		function showAlert(id) {
			$alert = $("<div>",{class: "w3-container w3-"+alerts[id].priority+" w3-top w3-card-8", style: "z-index:5;","data-alert-id": id}).append($("<span>",{class:"w3-closebtn"}).append("&times;").click(function(){
				this.parentNode.style.display = "none";
				recordDisplayedAlert(Number($(this.parentNode).data("alert-id")));
			})).append("<h3>"+alerts[id].title+"</h3><p>"+alerts[id].body+"</p>");
			$(".wrap").append($alert);												  
		}

		return {
			init: function(){
				for (var i = 0; i < alerts.length; i++) {
					if (!checkAlertShown(i)) {
						showAlert(i);
						break;
					}
				};
			}
		};
	}();

	$(document).on("showAlerts",function(){
		alertController.init();
	});
	

});


$(document).ready(function(){
	var alertController = function(){

		var shownAlerts = {
			alerts: []
		};

		if (localStorage.shownAlerts) {
			shownAlerts = JSON.parse(localStorage.shownAlerts);
		}

		
		var alerts = [
		{
			id: 2,
			priority: "green",
			title: "Open Source",
			body: "Hi Folks. Just an update to let you know that we've decided to Open Source the site to grow and allow more contributions. Want to help? Find the repo over at <a href='https://github.com/jivemonkey2000/evhighwaystatus'>GitHub</a>. Thanks."
		}
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

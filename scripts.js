//---Homepage Javascript-------------------------------------------------------------------------------------------

//a function to get regular offers from local php file and parse it into p tags
function getOffers()
{//ajax update statement
	fetch('getOffers.php')
		.then(
			function(response) {
				return response.text();
			})
		.then(
			function(data) {
				document.getElementById("offers").innerHTML //using the offers div to inject offers into
	           = "<p>" + data + "</p>"
			});
			updateOffers(); //to reset every 5 seconds
}

//a function that refreshes the regular offers every 5 seconds
function updateOffers()
{
	setTimeout(getOffers, 5000);
}

//a function that gets the output from the local php file that outputs xml and parses it into the XML offers div - uses AJAX
function getXMLOffers()
{
	fetch('getOffers.php?useXML')
		.then(
			function(response) {
				return response.text();
			})
		.then(
			function(data) {
				let parser = new DOMParser();
				let xmlDoc = parser.parseFromString(data,"text/xml");
				document.getElementById("XMLoffers").innerHTML =
					xmlDoc.getElementsByTagName("eventTitle")[0].childNodes[0].nodeValue +
					"<br>" +
					xmlDoc.getElementsByTagName("catDesc")[0].childNodes[0].nodeValue +
					"<br>" +
					xmlDoc.getElementsByTagName("eventPrice")[0].childNodes[0].nodeValue;
		});
}

//both of these need to be ran on startup
getOffers();
getXMLOffers();

//----------------------------------------------------------------------------------------------
// Global Variable Declaration For bookEvents.php
var termsText = document.getElementById("termsText");  //variable to describe the text next to the checkbox for terms
var btnSumbit = document.getElementsByName("submit")[0]; //variable to describe the submit button, the first element of the nodelist
var chkbxTerms = document.getElementsByName("termsChkbx")[0]; //variable to describe the terms checkbox, the first element of the nodelist
var chkbxEvents = document.getElementsByName("event[]"); //variable to describe all of the event price checkboxes, the whole nodelist is used
var eventNum = document.querySelectorAll('input[name="event[]"]').length; //variable to determine how many events there are for iteration checks
var fldForename = document.getElementsByName("forename")[0]; //variable to describe the firstname field, the first element of the nodelist
var fldSurname = document.getElementsByName("surname")[0]; //variable to describe the surname field, the first element of the nodelist
var fldCompany = document.getElementsByName("companyName")[0]; //variable to describe the company field, the first element of the nodelist
var divNames = document.getElementById("retCustDetails"); //variable to describe the names section
var divCompany = document.getElementById("tradeCustDetails"); //variable to describe the company section
divNames.style.visibility='hidden'; //hides the names section on startup to avoid confusion, terms must be checked and the type must be selected to make a section appear again
var form = document.getElementById('bookingForm'); //variable used to describe the whole form for an event listener
var formAction = document.getElementById("bookingForm"); //variable to describe the form action when sent, for javascript alert control
var radioHomeBtn = document.getElementsByName('deliveryType')[0]; //variable to describe the collection method, the first element of the nodelist
var radioOfficeBtn = document.getElementsByName('deliveryType')[1]; //variable to describe the collection method, the second element of the nodelist
var customerTypeBox = document.getElementsByName("customerType")[0];//variable to describe the collection method box, the second element of the nodelist
var boxPrice = 0; //a running total of all the selected events
var radioPrice = 0; //a total of the radio button values
var total; //variable to store the overal total, the variable we edit using the above.



//----------------------------------------------------------------------------------------------
// Total Price Management

//event listener to work out the total of every price and to output that to the user
form.addEventListener("change", function () {
			var i;
			for(i = 0; i <= eventNum-1; i++) { //-1 because arrays start at 0, checks if boxes are ticked
				if(chkbxEvents[i].checked == true) {
					var eventPrice = chkbxEvents[i].getAttribute('data-price'); //if ticked, get their price and store it
					boxPrice += (+eventPrice); //the + is used to add the strings as intergers rather than using string cocatination
					//add to current boxPrice to get running events total
				}
			}

			var radioHomePrice = radioHomeBtn.getAttribute('data-price');  //to reference against to see if checked
			var radioOfficePrice = radioOfficeBtn.getAttribute('data-price');	//to reference against to see if checked

			if(radioHomeBtn.checked == true) {
				var radioPrice = radioHomePrice;
			}
			else {
				var radioPrice = radioOfficePrice;
			}

			total = boxPrice + (+radioPrice); //the + is used to add the strings as intergers rather than using string cocatination
			boxPrice = 0; //reset to avoid values carrying over after each update
			radioPrice = 0; //reset to avoid values carrying over after each update
			document.getElementsByName("total")[0].value = total; //output to screen
});

//----------------------------------------------------------------------------------------------------------
//Field Input Check

//a function that checks if the company field is filled
function companyCheck()
{
	if(fldCompany.value.length == 0) {
		return false;
	}
	return true;
}

//a function that checks if the forename and surname fields are filled, if either are empty, it will fail
function nameCheck()
{
	if((fldForename.value.length == 0 || fldSurname.value.length == 0)) {  //check if empty
		return false;
	}
	return true;
}

//a function that checks if either of the required fields are entered since only the selected section is required
function fieldCheck()
{
	if(companyCheck() || nameCheck())
	{
		return true;
	}
	return false;
}

//a function that chceks if any of the boxes are ticked, since we need at least one for submission
function isBoxChecked()
{
	var i;
	var found;
	for(i = 0; i <= eventNum-1; i++) { //-1 because arrays start at 0
		if(chkbxEvents[i].checked == true) {
			found = true;
		}
	}
	if(found != true) {
		return i;  //workaround for wierd javascript conditionals
	}
}

//a function that checks if the customer type is picked, and hides or shows accordingly
function custTypeCheck()
{
	var selectedOption = customerTypeBox.options[customerTypeBox.selectedIndex].value;

	if(selectedOption == 'ret'){
		divNames.style.visibility='visible';
		divCompany.style.visibility='hidden';
		return true;
	}
	else if (selectedOption == 'trd') {
		divCompany.style.visibility='visible';
		divNames.style.visibility='hidden';
		return true;
	}
	else {
		alert("No Customer Type Selection!");
		return false;
	}
}

//-----------------------------------------------------------------------------------
//Other Startup Event Listeners

//an event listener that edits the form action to fit conditionals
btnSumbit.addEventListener("click", function()
{
   if(typeof isBoxChecked() == 'number' || fieldCheck() == false) {
		 formAction.action = "javascript:alert('Empty Fields!');";
   }
	 else {
	 	formAction.action = "javascript:alert('form submitted');";
	 }
});

//an event listener that changes terms text depending on whether the box is ticked
chkbxTerms.addEventListener("click", function()
{
	if(custTypeCheck())
	{
		if(chkbxTerms.checked == true){
			termsText.style.fontWeight = "normal";
			termsText.style.color = "black";
			btnSumbit.disabled = false;
		}
		else{
			termsText.style.fontWeight = "bold";
			termsText.style.color = "red";
			btnSumbit.disabled = true;
		}
	}
});

//an event listener that triggers the change of visability when the customer type box is changed
customerTypeBox.addEventListener("change", custTypeCheck);

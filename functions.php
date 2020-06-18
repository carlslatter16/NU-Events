<?php
ini_set("session.save_path", "/dirToSaveSessions/"); //we would want this in a secure place normally, anyone can view this
session_start(); //required to access session variables
set_exception_handler('exceptionHandler'); //by default use my handler program
$developMode = false;  //mode to select error feedback.

//---Event-Management--------------------------------------------------------------------------------------------------------------------------------------------------------------------------

//function to edit the database using posted variables, from the editForm.php form.
function editEvent()
{
	if(checkSession('logged-in') === false || checkEventParam() === false) //if we are not logged in, or if we have an empty get parameter
	{
		header("Refresh:0; url=index.php");  //place the user out of the edit page for security
	}

	try { //block to attempt
		if(isset($_POST['eventTitle'])) {  //if this variable is posted, then the rest will be due to the setup

			//I use temporary variables to store semi sanitized data, I trim it and check that it is not null
			$unFilteredTitle = trim(filter_has_var(INPUT_POST, 'eventTitle')
			? $_POST['eventTitle']: null);
			$unFilteredDescription = trim(filter_has_var(INPUT_POST, 'eventDescription')
			? $_POST['eventDescription']: null);
			$unFilteredVenue = trim(filter_has_var(INPUT_POST, 'activeVenue')
			? $_POST['activeVenue']: null);
			$unFilteredCategory = trim(filter_has_var(INPUT_POST, 'activeCategory')
			? $_POST['activeCategory']: null);
			$unFilteredStartDate = trim(filter_has_var(INPUT_POST, 'eventStartDate')
			? $_POST['eventStartDate']: null);
			$unFilteredEndDate = trim(filter_has_var(INPUT_POST, 'eventEndDate')
			? $_POST['eventEndDate']: null);
			$unFilteredPrice = trim(filter_has_var(INPUT_POST, 'eventPrice')
			? $_POST['eventPrice']: null);

			//I then create the 'real' variables and finish sanitization to avoid XSS. Tags can now no longer be used as input. I use filterhasvar with the parameter to avoid this, using the temp vars
			$eventTitle = filter_var($unFilteredTitle, FILTER_SANITIZE_STRING, 	FILTER_FLAG_NO_ENCODE_QUOTES);
			$eventDescription = filter_var($unFilteredDescription, FILTER_SANITIZE_STRING, 	FILTER_FLAG_NO_ENCODE_QUOTES);
			$venueName = filter_var($unFilteredVenue, FILTER_SANITIZE_STRING, 	FILTER_FLAG_NO_ENCODE_QUOTES);
			$catDesc = filter_var($unFilteredCategory, FILTER_SANITIZE_STRING, 	FILTER_FLAG_NO_ENCODE_QUOTES);
			$eventStartDate = filter_var($unFilteredStartDate, FILTER_SANITIZE_STRING, 	FILTER_FLAG_NO_ENCODE_QUOTES);
			$eventEndDate = filter_var($unFilteredEndDate, FILTER_SANITIZE_STRING, 	FILTER_FLAG_NO_ENCODE_QUOTES);
			$eventPrice = filter_var($unFilteredPrice, FILTER_SANITIZE_STRING, 	FILTER_FLAG_NO_ENCODE_QUOTES);


			if(fieldStatus($eventTitle, $eventDescription, $venueName, $catDesc, $eventStartDate, $eventEndDate, $eventPrice) === true) { //if fields are not null
				$eventQuery = $_GET["eventID"]; //fills variable with get parameter for use as a event primary key
				$dbConn = getConnection(); //simple connection

				$payload = [ //simple array to map PDO placeholders to actual data to be executed with the prepared statement, to change the database
					'title' => $eventTitle,
					'description' => $eventDescription,
					'category' => $catDesc,
					'venue' => $venueName,
					'startdate' => $eventStartDate,
					'enddate' => $eventEndDate,
					'price' => $eventPrice,
					'id' => $eventQuery,
				];

				$sqlEditQuery = "UPDATE NE_events
												 INNER JOIN NE_category
														ON (NE_events.catID = NE_category.catID)
												 INNER JOIN NE_venue
														ON (NE_events.venueID = NE_venue.venueID)
												 SET NE_events.eventTitle=:title,
														 NE_events.eventDescription=:description,
														 NE_category.catDesc=:category,
														 NE_venue.venueName=:venue,
														 NE_events.eventStartDate=:startdate,
														 NE_events.eventEndDate=:enddate,
														 NE_events.eventPrice=:price
												 WHERE NE_events.eventID =:id ";
												 //inner join to change all the tables of the relevant row, all using placeholders that are mapped above. EventID is used to select the desired row to change


					$stmt=$dbConn->prepare($sqlEditQuery); //prepare the SQL statement
					$stmt->execute($payload); //add the data payload seperatly and execute

				fetchTable($eventQuery); //fetch table after to show update giving the used eventID that is changed
			}
			else {
				throw new Exception("Invalid Data!"); //custom exception that will trigger the catch statement in case something is wrong
			}
		}
	}
	catch (Exception $e) { //error handler in case something is wrong
			exceptionHandler($e);
	}
}


function generateLists($prevCategory, $prevVenue )  //dynamically generates the cateory and venue select boxes, has parametes for the current selected item in each to set it as such
{
	try {
		$formContent=""; //initilize variable
		$dbConn = getConnection();

		$categoryQuery = "SELECT *
									 FROM NE_category"; //cateory query
		$venueQuery = "SELECT *
									 FROM NE_venue"; //venue query

		$getCategories = $dbConn->query($categoryQuery); //two different connection queries, for two different iteration loops
		$getVenues = $dbConn->query($venueQuery);



		$formContent .= "	<label class='formElements'>Category: </label><select name='activeCategory' class='formElements'>"; //sets up label and box
		while($row = $getCategories->fetchObject()) //iterate through each category row
		{
			if($prevCategory === $row->catDesc)  //if the selected category is the same as the one found in the database
			{
				//mark it as selected upon page load
				$formContent .= <<<OPTIONS
					<option value="$row->catDesc" selected>$row->catDesc</option>
OPTIONS;
			}
			else {
				//normal category option creation for those not selected by placeholder
				$formContent .= <<<OPTIONS
					<option value="$row->catDesc">$row->catDesc</option>
OPTIONS;
			}
		}

		$formContent .= "</select><br>"; //end category select box
		$formContent .= "<label class='formElements'>Venue: </label> <select name='activeVenue' class='formElements'>";  //sets up label and box

		while($row = $getVenues->fetchObject()) //iterate through each venue row
		{
			if($prevVenue === $row->venueName) 	//if the selected venue is the same as the one found in the database
			{
				//mark it as selected upon page load
				$formContent .=	<<<OPTIONS
					<option value="$row->venueName" selected>$row->venueName</option>
OPTIONS;
			}
			else {
				//normal venue option creation for those not selected by placeholder
				$formContent .=	<<<OPTIONS
					<option value="$row->venueName">$row->venueName</option>
OPTIONS;
			}
		}

		$formContent .= "</select><br>"; //end venue select box
		return $formContent; //return box select boxes with selected option cross referenced with the database
	}
	catch (Exception $e) { //if there any errors, catch them and handle them
		exceptionHandler($e);
	}
}

//simple function to check if any of the fields are empty - if the given values are null, they are not filled
function fieldStatus($eventTitle, $eventDescription, $venueName, $catDesc, $eventStartDate, $eventEndDate, $eventPrice)
{
	$fieldsToCheck = [$eventTitle, $eventDescription, $venueName, $catDesc, $eventStartDate, $eventEndDate, $eventPrice];  //creates an array for easy iteration

	foreach($fieldsToCheck as $i){ //for each entry in the fields array
		if(empty($i)){ //if the iterated option is empty
				return false;  //then return false because there is at least 1 empty field, which we cannot have.
		}
	}
	return true; //let the page know there are no empty fields
}

//function to create form that is used to pass data to the function designed to edit the database
function editForm()
{
	if(!checkSession('logged-in') || checkEventParam() === false) { //if not logged in or there is no eventid parameter
		header("Refresh:0; url=index.php");  //send the user back to the homepage for security
	}
	try { //try this block
				$eventQuery = $_GET["eventID"];  //store eventid get parameter in variable
				$dbConn = getConnection(); //connection

				$sqlQuery = "SELECT * FROM NE_events
												INNER JOIN NE_category ON NE_category.catID = NE_events.catID
												INNER JOIN NE_venue ON NE_venue.venueID = NE_events.venueID
												WHERE eventID=:id";  //edit joined tables using given id, as a placeholder

				$result=$dbConn->prepare($sqlQuery);  //prepare statement
				$result->execute(['id'=>$eventQuery]); //execute query with placeholder assigned to variable to avoid sql injection

				$formContent= ""; //initilize variable

				while($row = $result->fetchObject())
				{
					//create first part of the form, this has to be split due to function usage, I add placeholders using the database query row object for variables
					$formContent .= <<<EDITFORM
						<h2>Edit Event - $row->eventTitle</h2>
						<form method=	"post" id='editEventForm'>
							<label class='formElements'>Event Title: </label>
							<input type="text" value="$row->eventTitle" name="eventTitle" class='formElements' required>
							<br>
EDITFORM;
						//append variable with select boxes, I pass two parameters to show the current selected option also
						$formContent .= generateLists($row->catDesc, $row->venueName);

						//create more labels and inputs. I create a wrapper for textarea for easy centering for the text
						$formContent .= <<<EDITFORM
							<label class='formElements'>Start Date: </label>
							<input type="text" value="$row->eventStartDate" name="eventStartDate" class='formElements' required>
							<br>
							<label class='formElements'>End Date: </label>
							<input type="text" value="$row->eventEndDate" name="eventEndDate" class='formElements' required>
							<br>
							<label class='formElements'>Price: </label>
							<input type="text" value="$row->eventPrice" name="eventPrice" class='formElements' required>
							<br>
							<div id='textAreaCenter'>
								<label class='formElements'>Description: </label>
								<textarea name="eventDescription" class='formElements' rows="3" required>$row->eventDescription</textarea>
							</div>
							<br>
							<button type="submit" >Submit</button>
						</form>
EDITFORM;
				}
				echo $formContent; //prints form
				editEvent(); //creates the listener for the from values to be posted, and to handle them to edit the database
		}
		catch (Exception $e) { //catch in case of error, and handle
			exceptionHandler($e);
		}
}

function checkEventParam()
{
		if($_GET["eventID"] === null) //if there is no eventID in the URL
		{
			return false;
		}
		return true;
}
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------

//---Session-Management------------------------------------------------------------------------------------------------------------------------------------------------------------------------

function credCheck()
{
	try {
		$dbConn = getConnection();  // Make a database connection

		/* Query the users database table to get the password hash for the username entered by the user, using a PDO named placeholder for the username */
		$querySQL = "SELECT passwordHash FROM NE_users
		WHERE username = :user";

		$username = filter_has_var(INPUT_POST, 'user') //checks the value is not null
		? $_POST['user']: null;
		$password = filter_has_var(INPUT_POST, 'pass')  //checks the value is not null
		? $_POST['pass']: null;

		// Prepare the sql statement using PDO
		$stmt = $dbConn->prepare($querySQL);

		// Execute the query using PDO
		$stmt->execute(array(':user' => $username));

		//queries for a row match with above sql for the given user
		$userFound = $stmt->fetchObject();

		//if user is found in the database
		if ($userFound != false)
		{
			$passwordHash = $userFound->passwordHash; //the password hash is the users hash from the database, store it in a variable
			if(password_verify($password, $passwordHash)) //if entered password and hashed value match up
			{
				return true; //login is successul, we return true for credential check
			}
		}
		echo "Your username or password was incorrect!"; //generalised error for security
		return false; //credential check was unsucessful
	}
	catch (Exception $e) { //catch and handle if error
		exceptionHandler($e);
	}
}

//a function to create a session with given value, and given user - we tend to use 'logged-in' and verify if it is set, we also pass it the username for a welcome message later
function setSession($loginStatus, $user)
{
	$_SESSION[$loginStatus] = true;  //creates a session variable that indicates we are logged in
	$_SESSION["firstname"] = $user; //creates a session variable to indicate which user is logged in
}

//a function to check we are logged in by passing the 'logged-in' parameter
function checkSession($loginStatus)
{
	if (isset($_SESSION[$loginStatus]))
	{
			return true; //the session is active, we are logged in
	}
			return false; //the session is not active, we are not logged in
}

//a function to end the session when we logout, for security and functionality
function endSession()
{
	session_destroy();  //destroys all session variables
}

//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------


//---Page Generation-----------------------------------------------------------------------------------------------------------------------------------------------------------------------

//simple heredoc to print the start of every page, while including the css file used for every page, a modular function with variables
function makePageStart($title, $cssFile)
{
	$pageStartContent = <<<PAGESTART
	<!doctype html>
	<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>$title</title>
		<link href="$cssFile" rel="stylesheet" type="text/css">
	</head>
	<body>
	<div id="gridContainer">
PAGESTART;
	$pageStartContent .="\n";
	return $pageStartContent;
}


function makeHeader($header1)
{
	if(!checkSession("logged-in")) //if not logged in, show login form using heredoc
	{
		$headContent =
		<<<HEAD
			<header>
				<h1>$header1</h1>
				<form method="post">

					<div class="container">
						<label><b>Username: </b></label>
						<input type="text" placeholder="Enter Username" name="user" required>
						<label><b>Password: </b></label>
						<input type="password" placeholder="Enter Password" name="pass" required>
						<button type="submit" >Login</button>
					</div>
				</form>
			</header>
HEAD;
	}
	else  //if logged in, only show logout button and welcome message, again using heredoc
	{
		$headContent =
		<<<HEAD
			<header>
				<h1>$header1</h1>
				<form method="post">
				<div class="container">
					<button type="submit" name="logout" >Logout</button>
					<p>Welcome $_SESSION[firstname]</p>
				</div>
				</form>
			</header>
HEAD;
	}

	if(isset($_POST['logout'])) //if logout button is used
	{
		endSession();  //end session and send the user to the default privilage page
		header("Refresh:0; url=index.php");
	}

	if(isset($_POST['user']) && isset($_POST['pass']) && (credCheck() === true))  //if username and password is given, and they are correct
	{
			setSession('logged-in', $_POST['user']);   //create session for memory, with the username given
			header("Refresh:0; url=listEvents.php");	 //send the user to updated listevents page with hyperlinks
	}

	return $headContent; //return heredoc to be then echoed, to create the page
}

function makeNavMenu(array $links)  //links of indexed pages
{
  $navMenuContent = "<nav><ul id='navRow'>"; //creates nav and navbar row

  foreach($links as $link=>$displayValue)  //a dynamic way to pass a link and name to be used for that link, iterating and creating based on the given array
  {
    $navMenuContent.="<li><a href='$link'>$displayValue</a></li>";  // append variable with new link and name
  }
		$navMenuContent .='</ul></nav>'; //end row and nav element by appending

	return $navMenuContent; //return for navbar to be echoed out on each page
}

function startMain()
{
    return "<main>\n";  //simply creates main tag to be echoed out
}

//simple function that has homepage content, text, images and wrappers for flex boxes and structure
function mainContent()
{
	$mainBlock =
	<<<MAIN
		<div id=mainBlock>
			<section id=firstBlock>
				<p id=mainPara>
					North Events brings people together through live experiences. Discover events that match your passions,
					or create your own with online ticketing tools. North Events is a global platform for live experiences
					that allows anyone to create, share, find and attend events that fuel their passions and enrich their lives.
					From music festivals, marathons, conferences, community rallies and fundraisers, to gaming competitions and air guitar contests.
					Our mission is to bring the world together through live experiences. Please have a look at what is upcoming, as well as our special offers!
				</p>
				<div id='imageSection'>
					<img src="https://ents24.imgix.net/image/000/330/887/e0a7b7099a7df1a33739427c0113e142397e668f.jpg?auto=format&fit=crop&crop=entropy&w=1024&h=600&vib=50&q=50" alt="bandEventImage" class='eventImages'>
					<img src="https://www.newcastlefalcons.co.uk/Content/images/img_welcome_3.jpg" alt="foodEventImage" class='eventImages'>
					<img src="https://www.utilitaarena.co.uk/images/fw_20151008220606_1779.jpg" alt="comicEventImage" class='eventImages'>
				</div>
			</section>
MAIN;

		return $mainBlock; //return heredoc
}

function offerBlock() //heredoc function to setup offer structure, with the specified IDs, the div is simply for wrapping of the index.php
{
	$offerBlock	=
	<<<OFFER

		<section id='offerBlock'>
			<h3>Special Offer Rotation</h3>
			<aside id='offers'></aside>
			<h3>Exclusive Special Offer</h3>
			<aside id='XMLoffers'></aside>
		</section>
	</div>
OFFER;

 return $offerBlock;  //return offers to the user

}

//a function to get database rows based on selection or lack of.
function fetchTable($selection = null) //default value if eventID was not specified, used to either show the full database, or a single row
{
	try { //for error mitigation
		$dbConn = getConnection(); //store database connection in a variable
		$sqlQuery = "SELECT * FROM NE_events
								INNER JOIN NE_category ON NE_category.catID = NE_events.catID
								INNER JOIN NE_venue ON NE_venue.venueID = NE_events.venueID
								ORDER BY eventTitle";  //default SQL query, this will get every row

		$result = $dbConn->query($sqlQuery); //query database and store

		if($selection != null) { 	//if the scope of query was given to function call
			$eventQuery = $_GET["eventID"];  //set eventQuery variable to be get parameter
			$sqlQuery = "SELECT * FROM NE_events
									 INNER JOIN NE_category ON NE_category.catID = NE_events.catID
									 INNER JOIN NE_venue ON NE_venue.venueID = NE_events.venueID
									 WHERE eventID=:id"; //updated query to get event row, using a placeholder value for security

			$result = $dbConn->prepare($sqlQuery);  //overwrite previous query connection, using prepared statment because of parameter,
																							//could be susceptible to SQL injection otherwise

			$result->execute(['id'=>$eventQuery]);  //executing prepared query while filling in the placeholder
		}

		echo '<table><tr><th>EventID</th><th>Event Title</th><th>Venue Name</th><th>Category</th><th>Event Start Date</th><th>Event End Date</th><th>Event Price</th></tr>';
		//setting up table row for titles

		if(checkSession('logged-in') == true) //if logged in, show hyperlink
		{
			while($row = $result->fetchObject()) //iterate through database rows
			{
				echo '<tr>';
					echo '<td>'.$row->eventID.'</td>';  //access row object and get variable, same for all below, putting it in data fields
					echo '<td><a href=editEvent.php?eventID='.$row->eventID.'>'.$row->eventTitle.'</td>'; //using href and passing eventid and get parameter
					echo '<td>'.$row->venueName.'</td>';
					echo '<td>'.$row->catDesc.'</td>';
					echo '<td>'.$row->eventStartDate.'</td>';
					echo '<td>'.$row->eventEndDate.'</td>';
					echo '<td>'.$row->eventPrice.'</td>'; /**catDesc is usable due to the join above to link the two fields in the two tables**/
				echo '</tr>';
			}
		}
		else {
			while($row = $result->fetchObject())
			{
				echo '<tr>';
					echo '<td>'.$row->eventID.'</td>';  //access row object and get variable, same for all below, putting it in data fields
					echo '<td>'.$row->eventTitle.'</td>';
					echo '<td>'.$row->venueName.'</td>';
					echo '<td>'.$row->catDesc.'</td>';
					echo '<td>'.$row->eventStartDate.'</td>';
					echo '<td>'.$row->eventEndDate.'</td>';
					echo '<td>'.$row->eventPrice.'</td>';
				echo '</tr>';
			}
		}

		echo '</table>'; //ends table after everything
	}
	catch (Exception $e) {  //if any error, catch it and handle is using the given function, passing it the exception
		exceptionHandler($e);
	}
}

//a function that ends the main block
function endMain()
{
		return "</main>\n";
}

//a function to end the page using return value with HTML tags
function makePageEnd()
{
		return "</div>\n</body>\n</html>"; //ends gridContainer, body and the actual page
}
//-----------------------------------------------------------------------------------------------------------------------------------------------------------------------

//a function to setup database connection using PDO driven mysql. I give it credentials and it returns connection that we can store in a variable and access the database
function getConnection()
{
  try {
    $connection = new PDO("mysql:host=dbhost;dbname=dbname","username", "password");
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	  return $connection;
  }
  catch (Exception $e)
  {
  	exceptionHandler($e);
  }
}

//a function to handle error output depending on access mode
function exceptionHandler($e)
{
	if(defined("developMode") && $developMode === true)
	{
		throw new Exception("Runtime error: ". $e->getMessage(), 0, $e);
		echo "A Problem occurred: ".$e->getMessage(); //more detailed error message, while logging for admin
		log_error($e);
	}
	else {
		echo "Something Went Wrong!";   //generic error message
	}
}
?>

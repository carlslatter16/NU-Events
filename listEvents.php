<?php //I echo functions that return heredoc or HTML data and pass information to create dynamic webpages

	require_once('functions.php'); //functions location

	echo makePageStart("North Events Admin Page", "style.css");
  echo makeHeader("Events Page");
  echo makeNavMenu(array("index.php" => "Home", "bookEventsForm.php" => "Book An Event", "listEvents.php" => "Edit An Event", "credits.php" => "Credits"));
  echo startMain();
 	fetchTable();
  echo endMain();
  echo makePageEnd();
?>

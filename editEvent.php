<?php //I echo functions that return heredoc or HTML data and pass information to create dynamic webpages
	require_once('functions.php');  //functions location

	echo makePageStart("North Events", "style.css");
  echo makeHeader("Edit Event");
  echo makeNavMenu(array("index.php" => "Home", "bookEventsForm.php" => "Book An Event" ,"listEvents.php" => "Edit An Event", "credits.php" => "Credits" ));
  echo startMain();
  echo editForm();
  echo endMain();
  echo makePageEnd();
?>

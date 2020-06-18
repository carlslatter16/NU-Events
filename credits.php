<?php //I echo functions that return heredoc or HTML data and pass information to create dynamic webpages
  require_once('functions.php'); //functions location

  echo makePageStart("North Events", "style.css");
  echo makeHeader("Image & Text Credits");
  echo makeNavMenu(array("index.php" => "Home", "bookEventsForm.php" => "Book An Event" ,"listEvents.php" => "Edit An Event", "credits.php" => "Credits" ));
  echo startMain();
?>

<article id="creditinfo"> <!--Used for styling later-->

					<h3>Creditation</h3>

					<p> <!--Sourcing info-->
						I have linked the text I have used, with minor changes for context. I have also attatched links to images used on the homepage with harvard referencing. In terms of the code used, some
            creative logic was used to truncate some repeated code, for loops from arrays etc..
					</p>

					    <!--Links to the pages these are from, altered or not. I included harvard referencing in the format of url, site filename, website name and year uploaded/website founded-->
            <a href="https://www.eventbrite.co.uk">Sample Event Site Text (Changed for context) ~ Eventbrite: Eventbrite.co.uk [Online] (2018) (Accessed: 05 December 2019)</a><br>
	  				<a href="https://www.ents24.com/whatson//near/newcastle-upon-tyne">Band Image On Homepage ~ Newcastle upon Tyne Events: Ents24.com [Online] (2019) (Accessed: 05 December 2019)</a><br>
						<a href="https://www.newcastlefalcons.co.uk/Forms/Conference/Welcome">Restaurant Image On Homepage ~ Conference And Events: Newcastlefalcons.co.uk [Online] (2019) (Accessed: 05 December 2019)</a><br>
	  				<a href="https://www.utilitaarena.co.uk/events/newcastle-film-comic-con/">Comic-Con Image On Homepage ~ Newcastle Film & Comic Con: Utilitaarena.co.uk [Online] (2019) (Accessed: 05 December 2019)</a><br>

</article>
<?php
  echo endMain();
  echo makePageEnd();
?>

<?php 
/**
 * This is a Alpha pagecontroller.
 *
 */
// Include the essential config-file which also creates the $alpha variable with its defaults.
include(__DIR__.'/config.php'); 

// Do it and store it all in variables in the Alpha container.
$alpha['title'] = "Welcome to Rental Movies";

$alpha['main'] = NULL; 
$alpha['main'] .= "<article class='wrapper bg-grey'>
	<h2>Rental Movies</h2>
	<p>So you would like to know more about Rental Movies? Do you want to know a secret? It is very secret though, do you promise to not tell anyone?</p>
	
	<p>Hmm, okey, you seem like a trustworthy person... come closer...</p>
	
	<p>...we do not have any movies you can rent, none whatsoever!</p>
	
	<p>This website was created by Lenny Erlesand in 2015 as the final project of the course OOPHP at Blekinge Tekniska Högskola</p>
</article>";
#$alpha['main'] .= "<article class='wrapper bg-light'>"; 
#$alpha['main'] .= CNavigation::loadPage();
#$alpha['main'] .= "</article>";

// Finally, leave it all to the rendering phase of Alpha.
include(ALPHA_THEME_PATH);
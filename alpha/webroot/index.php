<?php 
/**
 * This is a Alpha pagecontroller.
 *
 */
// Include the essential config-file which also creates the $alpha variable with its defaults.
include(__DIR__.'/config.php'); 


// Add style for csource
$alpha['stylesheets'][] = 'css/source.css';


$alpha['title'] = "Welcome to Movie Rentals";

$alpha['header'] = <<<EOD
<a href="login.php" style="float: right">Login</a>
<h1>{$alpha['title']}</h1>
EOD;

$alpha['popular'] = '<div id="most-popular">
	<img class="label" src="img/most_popular.png" style="position: absolute; width: 20%; max-width: 200px">
	<img class="image" src="img/iron_man.jpg" style="width: 100%" alt="Most Popular Movie" title="Most Popular Movie: Iron Man">
	<span class="title">Iron man 3</span>
</div>'; 

$alpha['main'] = "<article class='wrapper bg-dark'>This is a test page</article>";
$alpha['main'] .= "<article class='wrapper bg-light'>This is another test</article>"; 
$alpha['main'] .= "<article class='wrapper bg-dark'>Tests are great for creating an understanding.</article>";

// Finally, leave it all to the rendering phase of Alpha.
include(ALPHA_THEME_PATH);
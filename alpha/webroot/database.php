<?php 
/**
 * This is a Alpha pagecontroller.
 *
 */
// Include the essential config-file which also creates the $alpha variable with its defaults.
include(__DIR__.'/config.php'); 

$db = new CDatabase($alpha['database']); 

// Do it and store it all in variables in the Alpha container.
$alpha['title'] = "Databasen";

$alpha['main'] = '<article class="justify border" style="width: 80%;">
			<h1>MYSQL DATABAS MED PDO</h1>'; 
 

$sql = "SELECT * FROM Movie";
$result = $db->ExecuteSelectQueryAndFetchAll($sql); 

$alpha['main'] .= "<p>Resultatet från SQL-frågan: <code>$sql</code>"; 

$alpha['main'] .= "<table>
	<thead>
		<tr>
			<th>Rad</th>
			<th>Id</th>
			<th>Bild</th>
			<th>Titel</th>
			<th>År</th>
		</tr>
	</thead>
	<tbody>";

foreach ($result as $row => $obj)
{
	$alpha['main'] .= "	<tr>
		<td>$row</td>
		<td>{$obj->id}</td>
		<td><img width='120' height='60' src='{$obj->image}' alt='{$obj->title}'></td>
		<td>{$obj->title}</td>
		<td>{$obj->year}</td>
	</tr>"; 
}

$alpha['main'] .= '</tbody></table></article>'; 

// Finally, leave it all to the rendering phase of Alpha.
include(ALPHA_THEME_PATH);
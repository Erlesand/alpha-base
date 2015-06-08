<?php 
class CMovieSearch 
{
	public function show() 
	{
		global $db; 
		
		$sql = "SELECT DISTINCT G.name FROM Genre AS G INNER JOIN Movie2Genre AS M2G ON G.id = M2G.idGenre"; 
		$genres = $db->ExecuteSelectQueryAndFetchAll($sql); 
				
		$genreSelect = "<option value=''>Välj genre &#9662;</option>";
		foreach ($genres as $i => $genre)
		{
			$genreSelect .= "<option value='{$genre->name}' ".(isset($_GET['genre']) && $_GET['genre'] == $genre->name ? 'selected="selected"' : "").">{$genre->name}</option>\n";
		}

		$sql = "SELECT MIN(year) AS min, MAX(year) AS max FROM VMovie"; 		
		$period = $db->ExecuteSelectQueryAndFetchAll($sql); 
		if (!isset($_GET['start'])) $_GET['start'] = $period[0]->min; 
		if (!isset($_GET['end'])) $_GET['end'] = $period[0]->max; 
		
		if (!isset($_GET['title'])) $_GET['title'] = NULL; 
		if (!isset($_GET['genre'])) $_GET['genre'] = NULL; 
		
		$selectStart = "";
		for ($i = $period[0]->min; $i <= $period[0]->max; $i++)
			$selectStart .= "<option value='$i' ".(isset($_GET['start']) && $_GET['start'] == $i ? 'selected="selected"' : "").">$i</option>\n";
			
		$selectEnd = "";
		for ($i = $period[0]->min; $i <= $period[0]->max; $i++)
			$selectEnd .= "<option value='$i' ".(isset($_GET['end']) && $_GET['end'] == $i ? 'selected="selected"' : "").">$i</option>\n";

	
		$html = NULL; 
		
		$html .= '<fieldset>
				<form method="GET">
				<p>Fyll i den filmtitel du vill söka efter.</p>'; 
				
		$html .= '<p class="row">
					<label>Titel</label>
					<input type="text" name="title" value="'.$_GET['title'].'">
				</p>'; 
				
		$html .= '<p class="row"> 
					<label>Genre</label>
					<select name="genre" class="last" style="width: 216px; text-align: right">'.
					$genreSelect
					.'</select>
				</p>'; 
	 
		$html .= '<p class="row">
			<label>Period</label>
			<select name="start">'.
			$selectStart
			.'</select>
			<span class="separator">-</span>
			<select name="end" class="last">'.
			$selectEnd
			.'</select>
		</p>'; 
		
		$html .= '<input class="search full" type="submit" value="Sök">'; 
			
		$html .= '</form>
				</fieldset>'; 
				
		return $html;
	}
	
	public function Query($params = NULL) 
	{	
		global $db; 
		
		$title 	= (isset($_GET['title']) ? $_GET['title']."%" : "%"); 
		$genre 	= (isset($_GET['genre']) ? $_GET['genre'] : NULL);

		$sort 	= (isset($_GET['sort']) ? $_GET['sort'] : NULL);
		$order 	= (isset($_GET['order']) ? $_GET['order'] : 'ASC');
		
		$hits = (isset($_GET['hits']) && is_numeric($_GET['hits']) ? $_GET['hits'] : 5);
		$page = (isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1);
 			
 		
 		$sql = "SELECT * FROM `VMovies` WHERE 1 AND title LIKE ?"; 
		$params = array($title);

		if ($genre) { 
			$sql .= " AND genre LIKE ?";
			$params[] = "%".$genre."%"; 
		}
	 
		if ($sort)
			$sql .= " ORDER BY $sort $order";
			
		// Add pagination syntax. 
		$sql .= "  LIMIT $hits OFFSET " . (($page - 1) * $hits); 
	
		$result = $db->ExecuteSelectQueryAndFetchAll($sql, $params); 
		
		return $result; 
		
		#$start = (!isset($_GET['start']) ? $period[0]->min : $_GET['start']); 
		#$end = (!isset($_GET['end']) ?  $period[0]->max : $_GET['end']);
		
		#return print_r(array($title, $start, $end), TRUE); 
	}
}
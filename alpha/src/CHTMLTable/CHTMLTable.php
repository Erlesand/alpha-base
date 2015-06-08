<?php 
class CHTMLTable 
{
		
		/* PrintTable is used to print the result of a SQL query in a HTML table. 	*/
			
	public function PrintTable($table, $result, $options = array(),  $showRow = FALSE)
	{
		$html = ""; 
		
		if (isset($options['pagination']))
		{
			$html .= $this->Pagination("hits", $options['pagination']); 
		}
		
		$html .= "<table><thead><tr>"; 
		
		if ($showRow) $html .= "<th>Rad</th>";
		
		foreach ($table as $head => $body)
		{
			$class = (isset($body['class']) ? ' class="'.$body['class'].'"' : NULL);
			if (isset($body['sort']))
			{
				if (isset($_GET['sort']) && $_GET['sort'] == $body['sort'])
					$order = (isset($_GET['order']) && $_GET['order'] == 'ASC' ? "DESC" : "ASC"); 
				else
					$order = "ASC";
				
				
				if (isset($_GET['sort']) && $body['sort'] == $_GET['sort'])
					$arrow = "59";
				else
					$arrow = "67"; 
					
					
				$html .= "<th $class><a href='".getQuerystring(array("sort" => $body['sort'], "order" => $order))."'>$head</a>&nbsp;".($order == 'ASC' ? "&#8{$arrow}3;" : "&#8{$arrow}5;")."</th>";
			}
			else
				$html .= "<th $class>$head</th>";
		}
		
		$html .= "</tr></thead>";
		
		$html .= "<tbody>";
		
		foreach ($result as $row => $obj)
		{
			$html .= "<tr>";
			if ($showRow)
				$html .= "<td>$row</td>";
				
			foreach ($table as $body)
			{ 
				$tbl_row = NULL; 
				$class = (isset($body['class']) ? " class='".$body['class']."'" : NULL); 
				if (isset($body['type']) && $body['type'] == 'img')
				{
					$tbl_row .= "<img ".$body['img']['tag']." src='img.php?src=".(isset($body['img']['folder']) ? $body['img']['folder'] : "").$obj->{$body['img']['src']}."' alt='".$obj->{$body['img']['alt']}."'>";
				}	
				else if (isset($body['type']) && $body['type'] == 'keyword')
				{

					$separator = (isset($body['separator']) ? $body['separator'] : ','); 
					$list = explode($separator, $obj->{$body['column']});
					foreach ($list as $keyword)
						if (!empty($keyword))
							$tbl_row .= "<span class='keyword'>$keyword</span>";
							
				}
				else if (isset($body['type']) && $body['type'] == 'link')
				{
					$tbl_row .= "<a href='".getQueryString(array("slug" => $obj->slug, "p" => $body['page']))."'>".$obj->$body['column']."</a>";
				}
				else if (isset($body['type']) && $body['type'] == 'edit')
				{
					if (isset($body['link']))
					{
						$link = array(); 
						foreach ($body['link']['query'] as $key => $qs)
						{
							if (substr($qs, 0, 1) == '_')
								$link[$key] = $obj->{substr($qs,1)}; 
							else
								$link[$key] = $qs; 
						}
						$tbl_row .= "<a href='".$body['link']['href'].getQueryString($link)."'>✎</a>";
					}
					else if (isset($body['page']))
						$tbl_row .= "<a href='".getQueryString(array("id" => $obj->id, "p" => $body['page']))."'>✎</a>";
					else
						$tbl_row .= "<a href='".getQueryString(array("id" => $obj->id))."'>✎</a>";
				}
				else if (isset($body['type']) && $body['type'] == 'delete')
				{
					if (isset($body['link']))
					{
						$link = array(); 
						foreach ($body['link']['query'] as $key => $qs)
						{
							if (substr($qs, 0, 1) == '_')
								$link[$key] = $obj->{substr($qs,1)}; 
							else
								$link[$key] = $qs; 
						}
						$tbl_row .= "<a href='".$body['link']['href'].getQueryString($link)."'>╳</a>";
					}
					else if (isset($body['page']))
						$tbl_row .= "<a href='".getQueryString(array("id" => $obj->id, "p" => $body['page']))."'>╳</a>";
					else
						$tbl_row .= "<a href='".getQueryString(array("id" => $obj->id))."'>╳</a>";
				}
				else if ($body['column'])
				{
					if (strlen(htmlentities($obj->{$body['column']})) > 200)
						$tbl_row .= substr(htmlentities($obj->{$body['column']}), 0, 200).'...';
					else
						$tbl_row .= htmlentities($obj->{$body['column']});
				}
				else 
					$tbl_row .= print_r($body, TRUE);
				
				
				if (isset($body['link']))
				{
					$tmp = "<a href='{$body['link']['href']}"; 
					if (isset($body['link']['query']))
					{
						$tmp .= "?"; 
						foreach ($body['link']['query'] AS $key => $value)
						{
							if (substr($value, 0, 1) == '_')
								$tmp .= $key . "=" . $obj->{substr($value, 1)} . "&"; 
							else
								$tmp .= $key . "=" . $value . "&"; 
						}
						
						$tmp = substr($tmp, 0, -1);
					}
					$tbl_row = $tmp . "'>$tbl_row</a>";
					#$tbl_row = "<a href='{$body['link']['href']}'>$tbl_row</a>";
				}
				$html .= "<td{$class}>".$tbl_row."</td>";
			}
			$html .= "</tr>";
		
		}
		
		$html .= "</tbody>";
		
		$html .= "</table>";
		
		if (isset($options['pagination']))
		{
			$html .= $this->Pagination("pages", $options['pagination']); 
		}
		
		/*	<tbody>";
		
		foreach ($result as $row => $obj)
		{
			$html .= "	<tr>
				<td>$row</td>
				<td>{$obj->id}</td>
				<td><img width='120' height='60' src='{$obj->image}' alt='{$obj->title}'></td>
				<td>{$obj->title}</td>
				<td>{$obj->year}</td>
			</tr>"; 
		}
		
		$html .= '</tbody></table></article>'; */
		return $html; 
	}

	private function Pagination($type, $options)
	{
		if ($type == "hits")
		{
			#return "<p>".print_r($options, TRUE)."</p>";
			$html = "<p class='pagination hits'><span class='pagination label'>Träffar per sida</span>"; 
			 foreach ($options['hits'] AS $hit)
			 	$html .= "<a href='".getQueryString(array("hits" => $hit))."' class='pagination hit".(isset($options['hit']) && $options['hit'] == $hit ? " current" : "")."'>$hit</a>"; 
			 $html .= "</p>"; 
			 
			 return $html;
		}
		
		if ($type == "pages")
		{
			$_GET['page'] = (isset($_GET['page']) ? $_GET['page'] : 1);
			
			$html = "<p class='pagination pages'>";
			
			// First and previous 
			$visibility = (!isset($_GET['page']) || $_GET['page'] == 1 ? 'invisible' : NULL);
			$page = (!isset($_GET['page']) ? 1 : $_GET['page'] - 1);
			
			$html .= "<span class='pages first $visibility'><a href='".getQueryString(array("page" => 1))."'>Första</a></span>";
			$html .= "<span class='pages previous $visibility'><a href='".getQueryString(array("page" => $page))."'>Föregående</a></span>";
			
			
			// List all pages
			$html .= "<span class='pages list'>";
			for ($i = 1; $i <= ceil($options['max'] / $options['hit']); $i++)
				$html .= "<a href='".getQueryString(array("page" => $i))."' ".($i == $options['page'] ? 'class="pages current"' : "class='pages'").">$i</a>";
			$html .= "</span>";
			
			// Next and last 
			
			$visibility = (isset($_GET['page']) && $_GET['page'] == ceil($options['max'] / $options['hit']) ? 'invisible' : NULL); 
			$html .= "<span class='pages last $visibility'><a href='".getQueryString(array("page" => ceil($options['max'] / $options['hit'])))."'>Sista</a></span>";
			$page = (!isset($_GET['page']) ? 2 : min($_GET['page'] + 1, ceil($options['max'] / $options['hit'])));
			$html .= "<span class='pages next $visibility'><a href='".getQueryString(array("page" => $page))."'>Nästa</a></span>";


			$html .= "</p>";
			
			return $html; 
		}
	}
}
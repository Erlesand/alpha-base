<?php 
/**
 * Database wrapper, provides a database API for the framework but hides details of implementation.
 *
 */
class CDatabaseTest {
 
	/**
	* Members
	*/
	private $options;                   // Options used when creating the PDO object
	private $db   = null;               // The PDO object
	private $stmt = null;               // The latest statement used to execute a query
	private static $numQueries = 0;     // Count all queries made
	private static $queries = array();  // Save all queries for debugging purpose
	private static $params = array();   // Save all parameters for debugging purpose
	
	
	/**
	* Constructor creating a PDO object connecting to a choosen database.
	*
	* @param array $options containing details for connecting to the database.
	*
	*/
	public function __construct($options) 
	{
		// Get debug information from session if any.
		if(isset($_SESSION['CDatabase'])) 
		{
			self::$numQueries = $_SESSION['CDatabase']['numQueries'];
			self::$queries    = $_SESSION['CDatabase']['queries'];
			self::$params     = $_SESSION['CDatabase']['params'];
			unset($_SESSION['CDatabase']);
		}
	
		$default = array(
			'dsn' => null,
			'username' => null,
			'password' => null,
			'driver_options' => null,
			'fetch_style' => PDO::FETCH_OBJ,
		);
		$this->options = array_merge($default, $options);
		
		try {
			$this->db = new PDO($this->options['dsn'], $this->options['username'], $this->options['password'], $this->options['driver_options']);
		}
		catch(Exception $e) {
			#throw $e; // For debug purpose, shows all connection details
			throw new PDOException('Could not connect to database, hiding connection details.'); // Hide connection details.
		}
		
		$this->db->SetAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $this->options['fetch_style']); 
	}
	
	/**
	* Getters
	*/
	public function GetNumQueries() { return self::$numQueries; }
	public function GetQueries() { return self::$queries; }
	
	
	/**
	* Get a html representation of all queries made, for debugging and analysing purpose.
	* 
	* @return string with html.
	*/
	public function Dump() {
		$html  = '<p><i>You have made ' . self::$numQueries . ' database queries.</i></p><pre>';

		foreach(self::$queries as $key => $val) {
			$params = empty(self::$params[$key]) ? null : htmlentities(print_r(self::$params[$key], 1)) . '<br/></br>';
			$html .= $val . '<br/></br>' . $params;
		}
		return $html . '</pre>';
	}
	
	/**
	* Save debug information in session, useful as a flashmemory when redirecting to another page.
	* 
	* @param string $debug enables to save some extra debug information.
	*/
	public function SaveDebug($debug=null) 
	{
		if($debug) 
		{
			self::$queries[] = $debug;
			self::$params[] = null;
		}
		
		self::$queries[] = 'Saved debuginformation to session.';
		self::$params[] = null;
		
		$_SESSION['CDatabase']['numQueries'] = self::$numQueries;
		$_SESSION['CDatabase']['queries']    = self::$queries;
		$_SESSION['CDatabase']['params']     = self::$params;
	}
	
	/**
	* Execute a select-query with arguments and return the resultset.
	* 
	* @param string $query the SQL query with ?.
	* @param array $params array which contains the argument to replace ?.
	* @param boolean $debug defaults to false, set to true to print out the sql query before executing it.
	* @return array with resultset.
	*/
	public function ExecuteSelectQueryAndFetchAll($query, $params=array(), $debug=false, $fetchStyle = NULL) {
	
		// Make the query
	    $this->stmt = $this->db->prepare($query);
	    $this->stmt->execute($params);
	    $res = $this->stmt->fetchAll($fetchStyle);
	    
	    // Log details on the query
	    $rows = count($res);
	    $logQuery = $query . "\n\nResultset has $rows rows.";
	    self::$queries[] = $logQuery;
	    self::$params[]  = $params; 
	    self::$numQueries++;
	    
	    // Debug if set
	    if($debug) 
	    {
	      echo "<p>Query = <br/><pre>{$logQuery}</pre></p><p>Num query = " . self::$numQueries . "</p><p><pre>".print_r($params, 1)."</pre></p>";
	    }
	    return $res;
	}
	
	/**
	* Execute a SQL-query and ignore the resultset.
	*
	* @param string $query the SQL query with ?.
	* @param array $params array which contains the argument to replace ?.
	* @param boolean $debug defaults to false, set to true to print out the sql query before executing it.
	* @return boolean returns TRUE on success or FALSE on failure. 
	*/
	public function ExecuteQuery($query, $params = array(), $debug=false) 
	{
	
		// Make the query
		$this->stmt = $this->db->prepare($query);
		$res = $this->stmt->execute($params);

		// Log details on the query
		$error = $res ? null : "\n\nError in executing query: " . $this->ErrorCode() . " " . print_r($this->ErrorInfo(), 1);
		$logQuery = $query . $error;
		self::$queries[] = $logQuery; 
		self::$params[]  = $params; 
		self::$numQueries++;

		// Debug if set
		if($debug) {
			echo "<p>Query = <br/><pre>".htmlentities($logQuery)."</pre></p><p>Num query = " . self::$numQueries . "</p><p><pre>".htmlentities(print_r($params, 1))."</pre></p>";
		}

		return $res;
	}
	
	/**
	* Return last insert id, see PDO::LastInsertId().
	*
	* @return string representation of id of last inserted row.
	*/
	public function LastInsertId() 
	{
		return $this->db->lastInsertid();
	}
	
	
	/**
	* Return rows affected of last INSERT, UPDATE, DELETE
	*/
	public function RowCount() {
		return is_null($this->stmt) ? $this->stmt : $this->stmt->rowCount();
	}
	
	
	/**
	* Return error code of last unsuccessful statement, see PDO::errorCode().
	*
	* @return mixed null or the error code.
	*/
	public function ErrorCode() { return $this->stmt->errorCode(); }
	
	
	/**
	* Return textual representation of last error, see PDO::errorInfo().
	*
	* @return array with information on the error.
	*/
	public function ErrorInfo() { return $this->stmt->errorInfo(); }


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
	
	public function ShowResultInTable($table, $result, $showRow = FALSE, $options = array())
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
					
					
				$html .= "<th><a href='".getQuerystring(array("sort" => $body['sort'], "order" => $order))."'>$head</a> ".($order == 'ASC' ? "&#8{$arrow}3;" : "&#8{$arrow}5;")."</th>";
			}
			else
				$html .= "<th>$head</th>";
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
				if (isset($body['type']) && $body['type'] == 'img')
					$html .= "<td><img ".$body['img']['tag']." src='".$obj->{$body['img']['src']}."' alt='".$obj->{$body['img']['alt']}."'></td>";
				else if (isset($body['type']) && $body['type'] == 'keyword')
				{
					$html .= "<td>";
					$separator = (isset($body['separator']) ? $body['separator'] : ','); 
					$list = explode($separator, $obj->{$body['column']});
					foreach ($list as $keyword)
						if (!empty($keyword))
							$html .= "<span class='keyword'>$keyword</span>";
					$html .= "</td>";
				}
				else if (isset($body['type']) && $body['type'] == 'edit')
				{
					$html .= "<td><a href='".getQueryString(array("id" => $obj->id))."'>✎</a></td>";
				}
				else if (isset($body['type']) && $body['type'] == 'delete')
				{
					$html .= "<td><a href='".getQueryString(array("id" => $obj->id))."'>╳</a></td>";
				}
				else if ($body['column'])
					$html .= "<td>".$obj->{$body['column']}."</td>";
				else 
					$html .= "<td>".print_r($body, TRUE)."</td>";
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
}
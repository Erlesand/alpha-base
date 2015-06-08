<?php 
	class CContent
	{
		private $db; 
		private $message; 
		
		public function __construct() {
			global $db; 
			
			$this->db = $db; 
		}
		
		public function init($mockdata = TRUE)
		{
			$sql = "DROP TABLE IF EXISTS Content;
			CREATE TABLE Content
			(
				id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
				slug CHAR(80) UNIQUE,
				url CHAR(80) UNIQUE,
				
				type CHAR(80),
				title VARCHAR(80),
				data TEXT,
				author CHAR(12),
				filter CHAR(80),
				
				published DATETIME,
				created DATETIME,
				updated DATETIME,
				deleted DATETIME
			
			) ENGINE INNODB CHARACTER SET utf8;";
			
			if (!$this->db->ExecuteQuery($sql))
				return FALSE; 
				
			if ($mockdata)
			{
				$sql = "INSERT INTO Content (slug, url, type, title, data, filter, published, created, author) VALUES
  ('hem', 'hem', 'page', 'Hem', \"Detta är min hemsida. Den är skriven i [url=http://en.wikipedia.org/wiki/BBCode]bbcode[/url] vilket innebär att man kan formatera texten till [b]bold[/b] och [i]kursiv stil[/i] samt hantera länkar.\n\nDessutom finns ett filter 'nl2br' som lägger in <br>-element istället för \\n, det är smidigt, man kan skriva texten precis som man tänker sig att den skall visas, med radbrytningar.\", 'bbcode,nl2br', NOW(), NOW(), 'admin'),
  ('om', 'om', 'page', 'Om', \"Detta är en sida om mig och min webbplats. Den är skriven i [Markdown](http://en.wikipedia.org/wiki/Markdown). Markdown innebär att du får bra kontroll över innehållet i din sida, du kan formatera och sätta rubriker, men du behöver inte bry dig om HTML.\n\nRubrik nivå 2\n-------------\n\nDu skriver enkla styrtecken för att formatera texten som **fetstil** och *kursiv*. Det finns ett speciellt sätt att länka, skapa tabeller och så vidare.\n\n###Rubrik nivå 3\n\nNär man skriver i markdown så blir det läsbart även som textfil och det är lite av tanken med markdown.\", 'markdown', NOW(), NOW(), 'admin'),
  ('blogpost-1', NULL, 'post', 'Välkommen till min blogg!', \"Detta är en bloggpost.\n\nNär det finns länkar till andra webbplatser så kommer de länkarna att bli klickbara.\n\nhttp://dbwebb.se är ett exempel på en länk som blir klickbar.\", 'link,nl2br', NOW(), NOW(), 'admin'),
  ('blogpost-2', NULL, 'post', 'Nu har sommaren kommit', \"Detta är en bloggpost som berättar att sommaren har kommit, ett budskap som kräver en bloggpost.\", 'nl2br', NOW(), NOW(), 'doe'),
  ('blogpost-3', NULL, 'post', 'Nu har hösten kommit', \"Detta är en bloggpost som berättar att sommaren har kommit, ett budskap som kräver en bloggpost\", 'nl2br', NOW(), NOW(), 'doe')
;";
				if (!$this->db->ExecuteQuery($sql))
					return FALSE; 
			}		
			
			return TRUE; 
		}
		
		public function create($params)
		{		
			$default = array(
				"title"		=> NULL,
				"slug"		=> (isset($params['title']) ? slugify($params['title']) : NULL),
				"url"		=> NULL, 
				"data"		=> NULL, 
				"type"		=> NULL,
				"filter"	=> NULL, 
				"published"	=> date("Y-m-d H:i:s"), 
				"author"	=> $_SESSION['user']->acronym
			); 
			

				foreach ($params AS $key => $value)
					if ($value == "") unset($params[$key]); 
			
			if (isset($params['filter']) && is_array($params['filter']))
				$params['filter'] = implode(',', $params['filter']); 
			
			$params = array_intersect_key($params, $default); 
			$params = array_values(array_merge($default, $params));
			
			
			
			$sql = "INSERT INTO Content(title, slug, url, data, type, filter, published, created, author) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

            if ($this->db->ExecuteQuery($sql, $params))
            {
				$this->message = '<p class="success">Informationen sparades.</p>'; 
				return TRUE; 
			}
			else
			{
				$this->message = '<p class="alert">Informationens sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), TRUE) . '</pre></p>'; 
				return FALSE; 
            }
		}
		
		public function update($id, $params)
		{
			// Check if the supplied ID is a numerical value. 
			if (!is_numeric($id))
			{
				$this->message = '<p class="alert">ID måste vara ett numeriskt värde</p>'; 
				return FALSE; 
			}
			
			$sql = "SELECT title, slug, url, data, type, filter, published FROM Content WHERE id = ?"; 
			$default = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id)); 

			// Check if there exists a row with the id. 
			if (!isset($default[0]))
			{
				$this->message = '<p class="alert">Finns inget rad med ID #'.$id.'</p>'; 
				return FALSE; 
			}
			
			$default = $default[0]; 
			
			// Pick out the intersecting keys from the submitted parameters, and then merge with the old values.
			$params = array_intersect_key($params, (array)$default); 
			$params = array_merge((array)$default, $params); 
			
			$params['url'] = strip_tags($params['url']); 
			if (empty($params['url'])) $params['url'] = NULL;
			$params['slug'] = slugify($params['title']); 
			$params['type'] = strip_tags($params['type']); 
			$params['published'] = strip_tags($params['published']); 
			$params['id'] = $id;
			if (is_array($params['filter'])) $params['filter'] = implode(',', $params['filter']); 

			$sql = "UPDATE Content SET title = ?, slug = ?, url = ?, data = ?, type = ?, filter = ?, published = ?, updated = NOW() WHERE id = ?";
			if ($this->db->ExecuteQuery($sql, $params))
			{
				$this->message = '<p class="success">Informationen sparades.</p>'; 
				return TRUE; 
			}
			else
			{
				$this->message = '<p class="alert">Informationens sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), TRUE) . '</pre></p>'; 
				return FALSE; 
            } 
		}
		
		public function delete($id)
		{
			if (!is_numeric($id))
			{
				$this->message = "<p class='alert'>ID är ej numeriskt.</p>";
				return FALSE; 
			}
			
			$sql = "DELETE FROM Content WHERE id = ? LIMIT 1";
			$params = array($id); 
			
			if ($this->db->ExecuteQuery($sql, $params))
			{
				$this->message = '<p class="success">ID #'.$id.' togs bort.</p>'; 
				return TRUE; 
			}
			else
			{
				$this->message = '<p class="alert">ID '.$id.' kunde ej tas bort.<br><pre>' . print_r($this->db->ErrorInfo(), TRUE) . '</pre></p>'; 
				return FALSE; 
            } 
		}
		
		private function checkID($id)
		{
			
		}
				
		public function load($param)
		{
			$sql = "SELECT type, slug, url FROM Content WHERE slug = ? OR url = ? LIMIT 1";
			$params = array($param, $param); 
			
			if (!$param)
			{
				$view = new CBlog(); 
				return $view->view(); 
			}
			
			$result = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);
			if (!isset($result[0]))
				return FALSE; 
			
			if ($result[0]->type == 'page')
			{
				$view = new CPage(); 
				return $view->view($result[0]->url); 
			}
			else if ($result[0]->type == 'post')
			{
				$view = new CBlog();
				return $view->view($result[0]->slug); 
			}
			
			return FALSE; 
		}

		public function getMessage()
		{
			return $this->message; 
		}
		
		public static function form($page)
		{
			if ($page == "edit")
			{
				$form = array(
					array("type" => "hidden", "name" => "id"),
					array("type" => "text", "name" => "title", "label" => "Titel"),
					array("type" => "text", "name" => "slug", "label" => "Slug"),
					array("type" => "text", "name" => "url", "label" => "URL"),
					array("type" => "textarea", "name" => "data", "label" => "Text"),
					#array("type" => "text", "name" => "type", "label" => "Typ"), 
					array("type" => "select", "name" => "type", "label" => "Sidtyp", "options" => "page,post"),
					#array("type" => "text", "name" => "filter", "label" => "Filter"),
					array("type" => "checkbox", "name" => "filter", "label" => "Filter", "options" => "bbcode,markdown,nl2br,link,htmlpurify,shortcode"),
					array("type" => "text", "name" => "published", "label" => "Publiserad")
				);
				
				$submit = array("name" => "doSave", "value" => "Uppdatera"); 

				return CForm::generate($form, $submit); 
			}
			
			if ($page == "create")
			{								
				$form = array(
					array("type" => "text", "name" => "title", "label" => "Titel"),
					array("type" => "text", "name" => "url", "label" => "URL"),
					array("type" => "textarea", "name" => "data", "label" => "Text"),
					#array("type" => "text", "name" => "type", "label" => "Typ"), 
					array("type" => "select", "name" => "type", "label" => "Sidtyp", "options" => "page,post"),
					array("type" => "checkbox", "name" => "filter", "label" => "Filter", "options" => "bbcode,markdown,nl2br,link,htmlpurify,shortcode"),
					array("type" => "text", "name" => "published", "label" => "Publiserad")
				);
				
				$submit = array("name" => "doSave", "value" => "Lägg till"); 

				return CForm::generate($form, $submit, "POST", FALSE); 
			}
			
			if ($page == "delete")
			{
				$form = array(
					array("type" => "hidden", "name" => "id"),
					array("type" => "text", "name" => "title", "label" => "Titel")
				);
				
				$submit = array("name" => "doDelete", "value" => "Ta bort"); 

				return CForm::generate($form, $submit); 
			}
		}
	}
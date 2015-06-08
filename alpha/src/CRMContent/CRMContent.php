<?php 
	class CRMContent
	{
		private $db; 
		private $message; 
		
		public function __construct() {
			global $db; 
			
			$this->db = $db; 
		}
		
		public static function Title()
		{
			$page = basename($_SERVER['PHP_SELF']); 
			if (!$page) $page = "index.php"; 
			
			$main = array(
				"index.php" 		=> "Welcome to Rental Movies", 
				"movies.php" 		=> "Welcome to our movie library",
				"news.php" 			=> "<a href='news.php'>News</a>",
				"competition.php" 	=> "The Dice Competion",
				"calendar.php"		=> "The Movie Calendar",
				"about.php"			=> "About Rental Movies", 
				"user.php"			=> "The User Panel"
			);
			
			if ($page == "movies.php")
			{
				if (isset($_GET['p']) && $_GET['p'] == 'view' && isset($_GET['id']))
				{
					$movie = new CRMContent();
					$page = "<a href='movies.php'>All movies</a> &raquo; ".$movie->getTitle("VMovies", $_GET['id']);
				}
				else if (isset($_GET['genre']))
					$page = "<a href='movies.php'>All movies</a> &raquo; ".$_GET['genre']; 
				else
					$page = $main[$page]; 

			}
			else if ($page == "news.php")
			{
				if (isset($_GET['p']) && $_GET['p'] == 'view' && isset($_GET['id']))
				{
					$movie = new CRMContent();
					$page = "<a href='news.php'>All news</a> &raquo; ".$movie->getTitle("VNews", $_GET['id']);
				}
				else if (isset($_GET['category']))
					$page = "<a href='news.php'>All news</a> &raquo; ".$_GET['category'];
				else
					$page = $main[$page];
			}
			else
				$page = $main[$page];
			
			return $page;
		}
		
		private function getTitle($table, $id)
		{
			$sql = "SELECT title FROM $table WHERE id = ?";
			$result = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));
			$row = $result[0];
			return $row->title; 
		}
		
		public function read($type, $header, $id = NULL) 
		{
			$html = NULL; 
			
			if ($type == "movies" || $type == 'last')
			{
				$sql = "SELECT * FROM Movies";
				

				if (!$id)
					$sql .= " ORDER BY id DESC LIMIT 3"; 
				else
					$sql .= " WHERE id = ?"; 
				 
				$result = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));

				$html .= '<div id="'.$type.'">'; 
				$html .= '<h2>'.$header.'</h2>'; 
				

				foreach ($result as $movie)
				{
					
					if (empty($movie->image) || !file_exists("img/posters/".$movie->image))
						$movie->image = "placeholder-movie.png";
					
					$html .= '<div class="movies">
						<h3>'.$movie->title.'</h3>
						<p class="poster"><img src="img.php?src=posters/'.$movie->image.'&amp;width=300&amp;height=430&amp;crop-to-fit"></p>';
						
					$html .= $this->rating($movie->rating); 
					$html .= '<p class="more"><a href="movies.php?p=view&amp;id='.$movie->id.'">Read more »</a></p>
					</div>'; 
				}			
				$html .= "</div>";
			}
			else if ($type == "genres")
			{
				$html .= '<div id="genres">'; 
				
				$sql = "SELECT * FROM Genres ORDER BY name"; 
				$result = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));
				
				if (isset($_GET['id']) && is_numeric($_GET['id']))
				{
					$genres = $this->db->ExecuteSelectQueryAndFetchAll("SELECT genre FROM VMovies WHERE id = ?", array($_GET['id'])); 
					$genres = explode(',', $genres[0]->genre);
				}
				else if (isset($_GET['genre']))
					$genres = array($_GET['genre']); 
				else
					$genres = array(); 
				
				
				foreach ($result as $genre)
				{
					$class = "genre".(in_array($genre->name, $genres) ? " selected" : NULL); 
						
					$html .= '<a href="movies.php?genre='.$genre->name.'"><img class="'.$class.'" src="img.php?src=genres/'.$genre->name.'.png" alt="'.$genre->name.'" title="'.$genre->name.'"></a>';
				}
				
				if ($header)
					$html .= "<div class='pick'>Pick a genre above to list all matching movies</div>";
				$html .= "</div>";
			}
			else if ($type == "categories")
			{
				$html .= '<div id="categories">'; 
				
				$sql = "SELECT * FROM Categories ORDER BY name"; 
				$result = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($id));

				if (isset($_GET['id']) && is_numeric($_GET['id']))
				{
					$categories = $this->db->ExecuteSelectQueryAndFetchAll("SELECT category FROM VNews WHERE id = ?", array($_GET['id'])); 
					$categories = explode(',', $categories[0]->category);
				}
				else if (isset($_GET['category']))
					$categories = array($_GET['category']); 
				else
					$categories = array(); 

				foreach ($result as $category)
				{
					$class = "category".(in_array($category->name, $categories) ? " selected" : NULL); 
						
					$html .= '<a href="news.php?category='.$category->name.'"><img class="'.$class.'" src="img.php?src=categories/'.$category->name.'.png" alt="'.$category->name.'" title="'.$category->name.'"></a>';
				}
				
				if ($header)
					$html .= "<div class='pick'>Pick a category above to list all matching news</div>";
				
				$html .= "</div>";
			}
			else if ($type == "news")
			{
				$html .= "<div id='$type'>"; 
				$html .= "<h2>$header</h2>"; 
				
				$sql = "SELECT * FROM News ORDER BY published DESC LIMIT 3"; 
				$result = $this->db->ExecuteSelectQueryAndFetchAll($sql);
				
				foreach ($result as $news)
				{
					$html .= '<div class="news">'; 
					$html .= '<h3>'.$news->title.'</h3>'; 
					$html .= '<div class="author">by '.$news->author.'</div>'; 
					$html .= '<p class="text"><strong>'.strtoupper(date("j M", strtotime($news->published))).'</strong> - '.htmlentities(substr($news->data, 0, 500)).'...</p>';
					$html .= '<div class="more"><a href="news.php?p=view&amp;id='.$news->id.'">Read more »</a></div>'; 
					$html .= "</div>\n"; 
				}
				$html .= "</div>";
			}
			else if ($type == "competition")
			{
				$html .= "<div id='$type'>"; 
				$html .= "<h2>$header</h2>"; 
				$html .= "<p>Here</p>"; 
				$html .= "</div>";
			}

			return $html; 
		}
		
		public static function rating($score)
		{
			$color = round($score / 10); 
			$html = "<p class='rating'>"; 
			
			for ($i = 1; $i <= 10; $i++)
			{
				if (round($score / 10) >= $i)
				{
					$html .= "<span class='rating color-$i'>★</span>";
					$color = $i; 
				}
				else 
					$html .= "<span class='rating'>☆</span>";
			}
			
			$html .= '<span class="rating score color-'.$color.'">'.$score.'</span><span class="rating max">/100</span>';
			
			$html .= "</p>";
			
			return $html;
		}
	}
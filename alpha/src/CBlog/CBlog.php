<?php 
	class CBlog
	{
		private $db; 
		
		public function view($slug = NULL)
		{
			global $db; 
			$this->db = $db; 
			
			$html = NULL; 
			
			if ($slug)
			{
				$sql = "SELECT title, data, published, updated, slug, filter, id, author FROM Content WHERE slug = ? AND type = 'post' AND published <= NOW() LIMIT 1";
				$params = array($slug); 
			}
			else
			{
				$sql = "SELECT title, data, published, updated, slug, filter, id, author FROM Content WHERE type = 'post' AND published <= NOW()";
				$params = array();
			}
				
			$result = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params); 
			if (!isset($result[0]))
				return "<p class='alert'>Kunde inte hitta något inlägg för  '$slug'</p>";
			
			else
			{
				$filter = new CTextFilter();
				
				foreach ($result as $row)
				{
					$html .= "<article class='justify blog'>";
					$html .= $this->datebox($row->published); 
					
					$html .= "<h1><a href='".getQueryString(array("slug" => $row->slug))."'>".htmlentities($row->title, null, 'UTF-8')."</a></h1>";
					$html .= "<p>".$filter->doFilter($row->data, $row->filter)."</p>";
					$html .= '<div class="report-footer">'; 
					$html .= 'Av '.$row->author;
					if (!empty($row->updated) && $row->published != $row->updated)
						$html .= ', uppdaterades '.date("Y-m-d", strtotime($row->updated));
					if (CUser::isAuthenticated())
						$html .= '<a href="?p=blog_edit&id='.$row->id.'" class="right button dark">Ändra inlägg</a>'; 
						
					$html .= '</div>'; 
					$html .= "</article>";
				}
			}
			
			return $html; 
		}
		
		public function datebox($date)
		{
			$ts = strtotime($date); 

			return '<div class="datebox" style="float: right;">
						<span class="month">'.date("M", $ts).'</span> 
						<span class="day">'.date("d", $ts).'</span> 
						<span class="year">'.date("Y", $ts).'</span>
					</div>'; 
		}
	}
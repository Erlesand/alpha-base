<?php 
	class CPage 
	{
		private $db; 
		
		public function view($url = NULL)
		{
			global $db; 
			$this->db = $db; 
			
			$html = NULL; 
			
			if ($url)
			{
				$sql = "SELECT title, data, published, slug, filter, id FROM Content WHERE url = ? AND type = 'page' AND published <= NOW() LIMIT 1";
				$params = array($url); 
			}
			else
			{
				$sql = "SELECT title, data, published, updated, slug, filter, id FROM Content WHERE type = 'page' AND published <= NOW()";
				$params = array();
			}
				
			$result = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params); 
			if (!isset($result[0]))
				return "<p class='alert'>Kunde inte hitta någon sida för '$url'</p>";
			
			else
			{
				$filter = new CTextFilter();
				
				foreach ($result as $row)
				{
					$html .= "<article class='justify blog'>";
					#$html .= $this->datebox($row->published); 
					
					$html .= "<h1><a href='".getQueryString(array("slug" => $row->slug))."'>".htmlentities($row->title, null, 'UTF-8')."</a></h1>";
					$html .= "<p>".$filter->doFilter(htmlentities($row->data, null, 'UTF-8'), $row->filter)."</p>";
					$html .= '<div class="report-footer">'; 
					if (empty($row->updated) || $row->updated == $row->published)
						$html .= 'Sidan skapades '.date("Y-m-d", strtotime($row->published)); 
					else
						$html .= 'Sidan uppdaterades '.date("Y-m-d", strtotime($row->updated)); 
						
					$html .= '<a href="?p=blog_edit&id='.$row->id.'" class="right button dark">Ändra sidan</a>'; 
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
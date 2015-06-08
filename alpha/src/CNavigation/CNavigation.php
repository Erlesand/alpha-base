<?php 
	class CNavigation {	
		public static $parent;
		public static $child; 
		
		
		public static function breadcrumb()
		{
			$html = "<div id='breadcrumb'>☰ "; 
			if (!empty(self::$parent))
				$html .= "<a href='".self::$parent['url']."' title='".self::$parent['title']."'>".self::$parent['text']."</a>" . ' > ';

			$html .= "<a href='".self::$child['url']."' title='".self::$child['title']."'>".self::$child['text']."</a>"; 
			$html .= "</div>";
			
			return $html;
		}
		
		// Version one of navigation bar, no support for submenu. 
		public static function GenerateMenu($menu, $class) 
		{
			$func = null; 

		    if (isset($menu['callback'])) {
		      $func = $menu['callback']; 
		    }

		    $html = "<nav class='$class'>\n";

		    foreach($menu['items'] as $item) {
		    	if (isset($menu['callback']) && call_user_func($menu['callback'], $item['url'])) { 
		    		$item['class'] .= ' selected'; 
		    	}
				$html .= "<a href='{$item['url']}' class='{$item['class']}' title='{$item['title']}'>{$item['text']}</a>\n";
		    }
		    $html .= "</nav>\n";
		    return $html;
		}
		
		public static function LoadPage($db = TRUE) 
		{
			$html = NULL; 
			
			if ($db == TRUE)
				global $db; 
				
			global $alpha; 
			
			$page = basename($_SERVER['PHP_SELF'], '.php'); 
			$file = (isset($_GET['p']) ? "include/$page/{$_GET['p']}.php" : "include/$page/index.php");
			
			include($file);	
			// The variable $html is declared in the included file.
			
			$html = "<article id='$page' class='wrapper bg-grey'>".$html."</article>";
			return $html;
		}
		
		public static function Restrict() 
		{
			if (!CUser::IsAuthenticated())
				header("Location: user.php?p=login"); 
		}
		
		public static function NavBlog()
		{
			global $alpha; 
			$db = new CDatabase($alpha['database']);

			$sql = "SELECT url, title, slug FROM Content WHERE type = 'page'"; 
			$pages = $db->ExecuteSelectQueryAndFetchAll($sql); 
			
			$nav = array(); 
			
			if (isset($pages[0]))
			{
				$nav['items'][] = array('text' => 'Mina bloggsidor', 'title' => 'Alla mina bloggsidor', 'class' => 'separate'); 
				
				foreach ($pages as $page)
				{
					$nav['items'][] = array('text' => $page->title, 'title' => $page->title, 'url' => 'blog.php?p=blog_view&slug='.$page->slug); 
				}			
			}
			$sql = "SELECT url, title, slug FROM Content WHERE type = 'post'"; 
			$posts = $db->ExecuteSelectQueryAndFetchAll($sql); 
			
			if (isset($posts[0]))
			{
				$nav['items'][] = array('text' => 'Mina blogginlägg', 'title' => 'Alla mina blogginlägg', 'class' => 'separate'); 
				
				foreach ($posts as $post)
				{
					$nav['items'][] = array('text' => $post->title, 'title' => $post->title, 'url' => 'blog.php?p=blog_view&slug='.$post->slug); 
				}			
			}
			
			$nav['items'][] = array('text' => 'Administration', 'title' => 'Administrera bloggen', 'class' => 'separate', 'show' => CUser::IsAuthenticated()); 
			$nav['items'][] = array('text' => 'Översikt', 'url' => 'blog.php?p=blog_admin', 'title' => 'Administrera alla sidor och inlägg', 'show' => CUser::IsAuthenticated());
			$nav['items'][] = array('text' => 'Ny sida/inlägg', 'url' => 'blog.php?p=blog_create', 'title' => 'Lägg till en ny sida eller ett nytt inlägg', 'show' => CUser::IsAuthenticated());
			$nav['items'][] = array('text' => 'Återställ databasen', 'url' => 'blog.php?p=blog_reset', 'title' => 'Återställ databasen till ursprungliga läget', 'show' => CUser::IsAuthenticated());
			
			return $nav; 
		}
		  
		public static function GenerateNavbar($menu)
		{
			
			// Keep default options in an array and merge with incoming options that can override the defaults.
			$default = array(
				'id'          => null,
				'class'       => null,
				'wrapper'     => 'nav',
				'create_url'  => function ($url) {
					return $url;
				},
			);
			
			$menu = array_replace_recursive($default, $menu);
			
			// Function to create urls
			$createUrl = $menu['create_url'];
			
			// Create the ul li menu from the array, use an anonomous recursive function that returns an array of values.
			$createMenu = function ($items, $callback) use (&$createMenu, $createUrl) 
			{
				$html = null;
				$hasItemIsSelected = false;
				
				foreach ($items as $item) 
				{
					// has submenu, call recursivly and keep track on if the submenu has a selected item in it.
					$submenu        = null;
					$selectedParent = null;
					$hasSubmenu 	= null; 
					
					if (isset($item['submenu'])) 
					{
						$hasSubmenu = " &#9662;";
						list($submenu, $selectedParent) = $createMenu($item['submenu']['items'], $callback);
						$selectedParent = $selectedParent ? "selected-parent " : null;
					}
					
					if (isset($item['url']))
						// Check if the current menuitem is selected
						$selected = $callback($item['url']) ? "selected " : null;
					else
						$selected = NULL; 
					
					// Is there a class set for this item, then use it
					$class = isset($item['class']) && ! is_null($item['class']) ? $item['class'] : null;
					
					// Prepare the class-attribute, if used
					$class = ($selected || $selectedParent || $class) ? " class='{$selected}{$selectedParent}{$class}' " : null;
					
					// Add the menu item
					$url = isset($item['url']) ? $createUrl($item['url']) : NULL;
					if (!isset($item['show']) || $item['show'] == TRUE)
					{
						
						if ($url)
						{
							$active = (basename($_SERVER['REQUEST_URI']) == $url || (strtok($url,'?') == basename($_SERVER['PHP_SELF']) && $hasSubmenu) ? '' : '');
							if ($active == '▸ ') 
							{
								if ($hasSubmenu)
									self::$parent = $item; 
								else
									self::$child = $item; 
							}
							$html .= "\n<li{$class}>$active<a href='{$url}' title='{$item['title']}'>{$item['text']}{$hasSubmenu}</a>{$submenu}</li>\n";
						}
						else
							$html .= "\n<li{$class}>{$item['text']}{$hasSubmenu}{$submenu}</li>\n";
					}
					
					
					// To remember there is selected children when going up the menu hierarchy
					if ($selected) {
						$hasItemIsSelected = true;
					}
				}
				
				// Return the menu
				return array("\n<ul>$html</ul>\n", $hasItemIsSelected);
			};
			
			// Call the anonomous function to create the menu, and submenues if any.
			list($html, $ignore) = $createMenu($menu['items'], $menu['callback']);
			
			
			// Set the id & class element, only if it exists in the menu-array
			$id      = isset($menu['id'])    ? " id='{$menu['id']}'"       : null;
			$class   = isset($menu['class']) ? " class='{$menu['class']}'" : null;
			$wrapper = $menu['wrapper'];
			
			return "\n<{$wrapper}{$id}{$class}>{$html}</{$wrapper}>\n";
		}
	};

/*	function modifyNavbar($items) {
		$ref = isset($_GET['p']) && isset($items[$_GET['p']]) ? $_GET['p'] : null;
		if($ref) {
			$items[$ref]['class'] .= 'selected'; 
		}
		return $items;
	}
*/
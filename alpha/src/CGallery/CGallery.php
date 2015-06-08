<?php 
	class CGallery 
	{
		private $path; 
		private $pathComplete; 
		private $pathToGallery; 
		private $basePath; 
		
		public function __construct($path) 
		{
			$this->path = $path; 
			$this->pathComplete = GALLERY_PATH . DIRECTORY_SEPARATOR . $this->path;
			$this->pathToGallery = realpath($this->pathComplete);
			$this->basePath      = realpath(GALLERY_PATH);
		}
		
		public function createGallery()
		{
			// Validate incoming arguments
			if ($this->pathToGallery === false) 
			{
				$this->errorMessage("The path to the gallery image seems to be a non existing path.");
			}
			
			if ($this->basePath === false) 
			{
				$this->errorMessage("The basepath to the gallery, GALLERY_PATH, seems to be a non existing path.");	
			} 
			
			if (!is_dir(GALLERY_PATH)) 
			{ 
				$this->errorMessage('The gallery dir "' . GALLERY_PATH . '" is not a valid directory.');
			}
			
			if (substr_compare($this->basePath, $this->pathToGallery, 0, strlen($this->basePath)) != 0)
			{
				$this->errorMessage("Security constraint: Source gallery is not directly below the directory GALLERY_PATH.\n" . $this->basePath . "\n" . $this->pathToGallery);
			}
			
			// Read and present images in the current directory
			if (is_dir($this->pathToGallery)) 
			{
				$gallery = $this->readAllItemsInDir($this->pathToGallery);
			}
			else if (is_file($this->pathToGallery)) 
			{
				$gallery = $this->readItem($this->pathToGallery);
			}
			
			return $gallery; 
		}
		
		/**
		* Read directory and return all items in a ul/li list.
		*
		* @param string $path to the current gallery directory.
		* @param array $validImages to define extensions on what are considered to be valid images.
		* @return string html with ul/li to display the gallery.
		*/
		public function readAllItemsInDir($path, $validImages = array('png', 'jpg', 'jpeg')) 
		{
			$files = glob($path . '/*'); 
			$gallery = "";
			$folders = "<ul class='gallery'>\n";
			$len = strlen(GALLERY_PATH);
			
			foreach($files as $file) {
			$parts = pathinfo($file);
			$href  = str_replace('\\', '/', substr($file, $len + 1));
			
			// Is this an image or a directory
			if (is_file($file) && in_array($parts['extension'], $validImages)) 
			{
			  $item    = "<img src='img.php?src=" 
			    . GALLERY_BASEURL 
			    . $href 
			    . "&amp;width=128&amp;height=128&amp;crop-to-fit' alt=''/>";
			  $caption = basename($file); 
			}
			else if (is_dir($file)) 
			{
			  $item    = "<img src='img/folder.png' alt=''/>";
			  $caption = basename($file) . '/';
			}
			else 
			{
			  continue;
			}
			
			// Avoid to long captions breaking layout
			$fullCaption = $caption;
			if (strlen($caption) > 18) 
			{
			  $caption = substr($caption, 0, 10) . '…' . substr($caption, -5);
			}
			
			if (is_dir($file))
				$folders .= "<li><a href='?path={$href}' title='{$fullCaption}'><figure class='figure overview'>{$item}<figcaption>{$caption}</figcaption></figure></a></li>\n";
			else
			    $gallery .= "<li><a href='?path={$href}' title='{$fullCaption}'><figure class='figure overview'>{$item}<figcaption>{$caption}</figcaption></figure></a></li>\n";
			}
			
			$gallery .= "</ul>\n";
			
			return $folders.$gallery;
		}
		
		/**
		 * Read and return info on choosen item.
		 *
		 * @param string $path to the current gallery item.
		 * @param array $validImages to define extensions on what are considered to be valid images.
		 * @return string html to display the gallery item.
		 */
		public function readItem($path, $validImages = array('png', 'jpg', 'jpeg')) 
		{
			$parts = pathinfo($path);
			if (!(is_file($path) && in_array($parts['extension'], $validImages))) 
			{
				return "<p>This is not a valid image for this gallery.";
			}
			
			// Get info on image
			$imgInfo = list($width, $height, $type, $attr) = getimagesize($path);
			$mime = $imgInfo['mime'];
			$gmdate = gmdate("D, d M Y H:i:s", filemtime($path));
			$filesize = round(filesize($path) / 1024); 
			
			// Get constraints to display original image
			$displayWidth  = $width > 800 ? "&amp;width=800" : null;
			$displayHeight = $height > 600 ? "&amp;height=600" : null;
			
			// Display details on image
			$len = strlen(GALLERY_PATH);
			$href = GALLERY_BASEURL . str_replace('\\', '/', substr($path, $len + 1));
			$item = "<p><img src='img.php?src={$href}{$displayWidth}{$displayHeight}' alt=''/></p>
			<p>Original image dimensions are {$width}x{$height} pixels. <a href='img.php?src={$href}'>View original image</a>.</p>
			<p>File size is {$filesize}KBytes.</p>
			<p>Image has mimetype: {$mime}.</p>
			<p>Image was last modified: {$gmdate} GMT.</p>";
			
			return $item;
		}
		
		/**
		 * Create a breadcrumb of the gallery query path.
		 *
		 * @param string $path to the current gallery directory.
		 * @return string html with ul/li to display the thumbnail.
		 */
		function createBreadcrumb() 
		{
			$parts = explode('/', trim(substr($this->pathToGallery, strlen(GALLERY_PATH) + 1), '/'));
			$breadcrumb = "<ul class='breadcrumb'>\n<li><a href='?'>Hem</a> »</li>\n";
			
			if (!empty($parts[0])) 
			{
				$combine = null;
				foreach($parts as $part) 
				{
					$combine .= ($combine ? '/' : null) . $part;
					$breadcrumb .= "<li><a href='?path={$combine}'>$part</a> » </li>\n";
				}
			}

			$breadcrumb .= "</ul>\n";
			return $breadcrumb;
		}
		
		/**
		* Display error message.
		*
		* @param string $message the error message to display.
		*/
		public function errorMessage($message) {
			header("Status: 404 Not Found");
			die('gallery.php says 404 - ' . htmlentities($message));
		}
	}
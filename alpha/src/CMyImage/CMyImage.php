<?php 
	/**
	 * This is a PHP skript to process images using PHP GD.
	 *
	 */

	class CMyImage 
	{
		
	
		// Picture parameters
		private $maxHeight; 
		private $maxWidth;
		private $newWidth;
		private $newHeight; 
		private $quality; 
		
		// File parameters
		private $src; 
		private $saveAs; 
		private $pathToImage; 
		
		// Misc.
		private $verbose; 
		private $ignoreCache;   
		private $cropToFit; 

		// Filters. 
		private $sharpen; 
		private $greyscale; 
		private $sepia; 
		
		public function __construct($get) 
		{
			//
			// Ensure error reporting is on
			//
			error_reporting(-1);              // Report all type of errors
			ini_set('display_errors', 1);     // Display all errors 
			ini_set('output_buffering', 0);   // Do not buffer outputs, write directly
		
			$this->maxWidth = $this->maxHeight = 2000;
			
			// Get the incoming arguments
			
			$this->src        = isset($get['src'])     ? $get['src']      : null;
			$this->verbose    = isset($get['verbose']) ? true             : null;
			$this->saveAs     = isset($get['save-as']) ? $get['save-as']  : null;
			$this->quality    = isset($get['quality']) ? $get['quality']  : 60;
			$this->ignoreCache = isset($get['no-cache']) ? true           : null;
			$this->newWidth   = isset($get['width'])   ? $get['width']    : null;
			$this->newHeight  = isset($get['height'])  ? $get['height']   : null;
			$this->cropToFit  = isset($get['crop-to-fit']) ? true 		  : null;
			$this->sharpen    = isset($get['sharpen']) ? true 			  : null;
			$this->greyscale  = isset($get['greyscale']) ? true 		  : null;
			$this->sepia      = isset($get['sepia']) ? true 			  : null;	
			
			$this->pathToImage = realpath(IMG_PATH . $this->src);
			
			
			// Validate incoming arguments
			
			is_dir(IMG_PATH) or $this->errorMessage('The image dir is not a valid directory.');
			is_writable(CACHE_PATH) or $this->errorMessage('The cache dir is not a writable directory.');
			isset($this->src) or $this->errorMessage('Must set src-attribute.');
			preg_match('#^[a-z0-9A-Z-_\.\/@]+$#', $this->src) or $this->errorMessage('Filename contains invalid characters.');
			substr_compare(IMG_PATH, $this->pathToImage, 0, strlen(IMG_PATH)) == 0 or $this->errorMessage('Security constraint: Source image is not directly below the directory IMG_PATH.');
			is_null($this->saveAs) or in_array($this->saveAs, array('png', 'jpg', 'jpeg', 'gif')) or $this->errorMessage('Not a valid extension to save image as');
			is_null($this->quality) or (is_numeric($this->quality) and $this->quality > 0 and $this->quality <= 100) or $this->errorMessage('Quality out of range');
			is_null($this->newWidth) or (is_numeric($this->newWidth) and $this->newWidth > 0 and $this->newWidth <= $this->maxWidth) or $this->errorMessage('Width out of range');
			is_null($this->newHeight) or (is_numeric($this->newHeight) and $this->newHeight > 0 and $this->newHeight <= $this->maxHeight) or $this->errorMessage('Height out of range');
			is_null($this->cropToFit) or ($this->cropToFit and $this->newWidth and $this->newHeight) or $this->errorMessage('Crop to fit needs both width and height to work');
			
			
			// Start displaying log if $this->verbose mode & create url to current image

			if ($this->verbose) {
				$query = array();
				parse_str($_SERVER['QUERY_STRING'], $query);
				unset($query['verbose']);
				$url = '?' . http_build_query($query);
			
			
				echo "<html lang='en'>
			<meta charset='UTF-8'/>
			<title>img.php $this->verbose mode</title>
			<h1>Verbose mode</h1>
			<p><a href=$url><code>$url</code></a><br>
			<img src='{$url}' /></p>";
			}


			// Get information on the image

			$imgInfo = list($width, $height, $type, $attr) = getimagesize($this->pathToImage);
			!empty($imgInfo) or $this->errorMessage("The file doesn't seem to be an image.");
			
			$this->mime = $imgInfo['mime']; 
			
			if($this->verbose) {
				$filesize = filesize($this->pathToImage);
				$this->verbose("Image file: {$this->pathToImage}");
				$this->verbose("Image information: " . print_r($imgInfo, true));
				$this->verbose("Image width x height (type): {$width} x {$height} ({$type}).");
				$this->verbose("Image file size: {$filesize} bytes.");
				$this->verbose("Image mime type: {$this->mime}.");
			}


			// Calculate new width and height for the image

			$aspectRatio = $width / $height;
			
			if($this->cropToFit && $this->newWidth && $this->newHeight) {
				$targetRatio = $this->newWidth / $this->newHeight;
				$cropWidth   = $targetRatio > $aspectRatio ? $width : round($height * $targetRatio);
				$cropHeight  = $targetRatio > $aspectRatio ? round($width  / $targetRatio) : $height;
				if($this->verbose) 
					$this->verbose("Crop to fit into box of {$newWidth}x{$newHeight}. Cropping dimensions: {$cropWidth}x{$cropHeight}.");
			}
			else if($this->newWidth && !$this->newHeight) {
				$this->newHeight = round($this->newWidth / $aspectRatio);
				if($this->verbose) 
					$this->verbose("New width is known {$this->newWidth}, height is calculated to {$this->newHeight}.");
			}
			else if(!$this->newWidth && $this->newHeight) {
				$this->newWidth = round($this->newHeight * $aspectRatio);
				if($this->verbose) 
					$this->verbose("New height is known {$this->newHeight}, width is calculated to {$this->newWidth}.");
			}
			else if($this->newWidth && $this->newHeight) {
				$ratioWidth  = $width  / $this->newWidth;
				$ratioHeight = $height / $this->newHeight;
				$ratio = ($ratioWidth > $ratioHeight) ? $ratioWidth : $ratioHeight;
				$this->newWidth  = round($width  / $ratio);
				$this->newHeight = round($height / $ratio);
				if($this->verbose) 
					$this->verbose("New width & height is requested, keeping aspect ratio results in {$this->newWidth}x{$this->newHeight}.");
			}
			else {
				$this->newWidth = $width;
				$this->newHeight = $height;
				if($this->verbose) 
					$this->verbose("Keeping original width & heigth.");
			}


			// Creating a filename for the cache
			
			$this->parts			= pathinfo($this->pathToImage);
			$this->fileExtension	= $this->parts['extension'];
			$this->saveAs			= is_null($this->saveAs) ? $this->fileExtension : $this->saveAs;
			$quality_				= is_null($this->quality) ? null : "_q{$this->quality}";
			$cropToFit_				= is_null($this->cropToFit) ? null : "_cf";
			$sharpen_ 				= is_null($this->sharpen) ? null : "_s";
			$dirName				= preg_replace('/\//', '-', dirname($this->src));
			$this->cacheFileName 	= CACHE_PATH . "-{$dirName}-{$this->parts['filename']}_{$this->newWidth}_{$this->newHeight}{$quality_}{$cropToFit_}{$sharpen_}.{$this->saveAs}";
			$cacheFileName = preg_replace('/^a-zA-Z0-9\.-_/', '', $this->cacheFileName);
			
			if($this->verbose) 
				$this->verbose("Cache file is: {$this->cacheFileName}");

			// Is there already a valid image in the cache directory, then use it and exit

			$imageModifiedTime = filemtime($this->pathToImage);
			$cacheModifiedTime = is_file($this->cacheFileName) ? filemtime($this->cacheFileName) : null;
			
			// If cached image is valid, output it.
			if(!$this->ignoreCache && is_file($this->cacheFileName) && $imageModifiedTime < $cacheModifiedTime) 
			{
				if ($this->verbose)
					$this->verbose("Cache file is valid, output it.");
					
				$this->outputImage($this->cacheFileName);
			}
			
			if	($this->verbose)
				$this->verbose("Cache is not valid, process image and create a cached version of it."); 
			

			// Open up the original image from file

			if ($this->verbose) 
				$this->verbose("File extension is: {$this->fileExtension}");
			
			switch($this->fileExtension) 
			{
				case 'jpg':
				case 'jpeg': 
				  $image = imagecreatefromjpeg($this->pathToImage);
				  if ($this->verbose) 
				  	$this->verbose("Opened the image as a JPEG image.");
				  
				  break;  
				
				case 'png':  
				  $image = imagecreatefrompng($this->pathToImage); 
				  if ($this->verbose) 
				  	$this->verbose("Opened the image as a PNG image.");
				  	
				  break;  

				case 'gif':  
				  $image = imagecreatefromgif($this->pathToImage); 
				  if ($this->verbose) 
				  	$this->verbose("Opened the image as a GIF image.");
				  	
				  break; 
			
				default: 
					errorPage('No support for this file extension.');
			}


			// Resize the image if needed

			if($this->cropToFit) {
				if ($this->verbose) 
					$this->verbose("Resizing, crop to fit.");
					
				$cropX = round(($width - $cropWidth) / 2);  
				$cropY = round(($height - $cropHeight) / 2);    
				$imageResized = $this->createImageKeepTransparency($this->newWidth, $this->newHeight);
				imagecopyresampled($imageResized, $image, 0, 0, $cropX, $cropY, $this->newWidth, $this->newHeight, $cropWidth, $cropHeight);
				$image = $imageResized;
				$width = $this->newWidth;
				$height = $this->newHeight;
			}
			else if(!($this->newWidth == $width && $this->newHeight == $height)) {
				if($this->verbose) 
					$this->verbose("Resizing, new height and/or width.");
					
				$imageResized = $this->createImageKeepTransparency($this->newWidth, $this->newHeight);
				
				imagecopyresampled($imageResized, $image, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $width, $height);
				$image  = $imageResized;
				$width  = $this->newWidth;
				$height = $this->newHeight;
			}
			
			//
			// Apply filters and postprocessing of image
			//
			if($this->sharpen) {	
				$image = $this->sharpenImage($image);
			}
			
			if ($this->greyscale)
			{
				imagefilter($image, IMG_FILTER_GRAYSCALE); 
				imagefilter($image, IMG_FILTER_BRIGHTNESS, 10); 
			}
			
			if ($this->sepia)
			{
				if (!$this->greyscale)
				{
					imagefilter($image, IMG_FILTER_GRAYSCALE); 
					imagefilter($image, IMG_FILTER_BRIGHTNESS, -10); 
				}
			
				if (!$this->sharpen)
					$image = $this->sharpenImage($image); 

				
				imagefilter($image, IMG_FILTER_COLORIZE, 120, 60, 0, 0);
			}
			
			
			
			//
			// Save the image
			//
			switch($this->saveAs) {
				case 'jpeg':
				case 'jpg':
				  if($this->verbose) 
				  	$this->verbose("Saving image as JPEG to cache using quality = {$this->quality}.");
				  imagejpeg($image, $this->cacheFileName, $this->quality);
				break;  
			
				case 'png':  
				  if($this->verbose) 
				  	$this->verbose("Saving image as PNG to cache.");
				  	
				  // Turn off alpha blending and set alpha flag
				  imagealphablending($image, false);
				  imagesavealpha($image, true);
				  imagepng($image, $this->cacheFileName);  
				break;  
				
				case 'gif':
				  if($this->verbose) 
				  	$this->verbose("Saving image as GIF to cache.");
				  imagegif($image, $this->cacheFileName);
				break; 
			
				default:
				  $this->errorMessage('No support to save as this file extension.');
				break;
			}
			
			if($this->verbose) { 
				clearstatcache();
				$cacheFilesize = filesize($this->cacheFileName);
				$this->verbose("File size of cached file: {$cacheFilesize} bytes."); 
				$this->verbose("Cache file has a file size of " . round($cacheFilesize/$filesize*100) . "% of the original size.");
			}


			// Output the resulting image

			$this->outputImage($this->cacheFileName);

		}
	

	
		/**
		 * Display error message.
		 *
		 * @param string $message the error message to display.
		 */
		private function errorMessage($message) {
			header("Status: 404 Not Found");
			die('img.php says 404 - ' . htmlentities($message));
		}
	


		/**
		 * Display log message.
		 *
		 * @param string $message the log message to display.
		 */
		private function verbose($message) {
			echo "<p>" . htmlentities($message) . "</p>";
		}
		
		
		
		/**
		 * Output an image together with last modified header.
		 *
		 * @param string $file as path to the image.
		 * @param boolean $$this->verbose if $this->verbose mode is on or off.
		 */
		private function outputImage() {
		      $info = getimagesize($this->cacheFileName);
		      !empty($info) or $this->errorMessage("The file doesn't seem to be an image.");
		      $this->mime = $info['mime']; 
		      	
		    
		      $lastModified = filemtime($this->cacheFileName);  
		      $gmdate = gmdate("D, d M Y H:i:s", $lastModified);
		    
		      if($this->verbose) {
		        $this->verbose("Memory peak: " . round(memory_get_peak_usage() /1024/1024) . "M");
		        $this->verbose("Memory limit: " . ini_get('memory_limit'));
		        $this->verbose("Time is {$gmdate} GMT.");
		      }
		    
		      if(!$this->verbose) header('Last-Modified: ' . $gmdate . ' GMT');  			
		      
		      if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified){
		        if($this->verbose) { $this->verbose("Would send header 304 Not Modified, but its $this->verbose mode."); exit; }
		        header('HTTP/1.0 304 Not Modified');
		      } else {  
		        if($this->verbose) { $this->verbose("Would send header to deliver image with modified time: {$gmdate} GMT, but its $this->verbose mode."); exit; }
		        header('Content-type: ' . $this->mime);  
		        readfile($this->cacheFileName);
		      }
		      exit;
		    }
		
		/**
		 * Create new image and keep transparency
		 *
		 * @param resource $image the image to apply this filter on.
		 * @return resource $image as the processed image.
		 */
		private function createImageKeepTransparency($width, $height) {
		    $img = imagecreatetruecolor($width, $height);
		    imagealphablending($img, false);
		    imagesavealpha($img, true);  
		    return $img;
		}
		
		/**
		 * Sharpen image as http://php.net/manual/en/ref.image.php#56144
		 * http://loriweb.pair.com/8udf-sharpen.html
		 *
		 * @param resource $image the image to apply this filter on.
		 * @return resource $image as the processed image.
		 */
		private function sharpenImage($image) {
			$matrix = array(
			  array(-1,-1,-1,),
			  array(-1,16,-1,),
			  array(-1,-1,-1,)
			);
			$divisor = 8;
			$offset = 0;
			imageconvolution($image, $matrix, $divisor, $offset);
			return $image;
		}
	}
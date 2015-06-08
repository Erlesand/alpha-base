<?php
/**
 * A dice with images as graphical representation.
 *
 */
class CDiceImage extends CDice {
	/**
	 * Properties
	 *
	 */
	
	const FACES = 6; 
	 
	/**
	* Constructor
	*
	*/
	public function __construct() {
	 	// self:: is used to access static properties of the class.
		parent::__construct(self::FACES);
	}
	
	/**
	 * Get the rolls as a serie of images.
	 *
	 */
	public function getRollsAsImageList() {
		$html = "<ul class='dice'>";
		foreach($this->rolls as $val) 
			$html .= "<li class='dice-$val'></li>";

		$html .= "</ul>";
		return $html;
	}
}
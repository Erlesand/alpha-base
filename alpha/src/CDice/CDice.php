<?php 
class CDice {
	/** 
	  * Properties
	  *
	  */
	  
	protected $rolls = array();
	private $faces; 
	private $last; 
	
	public function roll($times) {
		$this->rolls = array();
		
		for($i = 0; $i < $times; $i++) {
			$this->last = rand(1, $this->faces);
			$this->rolls[] = $this->last; 
		}
		
		return $this->last; 
	}
	
	public function getFaces() {
		return $this->faces; 
	}
	
	public function getRolls() {
		return $this->rolls; 
	}
	
	public function getLastRoll() {
		return $this->last; 
	}
	
	public function getTotal() {
		return array_sum($this->rolls);
	}
	
	public function getAverage() {
		return round(array_sum($this->rolls) / count($this->rolls), 1); 
	}
	
	/**
	 * Contructor
	 * 
	 * @param int $faces The number of faces to use
	 */
	public function __construct($faces = 6) {
		$this->faces = $faces; 
	}
	
}
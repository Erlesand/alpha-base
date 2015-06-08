<?php
/**
 * A hand of dices, with graphical representation, to roll.
 *
 */
class CDiceRound {
 
  /**
   * Properties
   *
   */
  private $dices;
  private $numDices;
  private $sum;
  private $sumRound; 
  private $sumGame; 
  
  /**
   * Constructor
   *
   * @param int $numDices the number of dices in the hand, defaults to six dices. 
   */
  public function __construct($numDices = 5) {
  	for ($i = 0; $i < $numDices; $i++)
  		$this->dices[] = new CDiceImage(); 
  		
  	$this->numDices = $numDices; 
  	$this->sum = 0; 
  	$this->sumRound = 0; 
  	$this->sumGame = 0; 
  }
  
  public function initRound() {
  	$this->sumRound = 0; 
  	$this->sum = 0; 
  }
 
 
  /**
	* Roll all dices in the hand.
	*
	*/
	public function roll() {
		$this->sum = 0; 
		for($i = 0; $i < $this->numDices; $i++)
		{
			$roll = $this->dices[$i]->roll(1);
			$this->sum += $roll; 
			$this->sumRound += $roll; 
		}
	}
	
	
	/**
	* Get the sum of the last roll.
	*
	* @return int as a sum of the last roll, or 0 if no roll has been made.
	*/
	public function getTotal() {
		return $this->sum; 
	}
	
	public function getRoundTotal() { return $this->sumRound; }
	public function getGameTotal() { return $this->sumGame; }
	
	/**
	* Get the rolls as a serie of images.
	*
	* @return string as the html representation of the last roll.
	*/
	public function GetRollsAsImageList() {
		$html = "You threw a: <ul class='dice'>";
		foreach($this->dices as $dice)
		{
			$val = $dice->getLastRoll(); 
			$html .= "<li class='dice-$val'></li>"; 
		}
		$html .= "</ul>";
		
		return $html; 
	}
	
	public function save($points = NULL)
	{
		if ($points === 0)
		{
			//$this->sum = 0; 
			$this->sumRound = 0; 
			
			return "<p>Unfortunately you threw a one, thus no points will be registered this round.</p>"; 
		}
		else
		{
			$sumRound = $this->sumRound; 
			$this->sumGame += $sumRound; 
			$this->sum = 0; 
			$this->sumRound = 0; 
			
			return "<p>Your $sumRound points gathered this round have been saved."; 
		}
	}
}
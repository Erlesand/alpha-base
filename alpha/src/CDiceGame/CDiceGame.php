<?php
	class CDiceGame {
		public $name; 
		public $type; 
		private $winner = FALSE; 
		
		// Constructor function, save the name and type (human / computer) of the player. 
		public function __construct($name, $type) {
			$this->name = $name; 
			$this->type = $type; 
		}
		
		// Function to check if the player has won.
		// Not used at the moment, but I had ideas about creating a score board.
		public function isWinner() { return $this->winner; }
		
		// Function used to simulate the computer's throws. 
		public function computer($round, $auto = TRUE)
		{
			// The computer will throw the dice until it has gathered either 20+ points during one round, or it has reached the goal of 100 points. 
			// This is not the optimal strategy, but implementing such a strategy is far too complex. 
			while ($round->getGameTotal() + $round->getRoundTotal() < 100 && $round->getRoundTotal() < 20)
			{
				$round->roll(); 
				
				// If getTotal() returns 1 a one has been thrown and we exit the loop by returning FALSE. 
				if ($round->getTotal() == 1)
					return FALSE; 
			}
			return TRUE; 
		}
		
		// The view function handles all the output of the class. 
		
		public function view($view = NULL, $choice = NULL) {
			$round = $_SESSION['round'][$this->name] = (isset($_SESSION['round'][$this->name]) ? $_SESSION['round'][$this->name] : new CDiceRound(1)); 
			$html = NULL; 
			
			// Prints the buttons used to controll the game. 
			if ($view == 'buttons')
			{
				$html = "<p>"; 
				
				if ($choice == 'new')
					$html .= "<a class='button' href='dice.php'>Huvudmeny</a>"; 
				
				if ($choice == 'next')
					$html .= "<a class='button' href='?next'>Nästa spelare</a>"; 
				
				if (!$choice || $choice == 'roll')
					$html .= "<a class='button' href='?roll'>Kasta tärning</a> "; 
				
				if (!$choice || $choice == 'save')
					$html .= "<a class='button' href='?save'>Spara runda</a>";
				
				$html .= "</p>";
				
				return $html; 
			}
			
			// Prints the boxes showing the current round's score or the player's total score. 
			if ($view == 'infoBox')
			{	
				$html = ""; 
				
				if (!$choice || $choice == 'roundPoints')
					$html .= '<div class="info">' . 
							'	<div class="header">Runda</div>' . 
							'	<div class="text">'.$round->getRoundTotal().'</div>' .
							'</div>';
							
				if (!$choice || $choice == 'totalPoints')
					$html .= '<div class="info">' . 
							'	<div class="header">Sparat</div>' . 
							'	<div class="text">'.$round->getGameTotal().'</div>' .
							'</div>';
							
				return $html; 
			}
			
			// Prints information when a new game starts or it is the next player's turn. 
			if (isset($_GET['start']) || isset($_GET['next']))
			{
				$html .= "<p><strong>".$this->name."</strong> ska kasta.</p>";
				$html .= "<p>Dina poäng är följande:</p>";

				$html .= $this->view('infoBox', 'totalPoints'); 
				$html .= $this->view('buttons', 'roll');

			}
			
			// Prints information about the dice throw. 
			if (isset($_GET['roll'])) {
			
				// The player is a computer
				if ($this->type == 'computer')
				{
					$html = "<p>Nu spelar: <strong>".$this->name."</strong></p>";
					
					// The dice did not show a 1. 
					if ($this->computer($round, isset($_GET['auto'])))
					{
						$html .= "<p>Det gick visst bra för ".$this->name." denna runda.</p>";
						$points = $round->getRoundTotal(); 
						
						$round->save(); 
						
						// The computer won. 
						if ($round->getRoundTotal() + $round->getGameTotal() >= 100)
						{
							$html .= "<p>Datorn har nått 100 poängsgränsen och har därför vunnit!</p>";
							$html .= "<p>Prova gärna igen och se vem som vinner nästa runda!</p>"; 
	
							$html .= $this->view('infoBox', 'totalPoints');
							$html .= $this->view('buttons', 'new');
						}
						// The computer gathered 20+ points and stopped. 
						else
						{
							$html .= "<p>Datorn lyckades få ihop ".$points." poäng.</p>";
							$html .= $this->view('infoBox', 'totalPoints'); 
							$html .= $this->view('buttons', 'next');
						}
					}  
					// A one was thrown. 
					else
					{
						$html .= "<p>Nej, detta var ingen bra runda. ".$this->name." rullade en etta.</p>";
						$round->save(0); 

						$html .= $this->view('infoBox', 'totalPoints'); 
						$html .= $this->view('buttons', 'next');
					}

				}
				// The player is a person. 
				else 
				{
					if ($round->getGameTotal() < 100)
					{
						$html = "<p>Nu spelar: <strong>".$this->name."</strong></p>";
						
						$round->roll(); 
						$html .= "<p>".$round->getRollsAsImageList()."</p>"; 
						
					}
					
					// A one was thrown. 
					if ($round->getTotal() == 1)
					{
						$html .= $round->save(0); 
						$html .= $this->view('buttons', 'next');
					}
					// The player won. 
					else if ($round->getRoundTotal() + $round->getGameTotal() >= 100)
					{
						$round->save();
						$html .= "<p>Grattis! Du har nått 100 poängsgränsen och har därför vunnit!</p>"; 
	
						$html .= $this->view('infoBox', 'totalPoints');
						$html .= $this->view('buttons', 'new');
						
						$this->winner = TRUE; 
	
					}
					// Shows the current player's points. 
					else
					{	
					
						$html .= "<p>Dina poäng är följande:</p>";
	
						$html .= $this->view('infoBox'); 
						$html .= $this->view('buttons');
					}
				}
				
			}
			
			// Save the points from the round. 
			if (isset($_GET['save']))
			{
				$html .= $round->save(); 
				$html .= $this->view('infoBox', 'totalPoints'); 
				$html .= $this->view('buttons', 'next');
			}
			
			return $html; 
		}
	}
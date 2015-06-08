<?php
	class CCompetitionGame {
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
			// The computer will throw the dice until it has gathered either 20+ points during one round, or it has reached the goal of 50 points. 
			// This is not the optimal strategy, but implementing such a strategy is far too complex. 
			while ($round->getGameTotal() + $round->getRoundTotal() < 50 && $round->getRoundTotal() < 14)
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
				$html = "<p class='center'>"; 
				
				if ($choice == 'new')
					$html .= "<a class='button' href='competition.php'>Main menu</a>"; 
				
				if ($choice == 'next')
					$html .= "<a class='button' href='?next'>Next player</a>"; 
				
				if (!$choice || $choice == 'roll')
					$html .= "<a class='button' href='?roll'>Throw dice</a> "; 
				
				if (!$choice || $choice == 'save')
					$html .= "<a class='button' href='?save'>Save round</a>";
				
				$html .= "</p>";
				
				return $html; 
			}
			
			// Prints the boxes showing the current round's score or the player's total score. 
			if ($view == 'infoBox')
			{	
				$html = ""; 
				
				if (!$choice || $choice == 'roundPoints')
					$html .= '<div class="info">' . 
							'	<div class="header">Round</div>' . 
							'	<div class="text">'.$round->getRoundTotal().'</div>' .
							'</div>';
							
				if (!$choice || $choice == 'totalPoints')
					$html .= '<div class="info">' . 
							'	<div class="header">Collected</div>' . 
							'	<div class="text">'.$round->getGameTotal().'</div>' .
							'</div>';
							
				return $html; 
			}
			
			// Prints information when a new game starts or it is the next player's turn. 
			if (isset($_GET['start']) || isset($_GET['next']))
			{
				$html .= "<p><strong>".$this->name."</strong> turn to play.</p>";
				$html .= "<p>You have gathered:</p>";

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
						$html .= "<p>Wow, ".$this->name." did well this round!</p>";
						$points = $round->getRoundTotal(); 
						
						$round->save(); 
						
						// The computer won. 
						if ($round->getRoundTotal() + $round->getGameTotal() >= 50)
						{
							$html .= "<p>The computer has gathered 50 points and won!</p>";
							$html .= "<p>Feel free to try again, maybe you will win the movie next round?</p>"; 
	
							$html .= $this->view('infoBox', 'totalPoints');
							$html .= $this->view('buttons', 'new');
						}
						// The computer gathered 20+ points and stopped. 
						else
						{
							$html .= "<p>The computer gathered ".$points." points.</p>";
							$html .= $this->view('infoBox', 'totalPoints'); 
							$html .= $this->view('buttons', 'next');
						}
					}  
					// A one was thrown. 
					else
					{
						$html .= "<p>Bugger, ".$this->name." rolled a one.</p>";
						$round->save(0); 

						$html .= $this->view('infoBox', 'totalPoints'); 
						$html .= $this->view('buttons', 'next');
					}

				}
				// The player is a person. 
				else 
				{
					if ($round->getGameTotal() < 50)
					{
						$html = "<p>Current player: <strong>".$this->name."</strong></p>";
						
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
					else if ($round->getRoundTotal() + $round->getGameTotal() >= 50)
					{
						$round->save();
						$html .= "<div class='center'>
							<p><strong>Congratulations!</strong></p>
							
							<p>You have reached 50 points and thus won the free movie of the month!</p>
							
							<p><img src='img.php?src=posters/MV5BMTcxNDI2NDAzNl5BMl5BanBnXkFtZTgwODM3MTc2MjE@._V1_SX300.jpg&height=250'></p>
							<p><a class='button teal' href='movies.php?p=view&id=11&winner'>Get movie</a></p></div>"; 
	
						#$html .= $this->view('infoBox', 'totalPoints');
						$html .= $this->view('buttons', 'new');
						
						$this->winner = TRUE; 
	
					}
					// Shows the current player's points. 
					else
					{	
					
						$html .= "<p>Your points are:</p>";
	
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
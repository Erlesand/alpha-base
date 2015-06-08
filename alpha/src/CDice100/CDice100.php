<?php 
	class CDice100 {
		public $player;
		public $players; 
		public $active;  
		
		public function view() {
			// Display welcome message. 
			if (empty($_GET))
				return $this->welcome();
			
			
			// Initiate a new round.
			if (isset($_GET['init'])) 
			{
				unset($_SESSION['round']); 
				
				return $this->init(); 
			}
			
			// Start a new game
			if (isset($_GET['start'])) 
				return $this->start(); 
			
			// Throw the dice
			if (isset($_GET['roll']))
			{
				$html = $this->player[$this->active]->view();
				
				// The value of the last throw
				$dice = $_SESSION['round'][$this->player[$this->active]->name]->getTotal();
								
				return $html; 
			}
				
			// Change player
			if (isset($_GET['next']))
			{
				$active = $this->changePlayer($this->active);
				$html = $this->player[$this->active]->view();
				
				return $html; 
			}
			
			// Save points from current round
			if (isset($_GET['save']))
			{
				if (isset($_SESSION['round']))
				{
					$active = $this->active;
					return $this->player[$active]->view(); 
				}
			}
		}
		
		// Change the active player to the next one in line. 
		private function changePlayer($active) { 			
			if ($active + 1 > $this->players) $this->active = 1; 
			else $this->active += 1; 
			
			return $active; 
		}
		
		// Displays a welcome message. 
		public function welcome() 
		{
			$html =	"<p>Välkommen till min testsida för tärningsspelet 100!</p>" . 
					"<p>Spelet går ut på att samla ihop poäng för att komma först till 100.</p>" .
					"<p>I varje omgång kastar en spelare tärningen tills man väljer att stanna och spara poängen. Kastar man en etta så förlorar man alla poäng som har samlats in under rundan, och kastomgången är över.</p>"; 
			$html .= "<p>Börja med att välja om du vill spela själv, mot en vän eller mot datorn:</p>";
			$html .= "<a class='button' href='?init&p2=none'>Spela själv</a> <a class='button' href='?init&p2=human'>Möt en vän</a> <a class='button' href='?init&p2=computer'>Möt datorn</a>";
			
			return $html; 			
		}
		
		// Initiates a new game by asking for the name of the player(s). 
		
		public function init() 
		{
			$html = NULL; 
			
			// Check if player 2 will be a human person. 
			$players = ($_GET['p2'] == 'human' ? 2 : 1); 
			
			$html .= "<p>Innan vi börjar spela skulle det vara kul att veta ".($players > 1 ? 'era' : 'ditt')." namn.</p>"; 
			$html .= '<form id="form-dice" method="post" action="?start">'; 
			
			// Loop for input fields where the players' names can be entered. 
			for ($i = 1; $i <= $players; $i++)
			{
				$html .= "<p><label>Spelare $i</label> <input type='text' name='p$i'></p>"; 
				$html .= "<input type='hidden' name='p".$i."_type' value='human'>";
				
			}
			
			// The computer-controller player will be called Sputnik.
			if ($_GET['p2'] == 'computer')
			{
				$html .= "<p><label>Dator</label> <input type='text' readonly='readonly' name='p$i' value='Sputnik'></p>"; 
				$html .= "<input type='hidden' name='p".$i."_type' value='computer'>";
			}
				
			$html .= '<input type="submit" class="button" name="startGame" value="Börja spela">'; 
			$html .= '</form>'; 
			
			return $html; 
		}

		// Start the game by creating instances of the CDiceGame class, one for each player. 
		public function start() 
		{
			for ($i = 1; isset($_POST['p'.$i]); $i++)
			{
				$this->player[$i] = new CDiceGame($_POST['p'.$i], $_POST['p'.$i.'_type']);
			}
			
			$this->players = count($this->player);  
			$this->active = 1; 
			
			return $this->player[1]->view();
		}			
			
	}
?>
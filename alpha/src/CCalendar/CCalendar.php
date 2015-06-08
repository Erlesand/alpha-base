<?php 
class CCalendar {
	public $month; 
	public $year; 
	public $holidays; 
	
	private $namesdays; 
	private $moonPhases; 
	private $flagdays; 

	// Method to show the active month and links to the previous / next one. 
	// It also initiates the table where the dates will be displayed. 
	public function head($image = TRUE) {
		$html = null; 
		
		$now = mktime(0,0,0, $this->month, 1, $this->year);
		$next = strtotime("+1 month", $now);
		$prev = strtotime("-1 month", $now);
		
		$prev = "<a class='prev' href='?year=".date("Y", $prev)."&amp;month=".date("m", $prev)."'>&laquo; ".strftime("%B", $prev)."</a>";
		$next = "<a class='next' href='?year=".date("Y", $next)."&amp;month=".date("m", $next)."'>".strftime("%B", $next)." &raquo;</a>"; 
		
		if ($image)
			$html .= "<img class='calendar picture' src='img/calendar/month_".strftime("%m", $now).".jpg' alt='Månadens Bild'>"; 
			
		$html .= "<h1 class='calendar month'>$prev".strftime("%B %Y", $now)."$next</h1>";
		$html .= "<table id='calendar'>"; 
		$html .= "<col width='30'><col width='140'><col width='140'><col width='140'><col width='140'><col width='140'><col width='140'><col width='140'>"; 
		
		$html .= "<thead><tr><th>Week</th>"; 
		
		
		// Print the column headers, English will be used since Swedish did not seem to be installed on the BTH-server.
		$ts = strtotime("last monday"); 
		
		for ($i = 0; $i < 7; $i++)
		{
			
			$html .= "<th>".strftime("%A", strtotime("+$i days", $ts))."</th>\n";
		}
		
		$html .= "<tr></thead>"; 
	
	
		return $html;
	}
	
	// Method to print all the dates of the month. 
	public function dates($ts = null) {
		mb_internal_encoding("UTF-8");
		
		$html = null; 
		
		if (!$ts) $ts = mktime(0, 0, 0, $this->month, 1, $this->year); 
		$week = strftime("%V", $ts); 
		
		$html .= "<tr>"; 
		$html .= "<td>".(int)$week."</td>"; 

		// Add table data for previous month, if needed. 
		for ($i = 1; $i < strftime("%u", $ts); $i++)
		{
			$html .= "<td class='calendar previous'></td>";
		}
		
		// Print all of the dates of the month
		for ($j = 1; $j <= date("t", $ts); $i++, $j++) {
			
			// Prints the name's of the day
			$name = "<div class='nameday'>"; 
			foreach ($this->namesdays[substr("0".$this->month, -2).substr("0".$j, -2)]['names'] as $val) 
				$name .= "<div>".$val."</div>";
			$name .= "</div>";
			
			$class = "date"; 
			
			// Check if the current date is a Sunday
			if (($i % 7) === 0)
				$class .= " sunday"; 
 
			// Check if the current date is a holiday, if so, add the name and a class to colour the day red. 
			if ($isHoliday = $this->isHoliday($this->year."-".substr("0".$this->month, -2)."-".substr("0".$j, -2)))
			{
				$holiday = "<div class='holiday'>".$isHoliday['name']."</div>";
				$class .= " red";  
			}
			else
				$holiday = "<div class='normal'>&nbsp;</div>"; 
				
			
			// Check if the date is a Swedish flag day, if so, add a small image. 
			if ($isFlagday = $this->isFlagday($this->year."-".substr("0".$this->month, -2)."-".substr("0".$j, -2)))
			{
				$flag = "<img class='flag' src='img/flag.jpg' alt='$isFlagday' title='$isFlagday'>";
			}
			else
				$flag = NULL; 

			// Add the moon phase if it is a full, new or half moon. 
			$moon = '<span class="'.$this->moonPhase($j).'"></span>';
			$date = '<span class="date">'.substr("0".$j, -2).'</span>'; 
			
				
			$html .= "<td class='$class'>
				<div class='calendar box'>
					<div class='header'>
						$moon
						$date
						$holiday
					</div>
					$name
					$flag
				</div>
			</td>";
			
			// If we are on a Sunday, start a new row and add the week number first. 
			if (($i % 7) === 0 && $j != date("t", $ts)) {
				$week++; 
				$html .= "</tr><tr><td>$week</td>"; 
			}

		}
		
		// Add table data for next month, if needed. 
		for (; ($i % 7) !== 0; $i++)
			$html .= "<td class='calendar next'></td>"; 
		
		$html .= "</tr>";
		return $html; 
		
	}
	
	// Use the CMoonPhase class to find the moon phases of the month. 
	// Converted to PHP by Samir Shah (http://rayofsolaris.net)
	
	private function addMoonPhases() {
		$moon = new CMoonPhase(mktime(0,0,0,$this->month,1,$this->year));
		$this->moonPhases = array(
			date('Y-m-d', $moon->new_moon()) => 'moon new', 
			date('Y-m-d', $moon->first_quarter()) => 'moon first',
			date('Y-m-d', $moon->full_moon()) => 'moon full',
			date('Y-m-d', $moon->last_quarter()) => 'moon last', 
		
			date('Y-m-d', $moon->next_new_moon()) => 'moon new', 
			date('Y-m-d', $moon->next_first_quarter()) => 'moon first', 
			date('Y-m-d', $moon->next_full_moon()) => 'moon full', 
			date('Y-m-d', $moon->next_last_quarter()) => 'moon last'
		); 
	}
	
	// Check if $date is a moon phase that will be displayed in the calendar. 
	private function moonPhase($date) {
		$date = $this->year.'-'.$this->month.'-'.substr("0".$date, -2); 
		if (array_key_exists($date, $this->moonPhases))
			return $this->moonPhases[$date]; 
		
		return 'moon'; 
	}
	
	// Add all the holidays of the year. 
	// Created by Lenny Erlesand (lenny@erlesand.se)
	
	private function addHolidays()
	{
		$easter = easter_date($this->year);  
		$june = strtotime($this->year . '-06-20'); 
		$midsummer = strtotime((6 - date("w", $june))." days", $june);
		$oct = strtotime($this->year . '-10-31');
		$saints = strtotime((6 - date("w", $oct))." days", $oct);
		
		$this->addHoliday('newYearsDay', $this->year.'-01-01', 'Nyårsdag'); 
		$this->addHoliday('epiphany', $this->year . '-01-06', 'Trettondag');
		$this->addHoliday('easter', date("Y-m-d", $easter), 'Påsk');
		$this->addHoliday('goodFriday', date("Y-m-d", strtotime("-2 days", $easter)), 'Långfredag');
		$this->addHoliday('easterMonday', date("Y-m-d", strtotime("+1 day", $easter)), 'Annandag påsk');
		$this->addHoliday('mayDay', $this->year . '-05-01', 'Första maj');
		$this->addHoliday('ascensionDay', date("Y-m-d", strtotime("+39 days", $easter)), 'Kristi himmelsfärdsdag');
		$this->addHoliday('pentecost', date("Y-m-d", strtotime("+49 days", $easter)), 'Pingstdag');
		$this->addHoliday('swedenNationalDay', $this->year . '-06-06', 'Nationaldag');
		$this->addHoliday('midSummer', date("Y-m-d", $midsummer), 'Midsommardag');
		$this->addHoliday('midSummerEve', date("Y-m-d", strtotime("-1 day", $midsummer)), 'Midsommarafton');
		$this->addHoliday('allSaintsDay', date("Y-m-d", $saints), 'Alla helgons dag');
		$this->addHoliday('christmasEve', $this->year . '-12-24', 'Julafton');
		$this->addHoliday('christmasDay', $this->year . '-12-25', 'Juldag');
		$this->addHoliday('boxingDay', $this->year . '-12-26', 'Annandag jul');
		$this->addHoliday('newYearsEve', $this->year . '-12-31', 'Nyårsafton');
	}
	
	// Add a holiday to the holidays array. 
	private function addHoliday($internalName, $date, $holiday)
	{
		$this->holidays[$date] = array("internal" => $internalName, "name" => $holiday);
	}
	
	// Check if $date is a holiday. 
	public function isHoliday($date) {
		return (array_key_exists($date, $this->holidays) ? $this->holidays[$date] : FALSE);  
	}
	
	// Get the date of a certain holiday, search by either the internal name (English camel case), or the Swedish name. 
	public function dateHoliday($search) {
		$search = mb_strtolower($search); 
		
		foreach ($this->holidays as $date => $holiday)
			if (strtolower($holiday['internal']) == $search || mb_strtolower($holiday['name']) == $search)
				return  $date; 
				
		return false; 
	}
	
	// Add all the flag days of the year. 
	private function addFlagdays() 
	{
		$this->flagdays = array(
			$this->year."-01-01" => 'Nyårsdagen', 
			$this->year."-01-28" => 'Konungens namnsdag', 
			$this->year."-03-12" => 'Kronprinsessans namnsdag', 
			$this->dateHoliday('easter') => 'Påskdagen', 
			$this->year."-04-30" => 'Konungens födelsedag', 
			$this->year."-05-01" => '1 maj', 
			$this->dateHoliday('pentecost') => 'Pingstdagen', 
			$this->year."-06-06" => 'Sveriges nationaldag &amp; Svenska flaggans dag', 
			$this->dateHoliday('midSummer') => 'Midsommardagen', 
			$this->year."-07-14" => 'Kronprinsessans födelsedage', 
			$this->year."-08-08" => 'Drottningens namnsdag', 
			$this->year."-10-24" => 'FN-dagen',
			$this->year."-11-06" => 'Gustav Adolfsdagen',
			$this->year."-12-10" => 'Nobeldagen',  
			$this->year."-12-23" => 'Drottningens födelsedag',
			$this->year."-12-25" => 'Juldagen'
		);
	}
	
	// Check if $date is a flag day. 
	private function isFlagday($date) {
		if (array_key_exists($date, $this->flagdays))
			return $this->flagdays[$date]; 
			
		return FALSE; 
	}
	
	// Close the table. 
	public function foot() {
		return "</table>";
	}
	
	// Constructor, used to set the current year and month. 
	// It also builds all the special days. 
	public function __construct($year = NULL, $month = NULL) {

		$this->year = isset($year) ? $year : date("Y"); 
		$this->month = isset($month) ? substr("0".$month, -2) : date("m"); 
		//setlocale(LC_ALL,'sv_SE'); // Not installed on student server, thus commented out. 
		require(__DIR__."/calendarNamedays.php");
		$this->namesdays = $namedays;
		$this->addHolidays(); 
		$this->addMoonPhases();
		$this->addFlagDays(); 
	}
}
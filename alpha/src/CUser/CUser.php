<?php
class CUser {
	public static function Login($user, $pass)
	{
		global $db; 
		
		$sql = "SELECT acronym, name FROM User WHERE acronym = ? AND password = md5(concat(?, salt))";
		$result = $db->ExecuteSelectQueryAndFetchAll($sql, array($user, $pass)); 
			
		$html .= "You are logged in!";
		if(isset($result[0]))
			$_SESSION['user'] = $result[0];
	
		header('Location: ?p=status'); 
	}
	
	public static function Logout() 
	{
		unset($_SESSION['user']);
		header('Location: ?p=login');
	}
	
	public static function IsAuthenticated() 
	{
		return isset($_SESSION['user']);
	}
	
	public static function GetAcronym()
	{
		return isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;
	}
	
	public static function GetName()
	{
		return isset($_SESSION['user']) ? $_SESSION['user']->name : null;
	}
}
<?php
class CUser2 {
	public static function Login($user, $pass)
	{
		global $db; 
		
		$sql = "SELECT acronym, name, picture, byline, access FROM Users WHERE acronym = ? AND password = md5(concat(?, salt))";
		$result = $db->ExecuteSelectQueryAndFetchAll($sql, array($user, $pass)); 
			
		if(isset($result[0]))
		{
			$_SESSION['user'] = $result[0];
			return TRUE; 
		}
		
		return FALSE; 
	}
	
	public static function Logout($url = "index.php") 
	{
		unset($_SESSION['user']);
		header('Location: '.$url);
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
	
	public static function GetAccess()
	{
		$html = '<div style="clear: right; display: inline-block; float: right">'; 
		$html .= '<a href="user.php">'.(isset($_SESSION['user']) ? "User Panel" : "Login")."</a> "; 
		if (isset($_SESSION['user']))
			$html .= '<a href="user.php?logout" style="margin-left: 1.5em">Log out</a>'; 
		$html .= '</div>'; 
		
		return $html; 
	}
	
}
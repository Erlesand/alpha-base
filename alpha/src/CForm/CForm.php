<?php 
	class CForm {
		public static function generate($form, $submit, $method = "POST", $populate = TRUE)
		{
			global $db; 
			
			$html = '<fieldset>';
			$html .= "<form method='$method'>\n"; 
			
			if ($method == "POST")
			{	
				$id = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : NULL);
				
				if ($id)
				{ 
					$sql = "SELECT * FROM Content WHERE id = ?";
					$result = $db->ExecuteSelectQueryAndFetchAll($sql, array($id)); 

					$_POST = (array)$result[0]; 
				}
			}

			foreach ($form as $field)
			{
				$class = isset($field['class']) ? "class='".$field['class']."'" : null; 
				
				if ($field['type'] == 'checkbox')
				{
					$html .= "<p class='form row'>\n
						<label>{$field['label']}</label>\n"; 
						
					if (isset($_POST[$field['name']]))
					{
						if (is_array($_POST[$field['name']])) $tmp = $_POST[$field['name']]; 
						else $tmp = explode(',', $_POST[$field['name']]); 
					}
					else $tmp = array(); 

					foreach (explode(',', $field['options']) AS $value)
					{
						$checked = array_search($value, $tmp) !== FALSE && $populate ? " checked='checked' " : NULL; 
						$html .= "<span class='checkbox'>
							<input type='checkbox' name='{$field['name']}[]' $checked value='$value'>
							<span class='label'>$value</span>
						</span>";
					}	
					$html .= "</p>";
				}
				else if ($field['type'] == 'select')
				{
					$html .= "<p class='form row'>\n
						<label>{$field['label']}</label>\n
						<select name='{$field['name']}' style='min-width: 216px'>"; 
						
					if (isset($_POST[$field['name']]))
					{
						if (is_array($_POST[$field['name']])) $tmp = $_POST[$field['name']]; 
						else $tmp = explode(',', $_POST[$field['name']]); 
					}
					else $tmp = array(); 

					foreach (explode(',', $field['options']) AS $value)
					{
						$selected = array_search($value, $tmp) !== FALSE && $populate ? " selected='selected' " : NULL; 
						$html .= "<option $selected value='$value'>$value</option>\n";
					}
					$html .= "</select></p>";
					
				}
				else
				{
					$value = isset($_POST[$field['name']]) && $populate ? "value='".$_POST[$field['name']]."'" : null; 
					
					if ($field['type'] == 'hidden')
						$html .= "<input type='hidden' name='{$field['name']}' $class $value>"; 
					
					else if ($field['type'] == 'textarea')
						$html .= "<p class='form row'>\n
							<label class='textarea'>{$field['label']}</label>\n
							<textarea name='{$field['name']}' $class>".(isset($_POST[$field['name']]) && $populate ? $_POST[$field['name']] : NULL)."</textarea>\n
						</p>"; 
					else
						$html .= "<p class='form row'>\n
							<label>{$field['label']}</label>\n
							<input type='{$field['type']}' name='{$field['name']}' $class $value>\n
						</p>";
				}
			}
			
			$class = isset($submit['class']) ? "class='".$submit['class']."'" : "class='button full'"; 
			
			$html .= "<p class='form row'>\n
				<input $class type='submit' name='{$submit['name']}' value='{$submit['value']}'>
			</p>";
			
			$html .= "</form>\n</fieldset>\n";
			
			return $html;
		}
	}
	
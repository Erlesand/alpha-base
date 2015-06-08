<?php 
	class CForm2 {
		public static function generate($form, $submit, $method = "POST", $populate = TRUE, $table = NULL)
		{
			global $db; 
			
			$fileupload = NULL; 
			
			foreach ($form AS $field)
			{
				if ($field['type'] == 'file')
					$fileupload = ' enctype="multipart/form-data"'; 
			}
			
			$html = NULL;
			$html .= "<form method='$method'$fileupload>\n"; 
			
			if ($method == "POST")
			{	
				$id = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : NULL);
				
				if ($id)
				{ 
					if (!$table)
						$html .= "<p class='info'>No table submitted into CForm2::generate()</p>";
					else
					{
						$sql = "SELECT * FROM $table WHERE id = ?";
						$result = $db->ExecuteSelectQueryAndFetchAll($sql, array($id)); 
	
						$_POST = (array)$result[0]; 
					}
				}
			}

			foreach ($form as $field)
			{
				$class = isset($field['class']) ? "class='".$field['class']."'" : null; 
				
				if ($field['type'] == 'checkbox')
				{
					$html .= "<div class='row'>\n
						<label class='lbl ".(isset($field['error']) ? $field['error'] : NULL)."'>{$field['label']}</label>\n"; 
						
					if (isset($_POST[$field['name']]))
					{
						if (is_array($_POST[$field['name']])) $tmp = $_POST[$field['name']]; 
						else $tmp = explode(',', $_POST[$field['name']]); 
					}
					else $tmp = array(); 

					foreach (explode(',', $field['options']) AS $key => $value)
					{
						$checked = array_search($value, $tmp) !== FALSE && $populate ? " checked='checked' " : NULL; 
						$html .= "<span class='checkbox'>
							<input type='checkbox' name='{$field['name']}[]' $checked value='".($key+1)."'>
							<span class='label'>$value</span>
						</span>";
					}	
					$html .= "</p>";
				}
				else if ($field['type'] == 'radio')
				{
					$html .= "<div class='row'>\n
						<label class='lbl ".(isset($field['error']) ? $field['error'] : NULL)."'>{$field['label']}</label>\n"; 
						
					if (isset($_POST[$field['name']]))
					{
						if (is_array($_POST[$field['name']])) $tmp = $_POST[$field['name']]; 
						else $tmp = explode(',', $_POST[$field['name']]); 
					}
					else $tmp = array(); 
					
					foreach (explode(',', $field['options']) AS $key => $value)
					{
						$value = mb_strtolower($value);
						$checked = array_search($value, $tmp) !== FALSE && $populate ? " checked='checked' " : NULL; 

						$html .= "<span class='checkbox'>
							<input type='radio' name='{$field['name']}[]' $checked value='".($key + 1)."'>
							<span class='label'>$value</span>
						</span>";
					}	
					$html .= "</p>";
				}
				else if ($field['type'] == 'select')
				{
					$html .= "<div class='row'>\n
						<label class='lbl ".(isset($field['error']) ? $field['error'] : NULL)."'>{$field['label']}</label>\n
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
					$html .= "</select></div>";
					
				}
				else
				{
					$value = isset($_POST[$field['name']]) && $populate ? "value='".htmlentities($_POST[$field['name']], ENT_QUOTES, "UTF-8")."'" : null; 
					
					if ($field['type'] == 'hidden')
						$html .= "<input type='hidden' name='{$field['name']}' $class $value>"; 
					
					else if ($field['type'] == 'textarea')
						$html .= "<div class='row'>\n
							<label class='lbl textarea ".(isset($field['error']) ? $field['error'] : NULL)."'>".htmlentities($field['label'])."</label>\n
							<textarea name='{$field['name']}' $class>".(isset($_POST[$field['name']]) && $populate ? htmlentities($_POST[$field['name']]) : NULL)."</textarea>\n
						</p>"; 
					else
						$html .= "<div class='row'>\n
							<label class='lbl ".(isset($field['error']) ? $field['error'] : NULL)."'>{$field['label']}</label>\n
							<input type='{$field['type']}' name='{$field['name']}' $class $value>\n
						</p>";
				}
			}
			
			$class = isset($submit['class']) ? "class='".$submit['class']."'" : "class='button full'"; 
			
			$html .= "<div class='row'>\n
				<label class='lbl' style='visibility: hidden'>&nbsp;</label>
				<input $class type='submit' name='{$submit['name']}' value='{$submit['value']}'>
			</p>";
			
			$html .= "</form>\n";
			
			return $html;
		}
	}
	
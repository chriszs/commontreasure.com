<?php
set_time_limit(0);

$files = scandir("mts");


foreach ($files as $file) {
	$file_explode = explode(".",$file);
	if (array_pop($file_explode) == "txt") {
		// echo $file."<br>";

		$monthyear = "0610";
		$lines = file("mts/$file",FILE_IGNORE_NEW_LINES);

		$header = 0;
		$column_names = false;
		$ignore_column_headers = false;
		$current_row = 0;
		// $initial_columns = false;

		$subtable = "A";

		$tables = array();
		$current_table = -1;

		$column_lines = array();

		foreach ($lines as $num=>$line) {
			$line = substr($line,1);
			
			// echo trim($line)."\n";

			if (trim($line) == "") {
				// blank - ignore
			}
			else if (trim($line) == "FINANCIAL MANAGEMENT SERVICE" || trim($line) == "STAR - TREASURY FINANCIAL DATABASE" || strpos($line,"ACCOUNTING DATE:") !== false) {
				// header
				$header++;
				$ignore_column_headers = true;
			}
			else if (strpos($line,"USER ID ") === 0 || strpos($line,"REPORT ID: ") === 0 || strpos($line,"DATE: ") === 0) {
				// footer
				$header = 0;
			}
			else if ($header == 2 && preg_match("/TABLE ([0-9]{1,3})\.[ ]*(.*)/",$line,$matches)) {
				// title
				$table_explode = explode("_",$current_table);
				if ($table_explode[0] != $matches[1] || !isset($tables[$current_table]) || (isset($tables[$current_table]['name']) && $tables[$current_table]['name'] != $matches[2])) {
					if ($table_explode[0] != $matches[1] || !isset($tables[$current_table])) {
						$current_table = $matches[1];
						$subtable = "A";
					}
					else {
						$current_table = $matches[1]."_".$subtable;
						$subtable++;
					}
					$tables[$current_table]['name'] = $matches[2];
					$current_row = 0;
								
					// $initial_columns = true;
				}
				
				$column_names = true;
				$column_lines = array();
			}
			else if ($header == 3 && substr_count($line,"_") > 10) {
				// column lines
						
				$tables[$current_table]['column_indices'] = array();
				$tables[$current_table]['column_widths'] = array();
						
				//if (!isset($tables[$current_table]['column_widths'])) {
					$index = 0;
					$current_column = 0;
					if (preg_match_all("/(_+| +)/",$line,$matches)) {
						foreach ($matches[0] as $match) {
							if (isset($match[0]) && $match[0] ==  "_") {
								$tables[$current_table]['column_indices'][$current_column] = $index;
								$tables[$current_table]['column_widths'][$current_column] = strlen($match);
								
								$current_column++;
							}
							
							$index += strlen($match);
						}
						// print_r($matches);
					}
					
				//}
				$column_names = false;
				
				$temp_columns = array();
				foreach ($column_lines as $column_line) {
					foreach ($tables[$current_table]['column_widths'] as $current_column=>$width) {
						$temp_name = substr($column_line,$tables[$current_table]['column_indices'][$current_column],$width);
						if (!isset($temp_columns[$current_column])) {
							$temp_columns[$current_column] = $temp_name;
						}
						else if ($temp_name != "") {
							$temp_columns[$current_column] .= " ".$temp_name;
						}
						$temp_columns[$current_column] = trim($temp_columns[$current_column]);
						// echo " ".$temp_name;
					}
				}
				
				// print_r($temp_columns);
				
				$same = true;
				if (isset($tables[$current_table]['column_names']) && isset($tables[$current_table]['rows'])) {
					foreach ($tables[$current_table]['column_names'] as $i=>$column) {
						if (!isset($temp_columns[$i]) || $column != $temp_columns[$i]) {
							$same = false;
						}
					}
					if (count($tables[$current_table]['column_names']) != count($temp_columns)) {
						$same = false;
					}
				}
				if (!$same) {
					$prev_table = $current_table;
					$table_explode = explode("_",$current_table);
					$current_table = $table_explode[0]."_".$subtable;
					
					$tables[$current_table]['name'] = $tables[$prev_table]['name'];
					$tables[$current_table]['column_widths'] = $tables[$prev_table]['column_widths'];
					$tables[$current_table]['column_indices'] = $tables[$prev_table]['column_indices'];
					
					$current_row = 0;
					
					$subtable++;
				}
				
				if (!isset($tables[$current_table]['column_names'])) {
					$tables[$current_table]['column_names'] = $temp_columns;
				}
				
			//	$initial_columns = false;
				$ignore_column_headers = false;
			}
			else if ($header == 3 && $column_names && $current_table != -1) {
				// column headers
				//print_r($matches);
		/*		$spaces = 0;
				$current_column = 0;
				$build_column = "";
				for ($i = 0;$i < strlen($line);$i++) {
					$char = $line[$i];
					if ($char == " ") {
						$spaces++;
					}
					else if ($spaces > 3) {
						$spaces = 0;
						$build_column = trim($build_column);
						
						if (!isset($temp_columns[$current_column])) {
							$temp_columns[$current_column] = $build_column;
						}
						else {
							if ($temp_columns[$current_column] != "") {
								$temp_columns[$current_column] .= " ";
							}
							$temp_columns[$current_column] .= $build_column;
						}
						$build_column = "";
						
						$current_column++;
					}
					if ($char != " " || $spaces < 3) {
						$build_column .= $char;
					}
				}
				
				$build_column = trim($build_column);
				
				if (!isset($temp_columns[$current_column])) {
					$temp_columns[$current_column] = $build_column;
				}
				else {
					if ($temp_columns[$current_column] != "") {
						$temp_columns[$current_column] .= " ";
					}
					$temp_columns[$current_column] .= $build_column;
				}*/
				
				$column_lines[] = $line;
				
			}
			else if ($header == 3 && $current_table != -1 && isset($tables[$current_table]['column_widths'])) {
				// rows
				foreach ($tables[$current_table]['column_widths'] as $current_column=>$width) {
					$temp_name = substr($line,$tables[$current_table]['column_indices'][$current_column],$width);
					if (preg_match("/^ *[\-0-9\,]+ *$/",$temp_name)) {
						$temp_name = str_replace(",","",trim($temp_name));
					}
					else if (preg_match("/^[\.\*\(\) ]+$/",$temp_name)) {
						$temp_name = trim($temp_name);
					}
					else {
						$temp_name = str_replace(";A","",str_replace(";B","",str_replace(";C","",rtrim($temp_name))));
					}
					$tables[$current_table]['rows'][$current_row][$current_column] = $temp_name;
				}
				$current_row++;
			}
		}

		foreach ($tables as $num=>$table) {
			unset($tables[$num]['column_widths']);
			unset($tables[$num]['column_indices']);
			
			$prepend = "";
			
			if ($table['name'] == "OUTLAYS OF THE U.S. GOVERNMENT (IN MILLIONS)") {
				foreach ($table['rows'] as $row_num=>$row) {
					$collapse = false;
					
					if ($prepend != "") {
						$tables[$num]['rows'][$row_num][0] = $prepend.trim($tables[$num]['rows'][$row_num][0]);
					}
			
					$prepend = "";
					
					if (strpos($row[0],":") === false) {
						$collapse = true;
					}
					
					foreach ($row as $col_num=>$column) {
						if ($col_num != 0 && $column != "") {
							$collapse = false;
						}
					}
					
					if ($collapse) {
						$prepend = $row[0]." ";
						unset($tables[$num]['rows'][$row_num]);
					}
				}
			}
		}

		file_put_contents("mts/".$file_explode[0].".json",json_encode($tables));

		$html = "";
		// foreach (array("5_A"=>$tables['5_A']) as $num=>$table) {
		foreach ($tables as $num=>$table) {
			$html .= "<h2>TABLE ".$num.". ".$table['name']."</h2>";
			$html .= "<table border=\"1\">";
			$html .= "<tr>";
			foreach ($table['column_names'] as $column) {
				$html .= "<td>".$column."</td>";
			}
			$html .= "</tr>";
			foreach ($table['rows'] as $row) {
				$html .= "<tr>";
				foreach ($row as $column) {
					if (strpos($column,":") !== false) {
						$html .= "<td style=\"font-weight:bold\">";
					}
					else {
						$html .= "<td>";
					}
					$html .= str_replace("  ","&nbsp;&nbsp;",ucwords(strtolower(str_replace("--"," &mdash; ",$column))))."</td>";
				}
				$html .= "</tr>";
			}
			$html .= "</table>";
		}
		// echo $html;

		file_put_contents("mts/".$file_explode[0].".html",$html);

	}
}
?>
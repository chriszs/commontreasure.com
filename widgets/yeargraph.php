<?php
class YearGraph
{
	private $year_totals = NULL;
	private $year_totals2 = NULL;
	private $height = 110;
	
	public function YearGraph ($year_totals) {
		$this->year_totals = $year_totals;
	}
	
	public function setYearTotals($year_totals) {
		$this->year_totals = $year_totals;
	}
	
	public function setYearTotals2($year_totals2) {
		$this->year_totals2 = $year_totals2;
	}
	
	public function setHeight($height) {
		if (is_numeric($height)) {
			$this->height = intval($height);
		}
	}
	
	private function renderBar ($amount,$year,$width,$amounts_min,$amounts_max,$pad,$level) {
		$result = "<div title=\"".htmlentities($year).": $".number_abbreviate(abs($amount))."\" style=\"";
				
		if ($year >= date("Y")) {
			$result .= "background-image:url('images/".redgreenblack($amount,$level)."-vertical-estimate.jpg')";	
		}
		else {
			$result .= "background-color:".redgreenblack($amount,$level);
		}
		
		/*
		if ($year == $this->year_totals->getYear()) {
			$result .= "e5b228";
		}
		else if ($year <= 2010 ) {
			$result .= "0556cf";
		}
		else {
			$result .= "999999";
		}*/
		
		$result .= ";height:";
		
		$result .= round(((abs($amount)-$amounts_min)/($amounts_max-$amounts_min))*($this->height))+$pad;
		
		$result .= "px;width:".$width."%;display:inline-block;cursor:pointer\" onclick=\"location.href='?";
		$filter = new Filter($this->year_totals->getFilter());
		$filter->setYear($year);
		$result .= $filter->getQuery("");
		$result .= "'\"></div>";
		
		return $result;
	}
	

	public function render() {
		$result = "";
		
		$amounts = $this->year_totals->fetchOnce();
		if ($this->year_totals2 != NULL) {
			$amounts2 = $this->year_totals2->fetchOnce();
		}
		if (count($amounts) == 0 || (isset($amounts2) && count($amounts2) == 0)) {
			return "";
		}
		
		$amounts_max = abs(max($amounts));
		$amounts_min = abs(min($amounts));
		
		$temp_max = $amounts_max;
		$amounts_max = max($amounts_min,$amounts_max);
		$amounts_min = min($amounts_min,$temp_max);
		
		if (isset($amounts2)) {
			$amounts2_max = abs(max($amounts2));
			$amounts2_min = abs(min($amounts2));
			
			$temp_max = $amounts_max;
			$amounts2_max = max($amounts2_min,$amounts2_max);
			$amounts2_min = min($amounts2_min,$temp_max);
			
			$amounts_max = max($amounts2_max,$amounts_max);
			$amounts_min = min($amounts2_min,$amounts_min);
		}
		
		$bottom_line_type = "dashed";
		if ($amounts_min == 0) {
			$bottom_line_type = "solid";
		}
		
		$pad = 0;
		if ($amounts_min != 0) {
			$pad = 3;
		}
		
		if ($amounts_max > 0) {
			
			$result .= "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
			<tbody>
			<tr>
			<td style=\"color:#666;font-size:9px;padding-bottom:2px;border-bottom:1px dashed #CCC\" nowrap=\"nowrap\">$";
			$result .= number_abbreviate($amounts_max);
			$result .= "</td>
			<td colspan=\"".((count($amounts)*2)+1)."\" style=\"border-bottom:1px dashed #CCC\">
			</td>
			</tr>
			<tr>
			<td>
			</td>";
			
			$count = count($amounts);
			
			$i = 0;
			foreach ($amounts as $year=>$amount) {
				
				if ($i == 0) {
					$result .= "<td width=\"5\" rowspan=\"2\" style=\"border-bottom:1px ".$bottom_line_type." #CCC\">
					</td>";
				}
				
				
				if (isset($amounts2)) {
					$result .= "<td width=\"20\" valign=\"bottom\" rowspan=\"2\" style=\"border-bottom:1px ".$bottom_line_type." #CCC";
					if ($this->year_totals->getFilter()->getYear() == $year) {
						$result .= ";background-color:rgb(230,230,230)";
					}
					$result .= "\">";

					$result .= $this->renderBar($amount,$year,50,$amounts_min,$amounts_max,$pad,1);
					$result .= $this->renderBar($amounts2[$year],$year,50,$amounts_min,$amounts_max,$pad,2);
				}
				else {
					$result .= "<td width=\"15\" valign=\"bottom\" rowspan=\"2\" style=\"border-bottom:1px ".$bottom_line_type." #CCC";
					if ($this->year_totals->getFilter()->getYear() == $year) {
						$result .= ";background-color:rgb(230,230,230)";
					}
					$result .= "\">";
					
					$result .= $this->renderBar($amount,$year,100,$amounts_min,$amounts_max,$pad,1);
				}
				
				$result .= "</td>
				<td width=\"5\" rowspan=\"2\" style=\"border-bottom:1px ".$bottom_line_type." #CCC\">
				</td>";
				
				$i++;
			}
			
			$result .= "</tr>
			<tr>
			<td valign=\"bottom\" style=\"color:#666;font-size:9px;border-bottom:1px ".$bottom_line_type." #CCC;padding-bottom:2px\" nowrap=\"nowrap\">$";
			$result .= number_abbreviate((((-$pad)/($this->height))*(abs($amounts_max)-abs($amounts_min)))+abs($amounts_min));
			$result .= "
			</td>
			</tr>
			<tr>
			<td>
			</td>";
			$i = 0;
			foreach ($amounts as $year=>$amount) {
				if ($i%3 == 0) {
					$result .= "<td style=\"color:#666;font-size:9px;padding:2px\" colspan=\"3\" align=\"center\">&rsquo;";
					$result .= substr($year,-2);
					$result .= "</td>
					<td colspan=\"3\"></td>";
				}
				$i++;
			}
			$result .= "
			</tr>
			</tbody>
			</table>";
		}
		
		return $result;
    }
	
	public function __toString() {
		return $this->render();
	}
}
?>
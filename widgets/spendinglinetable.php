<?php
class SpendingLineTable
{
	private $lines = array();
	
	public function SpendingLineTable ($lines) {
		$this->lines = $lines;
	}
	
	public function setLines($lines) {
		$this->lines = $lines;
	}
	
	public function renderChecker ($checker) {
		if ($checker) {
			return "checker-a";
		}
		return "checker-b";
	}

	public function render() {
		
		$lines = $this->lines->fetchOnce();

		$result = "<table cellpadding=\"2\" cellspacing=\"2\" border=\"0\" class=\"numbers";
		if (($this->lines->getDate() < time()-(60*60*24*60) && $this->lines->getFilter()->getYear() == date("Y")) || ($this->lines->getDate() < time()-(60*60*24*30*5) && $this->lines->getFilter()->getYear() != date("Y"))) {
			$result .= " out-of-date";
		}
		$result .= "\" width=\"100%\">
		<tbody>";
		
		$checker = true;
		
		if (count($lines) == 0) {
			$result .= "<tr>";
			$result .= "<td colspan=\"4\" align=\"center\" style=\"color:#333;padding:10px\">None disclosed.</td>";
			$result .= "</tr>";
		}
		
		$i = 1;
		foreach ($lines as $row) {
			$result .= "<tr>";
			$result .= "<td align=\"right\" style=\"color:#666\" class=\"".$this->renderChecker($checker)."\" width=\"5%\">$i.</td>";
			$result .= "<td align=\"left\" class=\"".$this->renderChecker($checker)."\" title=\"".htmlentities($row['recipient-or-contractor'])."\" nowrap=\"nowrap\" width=\"30%\">".htmlentities(ucwords(strtolower(truncate(str_replace(" THE","",$row['recipient-or-contractor']),28))))."</td>";
			$result .= "<td align=\"left\" class=\"".$this->renderChecker($checker)."\" title=\"".htmlentities($row['description'],ENT_COMPAT,"ISO-8859-1",false)."\" nowrap=\"nowrap\" width=\"50%\">".htmlentities(truncate(preg_replace("/TAS::[0-9]{1,3} [0-9]{3,5}::TAS( |)/","",$row['description']),43),ENT_COMPAT,"ISO-8859-1",false)."</td>";
			$result .= "<td align=\"right\" width=\"13%\" style=\"color:".redgreenblack($row['amount']*-1)."\" class=\"".$this->renderChecker($checker)."\" width=\"10%\">$".htmlentities(number_format(abs($row['amount'])))."</td>";
			// $result .= "<td align=\"middle\" width=\"5%\"><a href=\"#\" class=\"toggle-details\">+</a></td>";
			$result .= "</tr>";
			/*$result .= "<tr style=\"display:none\">";
			$result .= "<td align=\"left\" colspan=\"5\">test</td>";
			$result .= "<tr>";*/
			
			$checker = !$checker;
			$i++;
		}
		$result .= "</tbody>
		</table>";
		
		return $result;
    }
	
	public function __toString() {
		return $this->render();
	}
}
?>
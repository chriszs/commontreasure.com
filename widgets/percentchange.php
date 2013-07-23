<?php
class PercentChange
{
	private $yeartotals = NULL;
	
	public function PercentChange ($yeartotals) {
		$this->yeartotals = $yeartotals;
	}
	
	public function setYearTotals($yeartotals) {
		$this->yeartotals = $yeartotals;
	}

	public function render() {
		$amounts = $this->yeartotals->fetchOnce();
		
		$result = "
		<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
		<tr>";
		$percent_change = round(((abs($amounts[$this->yeartotals->getFilter()->getYear()])-abs($amounts[$this->yeartotals->getFilter()->getYear()-1]))/abs($amounts[$this->yeartotals->getFilter()->getYear()]))*100);
		$result .= "
		<td style=\"font-size:20px;font-weight:bold;color:rgb(50,50,50)\" align=\"center\">
		";
		$result .= abs($percent_change);
		$result .= "%</td>
		<td width=\"40\">
		</td>
		</tr>
		<tr>
		<td style=\"font-size:10px;color:#666;border-bottom:1px solid #CCC;padding-bottom:15px\" align=\"center\">";
		if ($percent_change > 0) {
			$result .= "more";
		}
		else {
			$result .= "less";
		}
		$result .= " than<br />";
		$result .= $this->yeartotals->getFilter()->getYear()-1;
		$result .= "</td>
		</tr>
		<tr>";
		$percent_change = round(((abs($amounts[$this->yeartotals->getFilter()->getYear()])-abs($amounts[$this->yeartotals->getFilter()->getYear()-5]))/abs($amounts[$this->yeartotals->getFilter()->getYear()]))*100);
		$result .= "
		<td style=\"font-size:20px;font-weight:bold;padding-top:15px;color:rgb(50,50,50)\" align=\"center\">
		";
		$result .= abs($percent_change);
		$result .= "%
		</td>
		</tr>
		<tr>
		<td style=\"font-size:10px;color:#666\" align=\"center\">";
		if ($percent_change > 0) {
			$result .= "more";
		}
		else {
			$result .= "less";
		}
		$result .= " than<br />";
		$result .= $this->yeartotals->getFilter()->getYear()-5;
		$result .= "</td>
		</tr>
		</tbody>
		</table>";

		return $result;
    }
	
	public function __toString () {
		return $this->render();
	}
}
?>
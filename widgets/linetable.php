<?php
class LineTable
{
	private $lines = array();
	
	public function LineTable ($lines) {
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
		
		$result = "<table cellpadding=\"2\" cellspacing=\"2\" border=\"0\" class=\"numbers\" width=\"100%\">
		<tbody>";
		
		$lines = $this->lines->fetchOnce();
		
		$i = 1;
		$checker = true;
		foreach ($lines as $line) {
			$result .= "<tr>
			<td width=\"5%\" align=\"right\" style=\"color:#666\" class=\"".($this->renderChecker($checker))."\">$i.</td>
			<td width=\"0%\" align=\"right\" style=\"background-color:#";
			if ($i <= 4 && $this->lines->getTotal() != 0 && round(($line['amount']/$this->lines->getTotal())*100) > 3 && (($line['amount'] < 0 && $this->lines->getFilter()->getTable() != "receipts") || ($line['amount'] > 0 && $this->lines->getFilter()->getTable() == "receipts"))) {
				$result .= PieGraph::renderColor($i-1);
			}
			else {
				$result .= PieGraph::renderColor(4);
			}
			
			$result .= "\"></td>
			<td class=\"".($this->renderChecker($checker))."\" align=\"left\" width=\"48%\"";
			if (strlen($line['name']) > 42) {
				$result .= " title=\"".htmlentities($line['name'])."\"";
			}
			$result .= ">";
			if ($this->lines->getFilter()->getAccount() === false && $this->lines->getFilter()->getSourceCategory() === false) {
				$result .= "<a href=\"lines.php";
				
				$filter = new Filter($this->lines->getFilter());
				if ($filter->getTable() == "receipts") {
					$filter->setSourceCategory($line['code']);
				}
				else if ($filter->getAgency() === false) {
					$filter->setAgency($line['code']);
				}
				else if ($filter->getBureau() === false) {
					$filter->setBureau($line['code']);
				}
				else if ($filter->getAccount() === false) {
					$filter->setAccount($line['code']);
				}
				$result .= $filter->getQuery();
				
				$result .= "\">";
			}
			$result .= str_replace("--","&mdash;",htmlentities(truncate($line['name'],42)));
			if ($this->lines->getFilter()->getBureau() === false) {
				$result .= "</a>";
			}
			$result .= "</td>
			<td width=\"18%\" align=\"right\" style=\"color:".redgreenblack($line['amount'])."\" class=\"".($this->renderChecker($checker))."\" nowrap=\"nowrap\">$".number_format(abs($line['amount']))."</td>";
			$result .= "<td class=\"".($this->renderChecker($checker))."\" width=\"29%\"><div style=\"height:12px;";
				
			if ($this->lines->getFilter()->getYear() >= date("Y")) {
				$result .= "background-image:url('images/".redgreenblack($line['amount'])."-horizontal-estimate.jpg')";	
			}
			else {
				$result .= "background-color:".redgreenblack($line['amount']);
			}
			$result .= ";width:".round((abs($line['amount'])/$this->lines->getMax())*100)."%\"></div></td>
			</tr>
			";
			
			$checker = !$checker;
			$i++;
		}
		
		$result .= "
		</tbody>
		</table>
		";
		
		return $result;
    }
	
	public function __toString() {
		return $this->render();
	}
}
?>
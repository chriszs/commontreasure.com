<?php
class LineGraph
{
	private $lines = NULL;
	
	public function LineGraph ($lines) {
		$this->lines = $lines;
	}
	
	public function setLines($lines) {
		$this->lines = $lines;
	}

	public function render() {
		$lines_result = $this->lines->fetchOnce();
		
		$result = "<table cellpadding=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"scale\">
		<tbody>
		<tr>";
		
		$total = 0;
		$i = 1;
		foreach ($lines_result as $key=>$line) {
			if (($line['amount'] < 0 && $this->lines->getFilter()->getTable() != "receipts") || ($line['amount'] > 0 && $this->lines->getFilter()->getTable() == "receipts")) {
				$total += $line['amount'];
			}
			else {
				unset($lines_result[$key]);
			}
		}
		
		if (count($lines_result) == 0) {
			return "";
		}

		$i = 1;
		$other_total = 0;
		foreach ($lines_result as $line) {
			
			if ($i <= 4 && round(($line['amount']/$total)*100) > 3) {

				$result .= "
				<td style=\"height:13px;border-right:1px solid white;background-color:#".PieGraph::renderColor($i-1).";width:".round(($line['amount']/$total)*100)."%\">
				</td>
				";
			}
			else {
				$other_total += $line['amount'];
			}
			
			$i++;
		}
		if (round(($other_total/$total)*100) > 3) {
			$result .= "
			<td style=\"height:13px;background-color:#".(PieGraph::renderColor(4)).";width:".round(($other_total/$total)*100)."%\">
			</td>
			";
		}
		
		$result .= "</tr>
		<tr>";
		
		$i = 1;
		$percent_total = 0;
		foreach ($lines_result as $line) {
			if ($i <= 4 && round(($line['amount']/$total)*100) > 3) {
				$percent = (($line['amount']/$total)*100);
				$result .= "
				<td style=\"color:#666;font-size:9px;padding:3px\">".round($percent)."%</td>
				";
				
				$percent_total += $percent;
			}
			else {
				break;
			}
			
			$i++;
		}
		if (round(($other_total/$total)*100) > 3) {
			$percent = (($other_total/$total)*100);
			$result .= "
			<td style=\"color:#666;font-size:9px;padding:3px\">".round($percent)."%</td>
			";
			
			$percent_total += $percent;
		}
		
		$result .= "</tr>
		</tbody>
		</table>";
		
		return $result;
    }
	
	public function __toString () {
		return $this->render();
	}
}
?>
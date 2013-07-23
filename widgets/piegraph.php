<?php
class PieGraph
{
	private $lines = NULL;
	
	public function PieGraph ($lines) {
		$this->lines = $lines;
	}
	
	public function setYearTotals($year_totals) {
		$this->year_totals = $year_totals;
	}

	private function circleX ($percent) {
		$r = 65-1+15;
		$a = (($percent/100)*360);
		if ($a > 270) {
			$x = cos(deg2rad($a-270))*$r*-1;
		}
		else if ($a > 180) {
			$x = sin(deg2rad($a-180))*$r*-1;
		}
		else if ($a > 90) {
			$x = cos(deg2rad($a-90))*$r;
		}
		else {
			$x = sin(deg2rad($a))*$r;
		}
		$x += $r-15+30;
		return $x;
	}
	
	private function circleY ($percent) {
		$r = 65-1+15;
		$a = (($percent/100)*360);
		if ($a > 270) {
			$y = sin(deg2rad($a-270))*$r*-1;
		}
		else if ($a > 180) {
			$y = cos(deg2rad($a-180))*$r;
		}
		else if ($a > 90) {
			$y = sin(deg2rad($a-90))*$r;
		}
		else {
			$y = cos(deg2rad($a))*$r*-1;
		}
		$y += $r-7+30;
		return $y;
	}
	
	public function renderColor ($i=0) {
		// FF0000
		$colors = array("3399CC","80C65A","ed3293","FFCC33","BBCCED");
		if ($i < 4) {
			return $colors[$i];
		}
		return $colors[4];
	}

	public function render() {
		$lines_result = $this->lines->fetchOnce();
		
		$result = "<div style=\"position:relative;background-image:url('http://chart.apis.google.com/chart?cht=p&chs=150x150&chp=4.71&chco=";
		
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
				if ($i != 1) {
					$result .=",";
				}
				$result .= PieGraph::renderColor($i-1);
			}
			else {
				$other_total += $line['amount'];
			}
			
			$i++;
		}
		if (round(($other_total/$total)*100) > 3) {
			$result .= ",".PieGraph::renderColor(4);
		}
		
		$result .= "&chd=t:";
		$i = 1;
		foreach ($lines_result as $line) {
			if ($i <= 4 && round(($line['amount']/$total)*100) > 3) {
				if ($i != 1) {
					$result .=",";
				}
				$result .= round(($line['amount']/$total)*100);
			}
			else {
				break;
			}
			
			$i++;
		}
		if (round(($other_total/$total)*100) > 3) {
			$result .=",".round(($other_total/$total)*100);
		}
		$result .= "');width:190px;height:190px;background-repeat:no-repeat;top:-13px;background-position:34px 34px\">";
		
		$i = 1;
		$percent_total = 0;
		foreach ($lines_result as $line) {
			if ($i <= 4 && round(($line['amount']/$total)*100) > 3) {
				$percent = (($line['amount']/$total)*100);
				$result .= "<div title=\"".$line['name']."\" style=\"font-size:9px;color:#666;height:15px;width:30px;text-align:center;vertical-align:middle;position:absolute;z-index:".$i.";left:".round($this->circleX($percent_total+($percent/2)))."px;top:".round($this->circleY($percent_total+($percent/2)))."px\">".round($percent)."%</div>";
				$percent_total += $percent;
			}
			else {
				break;
			}
			
			$i++;
		}
		if (round(($other_total/$total)*100) > 3) {
			$percent = (($other_total/$total)*100);
			$result .= "<div title=\"Other\" style=\"font-size:9px;color:#666;height:15px;width:30px;text-align:center;vertical-align:middle;position:absolute;z-index:".$i.";left:".round($this->circleX($percent_total+($percent/2)))."px;top:".round($this->circleY($percent_total+($percent/2)))."px\">".round($percent)."%</div>";
			$percent_total += $percent;
		}
				
		$result .= "</div>";
		
		return $result;
    }
	
	public function __toString () {
		return $this->render();
	}
}
?>
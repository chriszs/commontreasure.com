<?php
class Filters
{
	private $filter = NULL;
	private $cached_results = false;
	
	public function Filters ($filter) {
		$this->filter = $filter;
	}
	
	public function setFilter($filter) {
		$this->cached_results = false;
		$this->filter = $filter;
	}
	
	private function renderCategoryName($category) {
		$result = "";
		if ($category == "discretionary") {
			$result = "Discretionary";
		}
		else if ($category == "mandatory") {
			$result = "Mandatory";
		}
		else if ($category == "net-interest") {
			$result = "Net Interest";
		}
		else if ($category == false) {
			$result = "All";
		}
		return $result;
	}

	public function render() {
		$filters = array(false,"mandatory","discretionary","net-interest");
		
		$result = "<div style=\"width:100%;text-align:left\">";
			
		$filter = new Filter($this->yeartotals->getFilter());
		$filter->setStartYear($filter->getYear());
		$filter->setEndYear($filter->getYear());
		
		$all = 0;
		
		foreach ($filters as $category) {
			$filter->setCategory($category);
			
			if ($this->yeartotals->getFilter()->getCategory() != $category) {
				$yeartotals = new YearTotals($filter);
				$yeartotals_result = $yeartotals->fetchOnce();
			}
			else {
				$yeartotals_result = $this->yeartotals->fetchOnce();
			}
			
			if ($category == false) {
				$all = $yeartotals_result[$filter->getYear()];
			}
			
			// $result .= "<div style=\"height:15px;margin-top:2px;width:".((abs($yeartotals_result[$filter->getYear()])/abs($all))*100)."%;background-color:".redgreenblack($yeartotals_result[$filter->getYear()])."\"></div>";
			$result .= "<div style=\"font-size:9px;color:#666\">";	
			if ($this->yeartotals->getFilter()->getCategory() != $category && $yeartotals_result[$filter->getYear()] != 0) {
				$result .= "<a href=\"?".$filter->getQuery("")."\">";
			}
			else if ($yeartotals_result[$filter->getYear()] != 0) {
				$result .= "<strong>";
			}
			$result .= $this->renderCategoryName($filter->getCategory());
			if ($this->yeartotals->getFilter()->getCategory() != $category && $yeartotals_result[$filter->getYear()] != 0) {
				$result .= "</a>";
			}
			else if ($yeartotals_result[$filter->getYear()] != 0) {
				$result .= "</strong>";
			}
			$result .= "</div>";
		}
		
		$result .= "</div>";
		
		return $result;
    }
	
	public function renderOnce() {
		if ($this->cached_results == false) {
			$this->cached_results = $this->render();
		}
		
		return $this->cached_results;
	}
}
?>
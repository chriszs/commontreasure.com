<?php
class YearTotals
{
    private $filter = NULL;
	private $cached_results = false;
	
	private $changed = -1;
	
	public function YearTotals($filter = false) {
		if ($filter == false) {
			$filter = new Filter();
		}
		$this->filter = $filter;
	}
	
	public function setFilter($filter) {
		$this->changed = -1;
		$this->filter = $filter;
	}
	
	public function getFilter () {
		return $this->filter;
	}
	
	public function fetch () {
		$years = array();
		for ($year = $this->filter->getStartYear();$year <= $this->filter->getEndYear();$year++) {
			$years[] = "SUM(`$year`) AS `$year`";
		}
		$cache = "";
		if ($this->filter->getEndYear()-$this->filter->getStartYear() > 14) {
			$cache = " SQL_CACHE";
		}
		// echo "SELECT$cache ".implode(", ",$years)." FROM `".mysql_real_escape_string($this->filter->getTable())."`".$this->filter->getWhere().";";
		$years = mysql_query("SELECT$cache ".implode(", ",$years)." FROM `".mysql_real_escape_string($this->filter->getTable())."`".$this->filter->getWhere().";");
		
		$results = array();
		
		if (!$years || mysql_num_rows($years) == 0) {
			return $results;
		}
		
		$result = mysql_fetch_assoc($years);
		
		foreach ($result as $year=>$amount) {
			$results[$year] = $amount*$this->filter->getMultiplier();
		}
		
		return $results;
	}
	
	public function fetchOnce() {
		if ($this->cached_results == false || $this->filter->getChanged() != $this->changed) {
			$this->cached_results = $this->fetch();
			$this->changed = $this->filter->getChanged();
		}
		
		return $this->cached_results;
	}
}
?>
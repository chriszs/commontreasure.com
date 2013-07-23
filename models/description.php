<?php
class Description
{
    private $filter = NULL;
	private $cached_results = false;
	private $changed = -1;
	
	public function Description($filter = false) {
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
		
		$descriptions = mysql_query("SELECT * FROM `descriptions` WHERE `filter` = '".mysql_real_escape_string($this->filter->getQuery("",array("agency","bureau","account","source-category"),true))."' AND `status` = 'approved' LIMIT 1;");
		
		if (!$descriptions || mysql_num_rows($descriptions) == 0) {
			return array();
		}
		
		$description = mysql_fetch_assoc($descriptions);
		
		return $description;
	}
	
	public function fetchOnce() {
		if ($this->cached_results === false || $this->filter->getChanged() != $this->changed) {
			$this->cached_results = $this->fetch();
			$this->changed = $this->filter->getChanged();
		}
		
		return $this->cached_results;
	}
}
?>
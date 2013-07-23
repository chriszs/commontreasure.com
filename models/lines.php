<?php
class Lines
{
    private $filter = NULL;
	
    private $agency = false;
    private $bureau = false;
    private $account = false;
	private $sourcecategory = false;
	
    private $max = false;
    private $min = false;
    private $total = false;
	private $cached_results = false;
	
	private $changed = -1;
	
	public function Lines($filter = false) {
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
		if ($this->filter->getTable() == "receipts") {
			if ($this->filter->getSourceCategory() !== false) {
				$lines = mysql_query("SELECT `account-code` AS `code`, `account-name` AS `name`, `source-category-name`, SUM(`".mysql_real_escape_string($this->filter->getYear())."`) AS `amount`, COUNT(*) AS `count` FROM `".mysql_real_escape_string($this->filter->getTable())."` WHERE `".mysql_real_escape_string($this->filter->getYear())."` != 0".$this->filter->getWhere(false)." GROUP BY `account-code` ORDER BY `amount` DESC");
			}
			else {
				$lines = mysql_query("SELECT `source-category-code` AS `code`, `source-category-name` AS `name`, SUM(`".mysql_real_escape_string($this->filter->getYear())."`) AS `amount`, COUNT(*) AS `count` FROM `".mysql_real_escape_string($this->filter->getTable())."` WHERE `".mysql_real_escape_string($this->filter->getYear())."` != 0".$this->filter->getWhere(false)." GROUP BY `source-category-code` ORDER BY `amount` DESC");
			}
		}
		else {
			if ($this->filter->getBureau() !== false && $this->filter->getAgency() !== false) {
				$lines = mysql_query("SELECT `account-code` AS `code`, `account-name` AS `name`, `bureau-name`, `agency-name`, `account-name`, SUM(`".mysql_real_escape_string($this->filter->getYear())."`) AS `amount`, COUNT(*) AS `count` FROM `".mysql_real_escape_string($this->filter->getTable())."` WHERE `".mysql_real_escape_string($this->filter->getYear())."` != 0".$this->filter->getWhere(false)." GROUP BY `account-code` ORDER BY `amount` DESC");
			}
			else if ($this->filter->getAgency() !== false) {
				$lines = mysql_query("SELECT `bureau-code` AS `code`, `bureau-name` AS `name`, `bureau-name`, `agency-name`, `account-name`, SUM(`".mysql_real_escape_string($this->filter->getYear())."`) AS `amount`, COUNT(*) AS `count` FROM `".mysql_real_escape_string($this->filter->getTable())."` WHERE `".mysql_real_escape_string($this->filter->getYear())."` != 0".$this->filter->getWhere(false)." GROUP BY `bureau-code` ORDER BY `amount` DESC");
			}
			else {
				$lines = mysql_query("SELECT `agency-code` AS `code`, `agency-name` AS `name`, SUM(`".mysql_real_escape_string($this->filter->getYear())."`) AS `amount`, COUNT(*) AS `count` FROM `".mysql_real_escape_string($this->filter->getTable())."` WHERE `".mysql_real_escape_string($this->filter->getYear())."` != 0".$this->filter->getWhere(false)." GROUP BY `agency-code` ORDER BY `amount` DESC");
			}
		}
		
		$results = array();
		$this->total = 0;
		$this->max = 0;
		$this->min = 0;
		
    	$this->agency = false;
    	$this->bureau = false;
    	$this->account = false;
		
		if (!$lines || mysql_num_rows($lines) == 0) {
			return $results;
		}
		
		$other_total = 0;
		$other_count = 0;
		$i = 1;
		while ($line = mysql_fetch_assoc($lines)) {
			
			if (isset($line['agency-name'])) {
				$this->agency = $line['agency-name'];
				unset($line['agency-name']);
			}
			if (isset($line['bureau-name'])) {
				$this->bureau = $line['bureau-name'];
				unset($line['bureau-name']);
			}
			if (isset($line['account-name'])) {
				$this->account = $line['account-name'];
				unset($line['account-name']);
			}
			if (isset($line['source-category-name'])) {
				$this->sourcecategory = $line['source-category-name'];
				unset($line['source-category-name']);
			}
			
			$line['amount'] *= $this->filter->getMultiplier();
			
			if ($this->max < abs($line['amount'])) {
				$this->max = abs($line['amount']);
			}
			if ($this->min > abs($line['amount'])) {
				$this->min = abs($line['amount']);
			}
			
			$this->total += $line['amount'];
			
			if ($this->filter->getTruncate() != false && $this->filter->getTruncate() <= $i) {
				$other_total += $line['amount'];
				$other_count++;;
			}
			else {
				$results[] = $line;
			}
			$i++;
		}
		
		if ($this->filter->getTruncate() !== false) {
			$results[] = array("code" => (-1),"name" => "Other","amount" => $other_total,"count" => $other_count);
		}
		
		return $results;
	}
	
	public function fetchOnce() {
		if ($this->cached_results === false || $this->filter->getChanged() != $this->changed) {
			$this->cached_results = $this->fetch();
			$this->changed = $this->filter->getChanged();
		}
		
		return $this->cached_results;
	}
	
	public function getTotal() {
		if ($this->total === false || $this->filter->getChanged() != $this->changed) {
			$this->fetch();
			$this->changed = $this->filter->getChanged();
		}
		return $this->total;
	}
	
	public function getMax() {
		if ($this->max === false || $this->filter->getChanged() != $this->changed) {
			$this->fetch();
			$this->changed = $this->filter->getChanged();
		}
		return $this->max;
	}
	
	public function getMin() {
		if ($this->min === false || $this->filter->getChanged() != $this->changed) {
			$this->fetch();
			$this->changed = $this->filter->getChanged();
		}
		return $this->min;
	}
	
	public function getAgency() {
		if ($this->filter->getAgency() !== false && ($this->agency === false || $this->filter->getChanged() != $this->changed)) {
			$this->fetch();
			$this->changed = $this->filter->getChanged();
		}
		return $this->agency;
	}
	
	public function getBureau() {
		if ($this->filter->getAgency() !== false && $this->filter->getBureau() !== false && ($this->bureau === false || $this->filter->getChanged() != $this->changed)) {
			$this->fetch();
			$this->changed = $this->filter->getChanged();
		}
		return $this->bureau;
	}
	
	public function getAccount() {
		if ($this->filter->getAgency() !== false && $this->filter->getBureau() !== false && $this->filter->getAccount() !== false && ($this->account === false || $this->filter->getChanged() != $this->changed)) {
			$this->fetch();
			$this->changed = $this->filter->getChanged();
		}
		return $this->account;
	}
	
	public function getSourceCategory() {
		if ($this->filter->getSourceCategory() !== false && ($this->sourcecategory === false || $this->filter->getChanged() != $this->changed)) {
			$this->fetch();
			$this->changed = $this->filter->getChanged();
		}
		return $this->sourcecategory;
	}
}
?>
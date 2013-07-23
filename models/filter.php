<?php
class Filter
{
	private $default = array( "start-year"=>1977, "end-year"=>2015, "year"=>2009, "type"=>"outlays", "agency"=>false, "bureau"=>false, "account"=>false, "category"=>false, "budget"=>false, "grant"=>false, "truncate"=>false, "offsets"=>false, "source-category"=>false );
	private $values = array();
	
	private $changed = 0;

	public function Filter ($values = false) {
		if ($values == false) {
			$this->values = $this->default;
		}
		else if (is_object($values)) {
			$this->values = $values->getValues();
		}
		else {
			$this->values = array_merge($values,$default);
		}
	}
	
	public function clear () {
		$this->values = $default;
	}
	
	protected function getValues () {
		return $this->values;
	}

	public function setTable($type) {
		if (in_array($type,array("outlays","receipts","budgetauth"))) {
			$this->changed++;
			$this->values['type'] = $type;
		}
	}

	public function getTable() {
		return $this->values['type'];
	}
	
	public function setBudget($budget) {
		if (in_array($budget,array(false,"on","off"))) {
			$this->changed++;
			$this->values['budget'] = $budget;
		}
	}

	public function getBudget() {
		return $this->values['budget'];
	}
	
	public function setGrant($grant) {
		if (in_array($grant,array(false,"grant","non-grant"))) {
			$this->changed++;
			$this->values['grant'] = $grant;
		}
	}

	public function getGrant() {
		return $this->values['grant'];
	}

	public function setTruncate($truncate) {
		if ($truncate === false) {
			$this->changed++;
			$this->values['truncate'] = $truncate;
		}
		else if (is_numeric($truncate) && intval($truncate) !== $this->values['truncate']) {
			$this->changed++;
			$this->values['truncate'] = intval($truncate);
		}
	}
	
	public function getTruncate() {
		return $this->values['truncate'];
	}

	public function getMultiplier() {
		if ($this->values['type'] == "outlays") {
			return -1000;
		}
		return 1000;
	}
	
	public function setOffsets($offsets) {
		if (in_array($offsets,array(false,"offsets","non-offsets"))) {
			$this->changed++;
			$this->values['offsets'] = $offsets;
		}
	}
	
	public function getOffsets() {
		return $this->values['offsets'];
	}

	public function setStartYear($year) {
		if (intval($year) > $this->default['end-year']) {
			$year = $this->default['end-year'];
		}
		if (intval($year) < $this->default['start-year']) {
			$year = $this->default['start-year'];
		}
		if (is_numeric($year) && intval($year) !== $this->values['start-year']) {
			$this->changed++;
			$this->values['start-year'] = intval($year);
		}
	}

	public function getStartYear() {
		return $this->values['start-year'];
	}

	public function setEndYear($year) {
		if (intval($year) > $this->default['end-year']) {
			$year = $this->default['end-year'];
		}
		if (intval($year) < $this->default['start-year']) {
			$year = $this->default['start-year'];
		}
		if (is_numeric($year) && intval($year) !== $this->values['end-year']) {
			$this->changed++;
			$this->values['end-year'] = intval($year);
		}
	}
	
	public function getEndYear() {
		return $this->values['end-year'];
	}
	
	public function setYear($year) {
		if (intval($year) > $this->values['end-year']) {
			$year = $this->values['end-year'];
		}
		if (intval($year) < $this->values['start-year']) {
			$year = $this->values['start-year'];
		}
		if (is_numeric($year) && intval($year) !== $this->values['year']) {
			$this->changed++;
			$this->values['year'] = intval($year);
		}
	}
	
	public function getYear() {
		return $this->values['year'];
	}
	
	public function setAgency($agency) {
		if ($agency === false) {
			$this->changed++;
			$this->values['agency'] = $agency;
		}
		else if (is_numeric($agency) && $agency >= 0 && intval($agency) !== $this->values['agency']) {
			$this->changed++;
			$this->values['agency'] = intval($agency);
		}
	}
	
	public function getAgency() {
		return $this->values['agency'];
	}
	
	public function setSourceCategory($sourcecategory) {
		if ($sourcecategory === false) {
			$this->changed++;
			$this->values['source-category'] = $sourcecategory;
		}
		else if (is_numeric($sourcecategory) && $sourcecategory >= 0 && intval($sourcecategory) !== $this->values['source-category']) {
			$this->changed++;
			$this->values['source-category'] = intval($sourcecategory);
		}
	}
	
	public function getSourceCategory() {
		return $this->values['source-category'];
	}
	
	public function setBureau($bureau) {
		if ($bureau === false) {
			$this->changed++;
			$this->values['bureau'] = $bureau;
		}
		else if (is_numeric($bureau) && $bureau >= 0 && intval($bureau) !== $this->values['bureau']) {
			$this->changed++;
			$this->values['bureau'] = intval($bureau);
		}
	}
	
	public function getBureau() {
		return $this->values['bureau'];
	}
	
	public function setAccount($account) {
		if ($account === false) {
			$this->changed++;
			$this->values['account'] = $account;
		}
		else if (is_numeric($account) && $account >= 0 && intval($account) !== $this->values['account']) {
			$this->changed++;
			$this->values['account'] = intval($account);
		}
	}
	
	public function getAccount() {
		return $this->values['account'];
	}
	
	public function setCategory($category) {
		if (in_array($category,array("mandatory","discretionary","net-interest",false))) {
			$this->changed++;
			$this->values['category'] = $category;
		}
	}
	
	public function getCategory() {
		return $this->values['category'];
	}
	
	public function getChanged() {
		return $this->changed;
	}
	
	public function getQuery ($prepend = "?",$use = array("year","type","agency","bureau","account","category","budget","grant","source-category"),$sort=false) {
		$result_array = array_diff_assoc(array_intersect_key($this->values,array_flip($use)),$this->default);
		
		if ($sort) {
			ksort($result_array);
		}
		
		$params = array();
		foreach ($result_array as $key=>$value) {
			$params[] .= urlencode($key)."=".urlencode($value);
		}
		
		$result = "";
		if (count($params) > 0) {
			$result .= $prepend.implode($params,"&");
		}
		return $result;
	}
	
	public function setQuery ($query) {
		if (isset($query['year'])) {
			$this->setYear($query['year']);
		}
		if (isset($query['start-year'])) {
			$this->setStartYear($query['start-year']);
		}
		if (isset($query['end-year'])) {
			$this->setEndYear($query['end-year']);
		}
		if (isset($query['category'])) {
			$this->setCategory($query['category']);
		}
		if (isset($query['budget'])) {
			$this->setBudget($query['budget']);
		}
		if (isset($query['grant'])) {
			$this->setGrant($query['grant']);
		}
		if (isset($query['bureau'])) {
			$this->setBureau($query['bureau']);	
		}
		if (isset($query['agency'])) {
			$this->setAgency($query['agency']);
		}
		if (isset($query['source-category'])) {
			$this->setSourceCategory($query['source-category']);
		}
		if (isset($query['account'])) {
			$this->setAccount($query['account']);
		}
		if (isset($query['type'])) {
			$this->setTable($query['type']);
		}
		if (isset($query['truncate'])) {
			$this->setTruncate($query['truncate']);
		}
	}
	
	public function getWhere ($prepend = true,$use = array("agency","bureau","account","category","budget","grant","source-category")) {
		$key_map = array( "agency"=>"agency-code", "source-category"=>"source-category-code", "bureau"=>"bureau-code", "account"=>"account-code", "category"=>"bea-category", "budget" =>"on-or-off-budget", "grant" =>"grant-non-grant-split" );
		$value_map = array( "grant"=>"Grant", "non-grant"=>"Nongrant", "discretionary"=>"Discretionary", "mandatory"=>"Mandatory", "net-interest"=>"Net interest", "on"=>"On-budget", "off"=>"Off-budget" );
		
		$result_array = array_diff_assoc(array_intersect_key($this->values,array_flip($use)),$this->default);

		$params = array();
		foreach ($result_array as $key=>$value) {
			if (array_key_exists($key,$key_map)) {
				$key = $key_map[$key];
			}
			if (array_key_exists($value,$value_map)) {
				$value = $value_map[$value];
			}
			
			$params[] .= "`".mysql_real_escape_string($key)."` = '".mysql_real_escape_string($value)."'";
		}
		
		$result = "";
		if (count($params) > 0) {
			$result .= " ";
			if ($prepend) {
				$result .= "WHERE ";
			}
			else {
				$result .= "AND ";
			}
			$result .= implode($params," AND ");
		}
		return $result;
	}
}
?>
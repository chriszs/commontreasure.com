<?php
class SpendingLines
{
    private $filter = NULL;
	private $cached_results = false;
	private $date = false;
	private $numfound = false;
	private $changed = -1;
	private $use_api = false;
	
	public function SpendingLines($filter = false) {
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
	
	public function setUseAPI ($use_api) {
		if (is_bool($use_api)) {
			$this->use_api = $use_api;
		}
	}
	
	private function curlRetrieve ($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}
	
	private function fetchAPI ($type,$year,$agency,$account) {
		$result = mysql_query("SELECT `outlays`.`treasury-agency-code`, `outlays`.`account-code` FROM `outlays` WHERE `agency-code` = '".intval($agency)."' AND  `account-code` = '".intval($account)."';");

		if ($row = mysql_fetch_assoc($result)) {
		/*
			$opts = array(
			  'http'=>array(
				'method'=>"GET",
				'user_agent'=>"Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8",
				'timeout'=>10
			  )
			);
			
			$context = stream_context_create($opts);
			*/
			$starttime = microtime(true);
			//$response = file_get_contents("http://www.usaspending.gov/$type/$type.php?program_source_agency_code=".intval($row['treasury-agency-code'])."&program_source_account_code=".str_pad(intval($row['account-code']), 4, "0", STR_PAD_LEFT)."&max_records=100&fiscal_year=$year",false,$context);
			$response = $this->curlRetrieve("http://www.usaspending.gov/$type/$type.php?program_source_agency_code=".intval($row['treasury-agency-code'])."&program_source_account_code=".str_pad(intval($row['account-code']), 4, "0", STR_PAD_LEFT)."&max_records=100&fiscal_year=$year");
			$endtime = microtime(true);
			
			$lines = array();
			
			libxml_use_internal_errors(true);
			
			if ($response !== false) {
				$numfound = -1;
				try {
					$xml = new SimpleXMLElement($response);
					
					if ($xml != false && isset($xml->result['numFound'])) {
						$numfound = intval($xml->result['numFound']);
					}
					
				} catch (Exception $e) {
					
				}
				
				mysql_query("START TRANSACTION;");
				mysql_query("DELETE FROM `spendingcalls` WHERE `type` = '".$type."' AND `year` = '".$year."' AND `treasury-agency-code` = '".intval($row['treasury-agency-code'])."' AND `account-code` = '".intval($row['account-code'])."';");
				mysql_query("INSERT INTO `spendingcalls` (`id`, `date`, `type`, `year`, `treasury-agency-code`, `account-code`, `num-found`, `time`) VALUES (NULL, '".time()."', '".$type."', '".$year."', '".intval($row['treasury-agency-code'])."', '".intval($row['account-code'])."', '".$numfound."', '".(($endtime-$starttime)*1000)."');");
				
				$callid = mysql_insert_id();
				
				// file_put_contents("calls/".$type."-".$year."-".intval($agency)."-".intval($account).".xml",$response);
				
				if (isset($xml) && $xml != false && isset($xml->result->doc)) {
					foreach ($xml->result->doc as $doc) {
						$line = array("call-id"=>$callid,"count"=>-1, "date"=>"", "type"=>"", "description"=>"", "category"=>"", "amount"=>0, "city-or-county"=>"", "state"=>"", "recipient-or-contractor"=>"", "call-type"=>$type, "num-found"=>$numfound, "date-retrieved"=>time());
						
						if (isset($doc->record_count)) {
							$line['count'] = $doc->record_count."";
						}
						if (isset($doc->DateSigned)) {
							$line['date'] = $doc->DateSigned."";
						}
						if (isset($doc->TypeofSpending)) {
							$line['type'] = $doc->TypeofSpending."";
						}
						if (isset($doc->ContractDescription)) {
							$line['description'] = $doc->ContractDescription."";
						}
						if (isset($doc->ProductorServiceCode) && $doc->ProductorServiceCode != "") {
							$line['category'] = $doc->ProductorServiceCode."";
						}
						else if (isset($doc->RecipientType)) {
							$line['category'] = $doc->RecipientType."";
						}
						if (isset($doc->DollarsObligated)) {
							$line['amount'] = $doc->DollarsObligated."";
						}
						if ((isset($doc->PrincipalPlaceCountyOrCity) && $doc->PrincipalPlaceCountyOrCity != "") || (isset($doc->PlaceofPerformanceState) && $doc->PlaceofPerformanceState != "")) {
							if (isset($doc->PrincipalPlaceCountyOrCity)) {
								$line['city-or-county'] = $doc->PrincipalPlaceCountyOrCity."";
							}
							if (isset($doc->PlaceofPerformanceState)) {
								$line['state'] = $doc->PlaceofPerformanceState."";
							}
						}
						else if (isset($doc->RecipientCity) || isset($doc->RecipientState)) {
							if (isset($doc->RecipientCity)) {
								$line['city-or-county'] = $doc->RecipientCity."";
							}
							if (isset($doc->RecipientState)) {
								$line['state'] = $doc->RecipientState."";
							}
						}
						if (isset($doc->RecipientOrContractorName)) {
							$line['recipient-or-contractor'] = $doc->RecipientOrContractorName."";
						}
						
						mysql_query("INSERT INTO `spendingtransactions` (`call-id`, `count`, `date`, `type`, `description`, `category`, `amount`, `city-or-county`, `state`, `recipient-or-contractor`) VALUES ('".mysql_real_escape_string($line['call-id'])."', '".mysql_real_escape_string($line['count'])."', '".mysql_real_escape_string($line['date'])."', '".mysql_real_escape_string($line['type'])."', '".mysql_real_escape_string($line['description'])."', '".mysql_real_escape_string($line['category'])."', '".mysql_real_escape_string($line['amount'])."', '".mysql_real_escape_string($line['city-or-county'])."', '".mysql_real_escape_string($line['state'])."', '".mysql_real_escape_string($line['recipient-or-contractor'])."');");
						
						$lines[] = $line;
					}
				}
				
				mysql_query("COMMIT;");
			}
			
		}
		
		return $lines;
	}

	
	public function fetch () {
		$results = array();
		
		if ($this->use_api) {
			$lines1 = $this->fetchAPI("fpds",$this->filter->getYear(),$this->filter->getAgency(),$this->filter->getAccount());
			$lines2 = $this->fetchAPI("faads",$this->filter->getYear(),$this->filter->getAgency(),$this->filter->getAccount());
			
			$results = array_merge($lines1,$lines2);
			
			$this->date = time();
			$this->numfound = 0;
			
			if (isset($lines1[0]) && $lines1[0]['num-found'] > 0) {
				$this->numfound += $lines1[0]['num-found'];
			}
			if (isset($lines2[0]) && $lines2[0]['num-found'] > 0) {
				$this->numfound += $lines2[0]['num-found'];
			}
			
			if (count($results) == 0) {
				return array();
			}
			
			$amounts = array();
			foreach ($results as $key=>$row) {
				unset($results[$key]['date-retrieved']);
				unset($results[$key]['num-found']);
				$amounts[$key] = $row['amount'];
			}
			
			
			array_multisort($amounts, SORT_DESC, $results);
		}
		else {
			$lines = mysql_query("SELECT `spendingtransactions`.*, `spendingcalls`.`type` AS `call-type`, `spendingcalls`.`num-found`, `spendingcalls`.`date` AS `date-retrieved` FROM (SELECT * FROM `outlays`".$this->filter->getWhere()." GROUP BY `treasury-agency-code`, `account-code`) AS `outlays` JOIN `spendingcalls` ON `outlays`.`treasury-agency-code` = `spendingcalls`.`treasury-agency-code` AND `outlays`.`account-code` = `spendingcalls`.`account-code` AND `spendingcalls`.`year` = '".$this->filter->getYear()."' LEFT JOIN `spendingtransactions` ON `spendingtransactions`.`call-id` = `spendingcalls`.`id` ORDER BY `amount` DESC;");
			
			$this->numfound = 0;
			
			$foundids = array();
			$foundtypes = array();
			
			if (!$lines || mysql_num_rows($lines) == 0) {
				$this->date = 0;
				return $results;
			}
			
			while ($line = mysql_fetch_assoc($lines)) {
				if ($this->date === false || $line['date-retrieved'] < $this->date) {
					$this->date = $line['date-retrieved'];
				}
				if (!in_array($line['call-type'],$foundtypes)) {
					$foundtypes[] = $line['call-type'];
				}
				if (!in_array($line['call-id'],$foundids)) {
					if ($line['num-found'] > 0) {
						$this->numfound += $line['num-found'];
					}

					$foundids[] = $line['call-id'];
				}
				
				if ($line['id'] != "") {
					$results[] = $line;
				}
			}
			
			if (count($foundtypes) < 2) {
				$this->date = 0;
			}
		}
		
		array_splice($results, 100);
		
		return $results;
	}
	
	public function fetchOnce() {
		if ($this->cached_results === false || $this->filter->getChanged() != $this->changed) {
			$this->cached_results = $this->fetch();
			$this->changed = $this->filter->getChanged();
		}
		
		return $this->cached_results;
	}
	
	public function getDate() {
		if ($this->date === false || $this->filter->getChanged() != $this->changed) {
			$this->fetch();
			$this->changed = $this->filter->getChanged();
		}
		return $this->date;
	}
	
	public function getNumFound() {
		if ($this->numfound === false || $this->filter->getChanged() != $this->changed) {
			$this->fetch();
			$this->changed = $this->filter->getChanged();
		}
		return $this->numfound;
	}
}
?>
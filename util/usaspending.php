<?php
$path = "../";
include($path."includes/default.php");

$opts = array(
  'http'=>array(
    'method'=>"GET",
	'user_agent'=>"Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8",
	'timeout'=>20
  )
);

$type = "fpds";
$year = 2010;

$context = stream_context_create($opts);

$result = mysql_query("SELECT `outlays`.`treasury-agency-code`, `outlays`.`account-code` FROM `outlays` LEFT JOIN `spendingcalls` ON `spendingcalls`.`treasury-agency-code` = `outlays`.`treasury-agency-code` AND `spendingcalls`.`account-code` = `outlays`.`account-code` WHERE CHAR_LENGTH(`outlays`.`account-code`) <= 4 AND `outlays`.`treasury-agency-code` != 0 AND `outlays`.`treasury-agency-code` != 20 AND `spendingcalls`.`account-code` IS NULL ORDER BY RAND() LIMIT 1;");

while ($row = mysql_fetch_assoc($result)) {

	$starttime = microtime(true);
	$response = file_get_contents("http://www.usaspending.gov/$type/$type.php?program_source_agency_code=".$row['treasury-agency-code']."&program_source_account_code=".str_pad($row['account-code'], 4, "0", STR_PAD_LEFT)."&max_records=100&fiscal_year=$year",false,$context);
	// $response = file_get_contents("calls/fpds-2010-0-.xml");
	$endtime = microtime(true);
	
	if ($response !== false) {
		$xml = new SimpleXMLElement($response);
		
		$numfound = -1;
		if (isset($xml->result['numFound'])) {
			$numfound = intval($xml->result['numFound']);
		}
		
		mysql_query("INSERT INTO `spendingcalls` (`id`, `date`, `type`, `year`, `treasury-agency-code`, `account-code`, `num-found`, `time`) VALUES (NULL, '".time()."', '".$type."', '".$year."', '".$row['treasury-agency-code']."', '".$row['account-code']."', '".$numfound."', '".(($endtime-$starttime)*1000)."');");
		
		$callid = mysql_insert_id();
		
		file_put_contents("calls/".$type."-".$year."-".$row['treasury-agency-code']."-".$row['account-code'].".xml",$response);
		
		$lines = array();
		if (isset($xml->result->doc)) {
			foreach ($xml->result->doc as $doc) {
				$line = array("call-id"=>$callid,"count"=>-1, "date"=>"", "type"=>"", "description"=>"", "category"=>"", "amount"=>0, "city-or-county"=>"", "state"=>"", "recipient-or-contractor"=>"");
				
				if (isset($doc->record_count)) {
					$line['count'] = $doc->record_count;
				}
				if (isset($doc->DateSigned)) {
					$line['date'] = $doc->DateSigned;
				}
				if (isset($doc->TypeofSpending)) {
					$line['type'] = $doc->TypeofSpending;
				}
				if (isset($doc->ContractDescription)) {
					$line['description'] = $doc->ContractDescription;
				}
				if (isset($doc->ProductorServiceCode) && $doc->ProductorServiceCode != "") {
					$line['category'] = $doc->ProductorServiceCode;
				}
				else if (isset($doc->RecipientType)) {
					$line['category'] = $doc->RecipientType;
				}
				if (isset($doc->DollarsObligated)) {
					$line['amount'] = $doc->DollarsObligated;
				}
				if ((isset($doc->PrincipalPlaceCountyOrCity) && $doc->PrincipalPlaceCountyOrCity != "") || (isset($doc->PlaceofPerformanceState) && $doc->PlaceofPerformanceState != "")) {
					if (isset($doc->PrincipalPlaceCountyOrCity)) {
						$line['city-or-county'] = $doc->PrincipalPlaceCountyOrCity;
					}
					if (isset($doc->PlaceofPerformanceState)) {
						$line['state'] = $doc->PlaceofPerformanceState;
					}
				}
				else if (isset($doc->RecipientCity) || isset($doc->RecipientState)) {
					if (isset($doc->RecipientCity)) {
						$line['city-or-county'] = $doc->RecipientCity;
					}
					if (isset($doc->RecipientState)) {
						$line['state'] = $doc->RecipientState;
					}
				}
				if (isset($doc->RecipientOrContractorName)) {
					$line['recipient-or-contractor'] = $doc->RecipientOrContractorName;
				}
				
				mysql_query("INSERT INTO `spendingtransactions` (`call-id`, `count`, `date`, `type`, `description`, `category`, `amount`, `city-or-county`, `state`, `recipient-or-contractor`) VALUES ('".mysql_real_escape_string($line['call-id'])."', '".mysql_real_escape_string($line['count'])."', '".mysql_real_escape_string($line['date'])."', '".mysql_real_escape_string($line['type'])."', '".mysql_real_escape_string($line['description'])."', '".mysql_real_escape_string($line['category'])."', '".mysql_real_escape_string($line['amount'])."', '".mysql_real_escape_string($line['city-or-county'])."', '".mysql_real_escape_string($line['state'])."', '".mysql_real_escape_string($line['recipient-or-contractor'])."');");
				
				$lines[] = $line;
			}
		}
	}
}

?>
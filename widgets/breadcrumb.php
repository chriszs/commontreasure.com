<?php
class Breadcrumb
{
	private $lines = NULL;
	private $level = false;
	private $truncate = 40;
	
	public function Breadcrumb ($lines) {
		$this->lines = $lines;
	}
	
	public function setLevel($level) {
		if ($level === false) {
			$this->level = $level;
		}
		else if (is_numeric($level) && $level > 0 && intval($level) !== $this->level) {
			$this->level = intval($level);
		}
	}
	
	public function setLines($lines) {
		$this->lines = $lines;
	}
	
	private function renderTableName($table) {
		$result = "";
		if ($table == "outlays") {
			$result = "Spending";
		}
		else if ($table == "receipts") {
			$result = "Income";
		}
		else if ($table == "budgetauth") {
			$result = "Authority";
		}
		return $result;
	}

	public function render() {
		$result = "<h2><a href=\"./";
		
		$url_filter = new Filter($this->lines->getFilter());
		$result .= $url_filter->getQuery("?",array("year"));
		
		$result .= "\">".htmlentities($this->lines->getFilter()->getYear())."</a> &raquo; "; 
		
		if ($this->lines->getFilter()->getAgency() !== false || $this->lines->getFilter()->getSourceCategory() !== false || $this->lines->getFilter()->getCategory() !== false) {
			$result .= "<a href=\"lines.php";
			
			$url_filter = new Filter($this->lines->getFilter());
			$url_filter->setCategory(false);
			$url_filter->setAgency(false);
			$url_filter->setBureau(false);
			$url_filter->setAccount(false);
			$url_filter->setSourceCategory(false);
			$result .= $url_filter->getQuery();
			
			$result .= "\">";
			$result .= $this->renderTableName($this->lines->getFilter()->getTable());
			$result .= "</a> &raquo; ";
			
			if ($this->lines->getFilter()->getCategory() !== false) {
				if ($this->lines->getFilter()->getAgency() !== false) {
					$result .= "<a href=\"lines.php";
				
					$url_filter = new Filter($this->lines->getFilter());
					$url_filter->setAgency(false);
					$url_filter->setBureau(false);
					$url_filter->setAccount(false);
					$url_filter->setSourceCategory(false);
					$result .= $url_filter->getQuery();
					
					$result .= "\">";
				}
				$result .= ucfirst($this->lines->getFilter()->getCategory());
				if ($this->lines->getFilter()->getAgency() !== false) {
					$result .= "</a> &raquo; ";
				}
			}
			if ($this->lines->getFilter()->getBureau() !== false && $this->level != 3) {
				$result .= "<a href=\"lines.php";
				
				$url_filter = new Filter($this->lines->getFilter());
				$url_filter->setBureau(false);
				$url_filter->setAccount(false);
				$result .= $url_filter->getQuery();
				
				$result .= "\">";
			}
			if ($this->lines->getFilter()->getSourceCategory() !== false) {
				$result .= "<span";
				if (strlen($this->lines->getSourceCategory()) > $this->truncate) {
					$result .= " title=\"".str_replace("--","&mdash;",htmlentities($this->lines->getSourceCategory()))."\"";
				}
				$result .= ">".str_replace("--","&mdash;",htmlentities(truncate($this->lines->getSourceCategory(),$this->truncate)))."</span>";
			}
			else {
				$result .= "<span";
				if (strlen($this->lines->getAgency()) > $this->truncate) {
					$result .= " title=\"".str_replace("--","&mdash;",htmlentities($this->lines->getAgency()))."\"";
				}
				$result .= ">".str_replace("--","&mdash;",htmlentities(truncate($this->lines->getAgency(),$this->truncate)))."</span>";
			}
			if ($this->lines->getFilter()->getBureau() !== false && $this->level != 3) {
				$result .= "</a> &raquo; ";
			}
			if ($this->lines->getFilter()->getAccount() !== false && $this->level != 4) {
				$result .= "<a href=\"lines.php";
				
				$url_filter = new Filter($this->lines->getFilter());
				$url_filter->setAccount(false);
				$result .= $url_filter->getQuery();
				
				$result .= "\">";
			}
			if ($this->lines->getFilter()->getBureau() !== false && $this->level != 3) {
				$result .= "<span";
				if (strlen($this->lines->getBureau()) > $this->truncate-5) {
					$result .= " title=\"".str_replace("--","&mdash;",htmlentities($this->lines->getBureau()))."\"";
				}
				$result .= ">".str_replace("--","&mdash;",htmlentities(truncate($this->lines->getBureau(),$this->truncate-5)))."</span>";
			}
			if ($this->lines->getFilter()->getAccount() !== false && $this->level != 4) {
				$result .= "</a> &raquo; ";
				$result .= "<span";
				if (strlen($this->lines->getAccount()) > $this->truncate-10) {
					$result .= " title=\"".str_replace("--","&mdash;",htmlentities($this->lines->getAccount()))."\"";
				}
				$result .= ">".str_replace("--","&mdash;",htmlentities(truncate($this->lines->getAccount(),$this->truncate-10)))."</span>";
			}
		}
		else {
			$result .= $this->renderTableName($this->lines->getFilter()->getTable());
		}
		$result .= "</h2>";
		return $result;
    }
	
	public function __toString () {
		return $this->render();
	}
}
?>
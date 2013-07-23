<?php
class Descriptions
{
	private $description = NULL;
	
	public function Descriptions ($description) {
		$this->description = $description;
	}
	
	public function setDescription($description) {
		$this->description = $description;
	}

	public function render() {
		$description = $this->description->fetchOnce();

		$result = "<div class=\"widget description\">";
		
		if (count($description) == 0) {
			$result .= "<textarea cols=\"28\" rows=\"2\">What is this money used for?</textarea>";
		}
		else {
			$result .= "<span style=\"font-size:12px\">".htmlentities($description['description'])."</span>";
			if ($description['source-url'] != "") {
				$result .= "<div class=\"note source\" style=\"padding-bottom:0px\">Source: <a href=\"".htmlentities($description['source-url'])."\">".htmlentities($description['source-name'])."</a></div>";
			}
		}
		
		$result .= "</div>";

		return $result;
    }
	
	public function __toString () {
		return $this->render();
	}
}
?>
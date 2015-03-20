<?php

class CurrencyController {


	private $dbh = null;
	
	/**
	  * Constructor for class
	  * Params: Takes in an array of parameters
	  * Returns: None
	  * 
	  */
	public function __construct($params) {
		if(isset($params['dbh']) && $params['dbh'] instanceOf PDO) {
			$this->dbh = $params['dbh'];
			// Because of a small error in setting up the local MAMP instance
			// I had problems connect to the correct database while connection through the socket
			// Normally this would not be here
			$this->dbh->query("USE wmf");
		} else {
			throw new Exception("Invalid or missing database handler");
		}
	}

	/**
	  * Make a cURL request based on some kind of URL and returns the specified result
	  * Params: String $url
	  * Returns: cURL array result
	  *
	  * At 
	  */
	public function getData($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$output = curl_exec($ch);
		curl_close($ch);  

		if(empty($output)) {
			throw new Exception('cURL did not return any data');
		}

		return $output;
	}

	public function parseData($data){
		$xml = new SimpleXMLElement($data);
		return $xml;
	}

	public function insertData($data) {
		$tableName = 'daily_currency';
		if($data instanceOf SimpleXMLElement) {
			foreach($data as $xmlObj) {
				$currency = $xmlObj->currency;
				$rate = $xmlObj->rate;
			
				$query = "INSERT INTO $tableName SET currency = " . $this->dbh->quote($currency) . ", rate=". $this->dbh->quote($rate) . 
				" ON DUPLICATE KEY UPDATE currency = " . $this->dbh->quote($currency) . ", rate=". $this->dbh->quote($rate);
				$numRows = $this->dbh->exec($query);

				if(false === is_nan($numRows)){
					// uncomment for debugging
					// echo "Insert/Updated $numRows into $tableName\n";
				} else {
					throw new Exception("SQL Error: " . $this->dbh->errorInfo());
				}
				

			}
		} else {
			throw new Exception("data passed is not an xml object", $data);
		}
	}

	public function convertCurrency($string) {
		$output = preg_split( '/[,:| ]/', $string );

		$query = "SELECT rate FROM daily_currency WHERE currency = " . $this->dbh->quote($output[0]);
		$stmt = $this->dbh->prepare($query);
		$stmt->execute();
		$row = $stmt->fetch();
		
		$outputStr = "USD " . ($output[1] * $row[0]);
		return $outputStr;

	}

	public function convertCurrencies(array $array) {
		$outputArray = array();
		foreach($array AS $pair) {
			$output = preg_split( '/[,:| ]/', $pair );

			$query = "SELECT rate FROM daily_currency WHERE currency = " . $this->dbh->quote($output[0]);
			$stmt = $this->dbh->prepare($query);
			$stmt->execute();
			$row = $stmt->fetch();

			array_push($outputArray, "USD " . ($output[1] * $row[0]));
		}

		return $outputArray;
	}

}

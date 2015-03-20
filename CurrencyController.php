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
			// I had problems connecting to the correct database using the mysql MAMP socket
			// Normally this would not be here
			$this->dbh->query("USE wmf");
		} else {
			throw new Exception("Invalid or missing database handler");
		}
	}

	/**
	  * Make a cURL request based on some kind of URL and return the specified result
	  * Params: String $url
	  * Returns: cURL array result
	  *
	  * At some point this could leverage some kind of framework such as Symfony 1/2 or Guzzle
	  * Might also want to make this more customizeable by adding our own layer on top of cURL
	  * to handle a variety of requests
	  */
	public function getData($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$output = curl_exec($ch);
		curl_close($ch);  
		
		// If we get no response or no data throw an exception
		// getting no data in this case means an external error
		// TODO: See if throwing an exception is appropriate here
		// Might be better to make a note and move on
		if(empty($output)) {
			throw new Exception('cURL did not return any data');
		}

		return $output;
	}

	/**
	  * Given an array, return an XML object
	  * Params: array $data
	  * Returns: SimpleXMLElement object
	  *
	  * This really doesn't do a whole bunch except create an object. Probably would need to
	  * add some more sanity checks or maybe even parse from Obj to Array to make the other 
	  * functions more modular
	  */
	public function parseData($data){
		$xml = new SimpleXMLElement($data);
		return $xml;
	}

	/**
	  * Takes in a SimpleXMLElement Obj, insert/updates the data in the object
	  * Params: SimpleXMLElement object $data
	  * Returns: None
	  *
	  * To make this more modular we should standardize the data. So instead of getting a SimpleXML object we should be getting an array
	  * I made an INSERT/DUPLICATE KEY UPDATE query because this would be more efficient at insert new data/updating new data.  Since the 
	  * currencies rates probably change daily, we should need to call this method only once rather than a serious of methods
	  * to update our table
	  */
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

	/**
	  * Takes in a string of the format CURRENCY_NAME CURRENCY_RATE and returns a conversion to USD
	  * Params: String $string
	  * Returns: String $outputStr
	  *
	  * This is pretty specific to only converting from currency A to USD.  IF this were to be modular 
	  * enough to convert from currency A to currency B we would need to refactor this to
	  * get multiple currencies.  
	  */
	public function convertCurrencyToUSD($string) {
		$output = preg_split( '/[,:| ]/', $string );

		$query = "SELECT rate FROM daily_currency WHERE currency = " . $this->dbh->quote($output[0]);
		$stmt = $this->dbh->prepare($query);
		$stmt->execute();
		$row = $stmt->fetch();
		
		$outputStr = "USD " . ($output[1] * $row[0]);
		return $outputStr;

	}

	/**
	  * Takes in an array of currencies in the format array(CURRENCY_NAME CURRENCY_RATE, CURRENCY_NAME CURRENCY_RATE, ...) and returns an array
	  * of currencies all convereted to USD
	  * Params: array $array
	  * Returns: array $outputArray
	  * 
	  * I made the preg_split flexible enough to accept a variety of inputs such as:
	  * JPY 5000; JPY,5000; JPY:5000
	  * This is to allow for flexible data to be insert to our object
	  */
	public function convertCurrenciesToUSD(array $array) {
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

<?php

require("CurrencyController.php");

$url = 'https://wikitech.wikimedia.org/wiki/Fundraising/tech/Currency_conversion_sample?ctype=text/xml&action=raw';
$user = 'wmf_user';
$pass = 'password';
$socket = 'unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;dbname=wmf';

try {
    $dbh = new PDO("mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;dbname=wmf", $user, $pass);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

$params = array(
	'dbh' => $dbh
);

$cc = new CurrencyController($params);
try{
	// Get data from the API
	$data = $cc->getData($url);
	// Parse data into an array
	$xmlData = $cc->parseData($data);
	// Insert data into database
	$cc->insertData($xmlData);
	// Convert single currency
	echo "new: " .$cc->convertCurrency("JPY:5000") . "\n";
	// Convert multiple currencies
	$result = $cc->convertCurrencies(array( 'JPY 5000', 'CZK 62.5' ));
	print_r($result);

} catch(Exception $e) {
	echo "Caught exception: " . print_r($e->getMessage());
}

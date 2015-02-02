<?php 

namespace xavierfaucon\BravoIds;

class BravoIds
{
	
	/**
	 * List of characters used to encode in base62 ([a-z0-9A-Z])
	 */
	private static $index = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	
	/**
	 * List of characters to remove
	 */
	private static $unSafeCharacters = 'aeiouyAEIOUY';

	/**
	 * The base size of characters used to encode/decode
	 */
	private static $base = '';
	
	/**
	 * List of characters used to encode in base62 as Array
	 */
	private static $indexArray = array();
	
	/**
	 * List of characters to remove as Array
	 */
	private static $unSafeCharactersArray = array();
	
	/**
	 * To which range the base $number should be scaled
	 */
	private static $minRange = '';
	
	/**
	 * Initialization
	 */
	private static function init($safeCharacters) 
	{
		self::$indexArray = str_split(self::$index);
		
		if ($safeCharacters === true) {

			self::$unSafeCharactersArray = str_split(self::$unSafeCharacters);

			self::$indexArray = array_diff(self::$indexArray, self::$unSafeCharactersArray);
			self::$indexArray = array_values(self::$indexArray);
		}
		
		self::$base = count(self::$indexArray);
	}

	/**
	 * Shuffle the variable self::$indexArray using the $passPhrase provided
	 */
	private static function shuffle($passPhrase) 
	{
	
		# $i = 49
		for ($i = self::$base - 1, $v = 0, $p = 0; $i > 0; $i--, $v++) {

			$v = $v % strlen($passPhrase);
			$int = ord($passPhrase[$v]);
			$p = $p + $int;
			$j = ($int + $v + $p) % $i;

			$temp = self::$indexArray[$j];
			self::$indexArray[$j] = self::$indexArray[$i];
			self::$indexArray[$i] = $temp;
		}
	}
	
	/**
	 * Checks arguments provided.
	 */
	private static function argumentsCheck($input, $passPhrase, $minHashLength, $method) 
	{

		switch ($method) {
			case 'encrypt': $inputType = '$number'; break;
			case 'decrypt': $inputType = '$hash'; break;
		}
		
		if (is_null($input)) {
            throw new \InvalidArgumentException($inputType.' must not be blank.');
        }

		if (strlen($passPhrase) < 1) {
			throw new \InvalidArgumentException('$passPhrase must not be blank');
        }
		
		if (!is_int($minHashLength)) {
            throw new \InvalidArgumentException('$minHashLength is not a valid integer.');
        }
		
		# To get a 5 characters hash you need a number >= base^(5-1)
		# Example : 50^4 = 6 250 000. A value above 6 250 000 will return a hash >= 5 characters
		# To get a X characters hash you need a number >= base^(X-1)
		self::$minRange = ($minHashLength <= 1) ? 0 : pow(self::$base, $minHashLength - 1);

		$minHashLengthUpperLimit = floor(log(PHP_INT_MAX, self::$base));
		
		if ($minHashLength < 0 || $minHashLength > $minHashLengthUpperLimit) {
            throw new \InvalidArgumentException('$minHashLength must be comprised between [0 ; '. $minHashLengthUpperLimit . '].');
        }
		
		if ($method == 'encrypt') {
		
			if (!is_int($input)) {
				throw new \InvalidArgumentException('$number is not a valid integer.');
			}
			
			if ($input < 0 || $input > PHP_INT_MAX ) {
				throw new \InvalidArgumentException('$number must be comprised between [0 ; '. PHP_INT_MAX . '].');
			}

			if ((self::$minRange + $input) > PHP_INT_MAX) {
				throw new \InvalidArgumentException('$number cannot be greater than ' . (PHP_INT_MAX - self::$minRange) . '.');
			}
		}
	}

	/**
	 * Digital number (base10) -> alphabet letter code (base50 or base62)
	 * Example : 64645646 -> LF1bq
	 */
	public static function encrypt($number, $passPhrase, $minHashLength = 0, $safeCharacters = true) 
	{
		self::init($safeCharacters);
		
		self::argumentsCheck($number, $passPhrase, $minHashLength, 'encrypt');
		
		self::shuffle($passPhrase);
		
		# Example : $number = 100, $minHashLength = 5
		# 100 + 50^4 = 6 250 100 = 6 250 100
		$number = $number + self::$minRange;
		
		return self::encode($number, $minHashLength, $passPhrase);
	}
	
	/**
	 * alphabet letter code (base50 or base62) -> Digital number (base10)
	 * Example : LF1bq -> 64645646
	 */
	public static function decrypt($hash, $passPhrase, $minHashLength = 0, $safeCharacters = true) 
	{
		self::init($safeCharacters);
		
		self::argumentsCheck($hash, $passPhrase, $minHashLength, 'decrypt');
		
		self::shuffle($passPhrase);
		
		$number = self::decode($hash, $minHashLength, $passPhrase);
		
		return ($number - self::$minRange);
	}

	/**
	 * Encode from base10 to base50 ($safeCharacters = true)
	 */
	private static function encode($number, $minHashLength, $passPhrase) 
	{
		$hash = '';
		
		while ($number > 0) {
			$r = bcmod($number, self::$base);
			$hash = self::$indexArray[$r] . $hash;
			$number = ($number-$r)/self::$base; // equivalent to floor
		}
		
		return $hash;
	}

	/**
	 * Decode from base50 ($safeCharacters = true) to base10
	 */
	private static function decode($hash, $minHashLength, $passPhrase) 
	{
		$hashArray = str_split($hash);
		
		$number = 0;
		
		foreach($hashArray as $value) {
            $number = $number * self::$base + array_search($value, self::$indexArray);
        }
		
		return	$number;
	}
}

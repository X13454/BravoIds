<?php

use xavierfaucon\BravoIds\BravoIds;

class BravoIdsTest extends PHPUnit_Framework_TestCase 
{
	public function testEncrypt() 
	{
		$hash = BravoIds::encrypt(123, 'myPassPhrase');
		$this->assertEquals('tr', $hash);

		$hash = BravoIDs::encrypt (123, 'myPassPhrase', 4); 
		$this->assertEquals('kNtr', $hash);

		$hash = BravoIDs::encrypt (123, 'myPassPhrase', 4, false);
		$this->assertEquals('5u59', $hash);
	}


	public function testDecrypt() 
	{
		$number = BravoIds::decrypt('tr', 'myPassPhrase');  
		$this->assertEquals(123, $number);

		$number = BravoIds::decrypt('kNtr', 'myPassPhrase', 4);
		$this->assertEquals(123, $number);

		$number = BravoIDs::decrypt ('5u59', 'myPassPhrase', 4, false);
		$this->assertEquals(123, $number);
	}
}

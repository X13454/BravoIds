BravoIDs [![Build Status](https://travis-ci.org/xavierfaucon/BravoIds.svg?branch=master)](https://travis-ci.org/xavierfaucon/BravoIds)
========

Converts IDs (base10) to Hashed IDs (base50 or 62).

The BravoIDs class offers two methods : BravoIDs::encrypt and BravoIDs::decrypt.

### BravoIDs::encrypt

### Description

```php
<?php
/**
 *
 * @param int $number The ID you want to convert into a hash
 * @param string $passPhrase  A passphrase that you must provide
 * @param int $minHashLength Specify the minimum length for the output.
 * @param bool $safeCharacters Remove all the vowels to generate the output to avoid curse words
 *
 * @return string The hash of a converted ID.
 */
BravoIDs::encrypt($number, $passPhrase, $minHashLength = 0, $safeCharacters = true);
?>
```

### BravoIDs::encrypt

### Description

```php
<?php

 BravoIDs::decrypt(string $hash, string $passPhrase, int $minHashLength = 0, bool $safeCharacters = true);
 
?>
```

Returns an ID from a hash.

#### Parameters:

- $hash : The hash you want to convert into an ID
- $passPhrase : A passphrase that you must provide
- $minHashLength  (optional - default is 0) : Specify the minimum length for the output.
- $safeCharacters (optional - default is true) : Remove all the vowels to generate the output to avoid curse words

NB : in order to decrypt a hash, you must specify the exact same variables for $passPhrase, $minHashLength and $safeCharacters taht you used for BravoID::encrypt.

### Basic Usage:

To simply generate IDS :

```php
<?php

echo BravoIDs::encrypt (123, 'myPassPhrase');
// "tr"

echo BravoIDs::decrypt ('tr', 'myPassPhrase');
// "123"

?>
```

### Advanced Usage:

To increase hash length :

```php
<?php

echo BravoIDs::encrypt (123, 'myPassPhrase', 4);
// "kNtr"

echo BravoIDs::decrypt ('kNtr', 'myPassPhrase', 4);
// "123"

?>
```

NB: if you use a 32-bit builds of PHP you won't be able to set a $minHashLengh > 5. This is explained in the Limitations part.

To allow all characters including vowels (cursing words could appear) :

```php
<?php

echo BravoIDs::encrypt (123, 'myPassPhrase', 4, false);
// "5u59"

echo BravoIDs::decrypt ('5u59', 'myPassPhrase', 4, false);
// "123"

?>
```

### Limitations

Depending on your build of PHP you will be limited:

- 32-bit builds of PHP : Integers can be from -2147483648 to 2147483647
- 64-bit builds of PHP: Integers can be from -9223372036854775808 to 9223372036854775807

I did not run tests on 64-bit build of php.

On 32-bit, the limitation means that you'll be able to convert ids up to 2 147 483 647 and not one more.

Moreover, when you use the variable $minHashLength, you reduce the number of available ids.

A computation is done to find the minimum value that your number should have to reach the required HashLength):

For example, to get a hash of at least X characters, you'll need a ~number >= base * (X - 1)~.

Where base is the number of characters used to convert your numbers (base50 with $safeCharacters = true and base62 with $safeCharacters = false).

Once we know what is this minimum value, all your ids are "scaled" to that value, '0' would become 6 250 000 (base50 and $minHashLength = 4).

In this case, this has for effect that your 2 147 483 647 has 6 250 000 less value possible ('0' to '6 249 999').

### Thanks

Thanks to ivanakimov and https://github.com/ivanakimov/hashids.php from which I borrowed the _consistent_shuffle method.

Thanks to Kevin van Zonneveld who wrote this article from which I took the idea of removing the vowels to get safe Words: http://kvz.io/blog/2009/06/10/create-short-ids-with-php-like-youtube-or-tinyurl/

BravoID is named after the function offered on this article which was called AlphaID :-)

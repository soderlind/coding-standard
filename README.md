# My PHP CodeSniffer sniff


## Sniff

`FullyQualifiedInternalFunctions`, a PHP CodeSniffer sniff that Checks if all internal PHP functions are fully qualified.

## Install

Install it with `composer require --dev soderlind/coding-standard`

## Use

`./vendor/bin/phpcs -p test.php --standard=./vendor/soderlind/coding-standard/src/ruleset.xml`



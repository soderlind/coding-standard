# My PHP CodeSniffer sniff


## Sniff

`FullyQualifiedInternalFunctions`, a PHP CodeSniffer sniff that Checks if all internal PHP functions are fully qualified. e.g.:

Ekspeks




## Install

Install it with `composer require --dev soderlind/coding-standard`

## Use

### Check
`./vendor/bin/phpcs -p test.php --standard=./vendor/soderlind/coding-standard/src/ruleset.xml`

```
FILE: test.php
--------------------------------------------------------------------------------
FOUND 2 ERRORS AFFECTING 2 LINES
--------------------------------------------------------------------------------
 5 | ERROR | [x] Function array_diff() should be referenced via a fully
   |       |     qualified name, e.g.: \array_diff()
 7 | ERROR | [x] Function in_array() should be referenced via a fully qualified
   |       |     name, e.g.: \in_array()
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 2 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------
```

### Fix

`./vendor/bin/phpcbf -p test.php --standard=./vendor/soderlind/coding-standard/src/ruleset.xml`

```
PHPCBF RESULT SUMMARY
----------------------------------------------------------------------
FILE                                                  FIXED  REMAINING
----------------------------------------------------------------------
test.php                                              2      0
----------------------------------------------------------------------
A TOTAL OF 2 ERRORS WERE FIXED IN 1 FILE
----------------------------------------------------------------------
```


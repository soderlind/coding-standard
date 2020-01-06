# My PHP CodeSniffer sniff


## Sniff

`FullyQualifiedGlobalFunctions`, a PHP CodeSniffer sniff that checks if the global PHP functions are missing a backslash.

### Why?:

Function resolution without the backslash forces the PHP globals to verify for each function call if function belongs to current namespace or the global namespace. With the backslash PHP does not check the current namespace and therefore execution is faster.

## Install

Install it with `composer require --dev soderlind/coding-standard`

## Use

### Check
`./vendor/bin/phpcs -p test.php --standard=FullyQualifiedGlobalFunctions`

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

`./vendor/bin/phpcbf -p test.php --standard=FullyQualifiedGlobalFunctions`

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

To not have to pass the arguments to the command line, create a `phpcs.xml.dist` in the project folder. Something like this:

```xml
<?xml version="1.0"?>
<ruleset name="MyProject">

	<arg name="extensions" value="php" />
	<exclude-pattern>/vendor/*</exclude-pattern>
	<rule ref="WordPress" />

	<!-- Here's the rule for my sniff -->
	<rule ref="FullyQualifiedGlobalFunctions" />

</ruleset>
```

## License

MIT License

Copyright (c) 2020 Per Søderlind

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
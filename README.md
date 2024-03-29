# CoreDump

[![Tests](https://github.com/themichaelhall/coredump/workflows/tests/badge.svg?branch=master)](https://github.com/themichaelhall/coredump/actions)
[![StyleCI](https://styleci.io/repos/165721365/shield?style=flat&branch=master)](https://styleci.io/repos/165721365)
[![License](https://poser.pugx.org/michaelhall/coredump/license)](https://packagist.org/packages/michaelhall/coredump)
[![Latest Stable Version](https://poser.pugx.org/michaelhall/coredump/v/stable)](https://packagist.org/packages/michaelhall/coredump)
[![Total Downloads](https://poser.pugx.org/michaelhall/coredump/downloads)](https://packagist.org/packages/michaelhall/coredump)

Create a core dump file with debug information.

## Requirements

- PHP >= 8.0

## Install with composer

``` bash
$ composer require michaelhall/coredump
```

## Basic usage

``` php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use MichaelHall\CoreDump\CoreDump;

// Creates a core dump and add some extra content.
// Superglobals like $_SERVER, $_GET, $_POST etc. are added automatically.
$coreDump = new CoreDump();
$coreDump->add('Foo', 'Bar');

// Outputs the core dump.
echo $coreDump;

// Saves the core dump with an auto-generated file name in the current directory.
// Also returns the file name.
$coreDump->save();

// As above, but saves the core dump in the /tmp-directory.
$coreDump->save('/tmp');
```

## The core dump file

The core dump file contains human-readable debug information from:

- An optional ```Throwable``` passed to the ```CoreDump``` constructor.
- Optional variables added by the ```add()``` method.
- Superglobals like ```$_SERVER```, ```$_GET```, ```$_POST``` etc.

## License

MIT

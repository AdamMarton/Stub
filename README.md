# Stub

A stub generator written in PHP. Useful for creating stubs for static code analyzers when the actual code not available/cannot autoloaded, or just to speed up parsing.

The script is tested against these frameworks/libraries:

* WordPress (~5secs)
* Laravel (~2.5secs)

## Features

* PSR-2 compliant output

## Usage

```
$ composer require adammarton/stub
```

```
<?php

require_once('vendor\autoload.php');

$stub = new AdamMarton\Stub\Stub('<directory-to-parse>');
$stub->generate('<output-directory>');
```

## Known Issues

* Fix issue when output directory isn't created automatically
* Fix `namespace` declaration issue: when more than one empty lines after `namespace`

## Todos

* Test with more frameworks
* Restore logging feature
* Implement CLI commands/options
* Generate output in a single file optionally

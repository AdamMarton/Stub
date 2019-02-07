# Stub

A stub generator written in PHP. Useful for creating stubs for static code analyzers when the actual code not available/cannot autoloaded, or just to speed up parsing.

The script is tested against these frameworks/libraries:

* WordPress
* Laravel

## Features

* PSR-2 compliant output

## Usage

```
<?php

require_once('<wherever-it-is>\stubgen.php');

$stubgen = new AdamMarton\Stubgen\Stubgen('<directory-to-parse>');
$stubgen->generate('<output-directory>');
```

## Known Issues

* Lambda function detection should be improved
* Should output `array`s as multiline
* Line length should be fixed in `class`, `use` definitions and in `class`-properties
* Fix issue when output directory isn't created automatically

## Todos

* Include tests
* Test with more frameworks
* Restore logging feature
* Display status during parsing
* Make it pluggable via composer
* Experiment with [`Reflection`](http://php.net/manual/en/book.reflection.php), maybe it can speed up formatting

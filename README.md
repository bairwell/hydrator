# Bairwell\Hydrator

[![Latest Stable Version](https://poser.pugx.org/bairwell/hydrator/v/stable)](https://packagist.org/packages/bairwell/hydrator)
[![License](https://poser.pugx.org/bairwell/hydrator/license)](https://packagist.org/packages/bairwell/hydrator)
[![Coverage Status](https://coveralls.io/repos/bairwell/hydrator/badge.svg?branch=master&service=github)](https://coveralls.io/github/bairwell/hydrator?branch=master)
[![Build Status](https://travis-ci.org/bairwell/hydrator.svg?branch=master)](https://travis-ci.org/bairwell/hydrator)
[![Total Downloads](https://poser.pugx.org/bairwell/hydrator/downloads)](https://packagist.org/packages/bairwell/hydrator)

This is a PHP 7 [Composer](https://getcomposer.org/) compatible library for providing an annotation based hydration facility.

# WARNING

This code is currently ALPHA standard - it has had limited testing and is still under active development.

UNIT TESTS ARE CURRENTLY BROKEN.

## Standards

The following [PHP FIG](http://www.php-fig.org/psr/) standards should be followed:

 * [PSR 1 - Basic Coding Standard](http://www.php-fig.org/psr/psr-1/)
 * [PSR 2 - Coding Style Guide](http://www.php-fig.org/psr/psr-2/)
 * [PSR 3 - Logger Interface](http://www.php-fig.org/psr/psr-3/)
 * [PSR 4 - Autoloading Standard](http://www.php-fig.org/psr/psr-4/)
 * [PSR 5 - PHPDoc Standard](https://github.com/phpDocumentor/fig-standards/tree/master/proposed) - (still in draft)
 * [PSR 12 - Extended Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/proposed/extended-coding-style-guide.md) - (still in draft)
 
### Standards Checking
[PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer/) highlights potential coding standards issues.

`vendor/bin/phpcs`

PHP CS will use the configuration in `phpcs.xml.dist` by default.

To see which sniffs are running add "-s"

## Unit Tests
[PHPUnit](http://phpunit.de) is installed for unit testing (tests are in `tests`)

To run unit tests:
`vendor/bin/phpunit`

For a list of the tests that have ran:
`vendor/bin/phpunit --tap`

To restrict the tests run:
`vendor/bin/phpunit --filter 'Cors\\Exceptions\\BadOrigin'`

or just

`vendor/bin/phpunit --filter 'ExceptionTest'`

for all tests which have "Exception" in them and:
`vendor/bin/phpunit --filter '(ExceptionTest::testEverything|ExceptionTest::testStub)'`

to test the two testEverything and testStub methods in the ExceptionTest class.


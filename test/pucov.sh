#!/bin/sh
alias phpunit='/cygdrive/e/Apps/php/php-5.6.26-Win32-VC11-x64/php /Apps/phpunit/phpunit'

if [[ $# -ne 1 ]]; then
   echo 'Need a test file as input parmeter.'
   exit 1
fi

phpunit --verbose --coverage-html ./coverage-html --include-path ".." --bootstrap data/Defaults.php -c PHPUnitConfig.xml $1

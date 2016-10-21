#!/bin/sh
alias phpunit='/cygdrive/e/Apps/php/php-5.6.26-Win32-VC11-x64/php /Apps/phpunit/phpunit'

if [[ $# -ne 1 ]]; then
   echo 'Need a test file as input parmeter.'
   exit 1
fi

phpunit --verbose --include-path ".." --bootstrap data/Defaults.php $1

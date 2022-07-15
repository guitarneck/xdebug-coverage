#/bin/env bash
script="$1"
shift

php="`which php.exe`"
if [[ -z $php ]]
then
   php=php
else
   php=php.exe
fi

$php -d auto_prepend_file=coverage/XDebugCoverageBootstrap.php -d xdebug.mode=coverage -f $script -- $*
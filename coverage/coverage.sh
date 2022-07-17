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

if $php -r "ini_get('xdebug.modess') !== false ? exit(0) : exit(-1);"
then
   xdebug_cov=xdebug.mode=coverage
else
   xdebug_cov=xdebug.coverage_enable=on
fi

$php -d auto_prepend_file=coverage/XDebugCoverageBootstrap.php -d $xdebug_cov -f $script -- $* 2>/dev/null
@echo off
set rest=
if [%2] neq [] (
   for /f "tokens=1,*" %%i in ("%*") do set "rest=%%j"
)

php -r "ini_get('xdebug.mode') !== false ? exit(0) : exit(-1);"
if %errorlevel% == -1 set "xdebug_cov=xdebug.coverage_enable=on"
if %errorlevel% == 0 set "xdebug_cov=xdebug.mode=coverage"

php -d auto_prepend_file=coverage\XDebugCoverageBootstrap.php -d %xdebug_cov% -f %1 -- %rest%
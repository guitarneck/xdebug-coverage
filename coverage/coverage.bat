@echo off
set rest=
if [%2] neq [] (
   for /f "tokens=1,*" %%i in ("%*") do set "rest=%%j"
)
php -d auto_prepend_file=coverage\XDebugCoverageBootstrap.php -d xdebug.mode=coverage -f %1 -- %rest%
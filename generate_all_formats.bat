@echo off & setlocal
set all=export serialize json dot dump coverage coveralls lcov clover
for %%a in (%all%) do @coverage\coverage tests\Hello.test.php --includes=src/ --format=%%a
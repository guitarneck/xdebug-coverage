<?php
namespace coverage;

@xdebug_set_filter(XDEBUG_FILTER_CODE_COVERAGE,XDEBUG_PATH_EXCLUDE,[dirname(__DIR__)]);

// error_reporting(E_ALL);
// ini_set('display_errors',1);

require_once   'sources/XDebugCoverage.class.php';

$coverage = new XDebugCoverage();
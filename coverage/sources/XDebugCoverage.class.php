<?php

namespace coverage;

include_once   'XDebugConfiguration.class.php';

const XDBGCOV_NOT_LOADED   = "\n\033[31m[ERROR]\033[0m \033[1;30mxdebug extension is not loaded.\033[0m\n";
const XDBGCOV_NOT_COVERAGE = "\n\033[33m[STOP]\033[0m \033[1;30mcoverage is not activated.\033[0m\n";
const XDBGCOV_COVERAGE_OK  = "\n\033[32m[DONE]\033[0m \033[1;30mcoverage ended nicely in %0.4fs.\033[0m\n";
const XDBGCOV_DEBUG        = "\n\033[34m[DEBUG]\033[0m%s";
const XDBGCOV_FILE_ERROR   = "\n\033[31m[ERROR]\033[0m \033[1;30moutput path failed: No such directory or not writable.\033[0m\n";

if ( @defined(XDEBUG_PATH_BLACKLIST) ):
   @define('XDBGCOV_PATH_INCLUDE', XDEBUG_PATH_WHITELIST);
   @define('XDBGCOV_PATH_EXCLUDE', XDEBUG_PATH_BLACKLIST);
else:
   define('XDBGCOV_PATH_INCLUDE', XDEBUG_PATH_INCLUDE);
   define('XDBGCOV_PATH_EXCLUDE', XDEBUG_PATH_EXCLUDE);
endif;

class XDebugCoverage
{
   protected $active;
   protected $caller;

   protected $timer;

   function __construct ()
   {
      self::isMemoryLimitAvailable();
      $this->onDebugExit();

      if ( ! $this->isLoaded() ) die(XDBGCOV_NOT_LOADED);
      if ( ! $this->isCoverage() ) die(XDBGCOV_NOT_COVERAGE);

      $this->active = true;
      $this->timer = microtime(true);

      $this->xdebugFilter();

      xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE | XDEBUG_CC_BRANCH_CHECK );
   }

   function __destruct ()
   {
      if ( ! $this->active ) return;

      $coverage = xdebug_get_code_coverage();
      xdebug_stop_code_coverage();

      $coverage   = $this->filter($coverage);
      $coverage   = $this->format($coverage);

      if ( @file_put_contents($this->reportPath(),$coverage) === false ) die(XDBGCOV_FILE_ERROR);

      $timer = (microtime(true) - $this->timer)/*  * 1000.0 */;
      printf(XDBGCOV_COVERAGE_OK,$timer);
   }

   protected
   function reportPath ()
   {
      $config  = \XDebugConfiguration::instance();

      $script  = $_SERVER['argv'][0];
      $basename= basename($script,$config->renaming->extension);
      $name    = sprintf($config->renaming->rename,$basename);

      $path    = $this->output_path();
      array_push($path,$name);
      return implode(DIRECTORY_SEPARATOR,$path);
   }

   protected
   function onDebugExit ()
   {
      $config = \XDebugConfiguration::instance();
      if ( ! $config->debug ) return;

      include_once 'format/DataFormater.class.php';
      $formater = new \DataFormater($config->format);

      $R = "\033[31m";
      $G = "\033[32m";
      $E = "\033[0m";

      $debug = "\n";
      $debug .= sprintf("xdebug is loaded   : %s%s\n",$this->isLoaded() ? "{$G}ok" : "{$R}no",$E);
      $debug .= sprintf("coverage activated : %s%s\n",$this->isCoverage() ? "{$G}ok" : "{$R}no",$E);
      $debug .= sprintf("format             : %s%s%s\n",$formater->isValid($config->formats) ? $G : $R,$config->format,$E);
      $debug .= "\n";
      $debug .= sprintf("includes\n%s\n",str_repeat('-',20));
      $debug .= ! $config->includes ? '<N/A>' : implode("\n",$config->includes);
      $debug .= "\n\n";
      $debug .= sprintf("excludes\n%s\n",str_repeat('-',20));
      $debug .= ! $config->excludes ? '<N/A>' : implode("\n",$config->excludes);
      $debug .= "\n\n";
      $debug .= sprintf("output directory   : %s\n",implode(DIRECTORY_SEPARATOR,$this->output_path()));
      $debug .= sprintf("memory             : used %s, peak %s\n",self::memoryString(self::memoryUsage()),self::memoryString(self::memoryPeak()));

      printf(XDBGCOV_DEBUG,$debug);
      exit;
   }

   protected
   function output_path (): array
   {
      $config = \XDebugConfiguration::instance();

      $path = explode(',',$config->output);
      $path = array_map(function($v){
         $v = str_replace('{DIR}',__DIR__,$v);
         return $v;
      },$path);
      return $path;
   }

   protected
   function xdebugFilter ()
   {
      $config = \XDebugConfiguration::instance();

      if ( $config->includes )
         xdebug_set_filter(
            XDEBUG_FILTER_CODE_COVERAGE,
            XDBGCOV_PATH_INCLUDE,
            $config->includes
         );
      elseif ( $config->excludes )
         xdebug_set_filter(
            XDEBUG_FILTER_CODE_COVERAGE,
            XDBGCOV_PATH_EXCLUDE,
            $config->excludes
         );
   }

   protected
   function filter ( $datas )
   {
      $config = \XDebugConfiguration::instance();
      if ( $config->noExtraFilter ) return $datas;

      if ( $config->includes )
         $datas = array_filter($datas,function($k) use($config) {
            $found = false;
            foreach ( $config->includes as $include )
            {
               $found = ($include === substr($k,0,strlen($include)) );
               if ( $found ) break;
            }
            return $found;
         },ARRAY_FILTER_USE_KEY);

      if ( $config->excludes )
         $datas = array_filter($datas,function($k) use($config) {
            $found = false;
            foreach ( $config->excludes as $exclude )
            {
               $found = ($exclude === substr($k,0,strlen($exclude)) );
               if ( $found ) break;
            }
            return !$found;
         },ARRAY_FILTER_USE_KEY);

      return $datas;
   }

   protected
   function format ( $datas )
   {
      $config = \XDebugConfiguration::instance();

      include_once 'format/DataFormater.class.php';
      $formater = \DataFormater::factory($config->format,$config->formats);

      return $formater->render($datas);
   }

   static
   function hasStarted ()
   {
      return xdebug_code_coverage_started();
   }

   /*
      Before PHP 5.2.1, this only works if PHP is compiled with --enable-memory-limit.
      From PHP 5.2.1 and later this function is always available.
   */
   static private
   function isMemoryLimitAvailable ()
   {
      if ( ! version_compare(phpversion(), '5.2.1', '>') )
      {
         ob_start();
         phpinfo();
         $out = ob_get_clean();
         preg_match('/^Configure Command =>(.*)$/m',$out,$matches);
         return strpos($matches[0],'--enable-memory-limit') !== false;
      }

      return true;
   }

   static
   function memoryString ( $bytes )
   {
      static $units = array('b','Kb','Mb','Gb','Tb','Pb','Eb','Zb','Yb');
      $expo = floor(log($bytes,1024));
      $frac = $bytes / pow(1024,$expo);
      return sprintf('%.2f %s',$frac,$units[$expo]);
   }

   static
   function memoryUsage ()
   {
      if ( ! self::isMemoryLimitAvailable() ) return 0;
      return xdebug_memory_usage();
   }

   static
   function memoryPeak ()
   {
      if ( ! self::isMemoryLimitAvailable() ) return 0;
      return xdebug_peak_memory_usage();
   }

   function isLoaded ()
   {
      return false !== extension_loaded('xdebug');
   }

   function isCoverage ()
   {
      return (false !== strpos(ini_get('xdebug.mode'),'coverage') || 1 == ini_get('xdebug.coverage_enable'));
   }
}
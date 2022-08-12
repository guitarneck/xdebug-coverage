<?php
require_once   'CLIArguments.class.php';
require_once   'format/DataFormater.class.php';

const XDBGCOV_CONFIG_JSON  = __DIR__."/XDebugCoverage.json";
const XDBGCOV_CONFIG_ERROR = "\033[31m[ERROR]\033[0m \033[1;30mconfiguration has bad json format.\033[0m\n";

class XDebugConfiguration
{
   private  $config;

   static
   function instance (): XDebugConfiguration
   {
      static $instance=null;
      if ( $instance === null ) $instance = new XDebugConfiguration();
      return $instance;
   }

   private
   function __construct ()
   {
      if ( ! $this->loadConfiguration() ) die(XDBGCOV_CONFIG_ERROR);

      $this->update();
   }

   private
   function __clone() {}

   function __get ( $name )
   {
      return $this->config->{$name};
   }

   private
   function loadConfiguration (): bool
   {
      $this->config = json_decode(file_get_contents(XDBGCOV_CONFIG_JSON));
      return JSON_ERROR_NONE === json_last_error();
   }

   private
   function update (): void
   {
      CLIArguments::onHelp(function(CLIArguments $arguments){
         $this->formatHelp($arguments);
      });
      $arguments = new CLIArguments($this->config->arguments);

      if ( $arguments->includes !== null )
         $this->config->includes = $this->realpath($arguments->includes);

      if ( $arguments->excludes !== null )
         $this->config->excludes = $this->realpath($arguments->excludes);

      if ( $arguments->format !== null )
         $this->config->format = $arguments->format;

      if ( $arguments->output !== null )
         $this->config->output = $arguments->output;

      $this->config->debug = $arguments->debug;

      $this->config->noExtraFilter = $arguments->noExtraFilter;
   }

   public
   function formatHelp ( CLIArguments $arguments ): void
   {
      if ( $arguments->format === null ) return;

      $formater = new DataFormater($arguments->format);

      if ( !$formater->inFormats($this->config->formats) )
         die(sprintf(XDBGCOV_FORMATER_TYPE_NOT_FOUND,$arguments->format));

      $class = $formater->formaterClass($this->config->formats);
      $script  = $formater->formaterScript($class);
      if ( !file_exists($script) )
         die(XDBGCOV_FORMATER_CLASS_NOT_FOUND);

      include $script;
      print $class::help();
   }

   private
   function realpath ( string $paths ): array
   {
      return array_map(function($p){
         $path = realpath($p) ?? $p;
         if ( is_dir($path) ) $path .= DIRECTORY_SEPARATOR;
         return $path;
      },explode(',',$paths));
   }
}
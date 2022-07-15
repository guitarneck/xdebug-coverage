<?php

class JSONFormat extends AbstractFormat
{
   protected $flags;
   protected $depth;

   function __construct( array $params=null )
   {
      $config = XDebugConfiguration::instance();
      $config->renaming->rename = '%s.json';

      if ( !isset($params['flags']) ) $params['flags'] = JSON_PRETTY_PRINT;
      if ( !isset($params['depth']) ) $params['depth'] = 512;

      $this->flags = $params['flags'];
      $this->depth = $params['depth'];
   }

   function render ( array $datas ): string
   {
      ksort($datas);
      foreach ( $datas as $sname => $info ) $this->sortLinesFunctions($info['functions']);
      return json_encode($datas,$this->flags,$this->depth);
   }

   static
   function help(): string
   {
      return sprintf(XDBGCOV_FORMAT_PARMAMETER_HEAD,DataFormater::class2format(__CLASS__),"[?][flags=][&][depth=]")
           . sprintf(XDBGCOV_FORMAT_PARMAMETER_PARM,"flags : (int)")
           . sprintf(XDBGCOV_FORMAT_PARMAMETER_PARM,"depth : (int)")
           . sprintf(XDBGCOV_FORMAT_PARMAMETER_PARM,"see https://www.php.net/manual/fr/function.json-decode.php")
           . XDBGCOV_FORMAT_PARMAMETER_FOOT;
   }
}
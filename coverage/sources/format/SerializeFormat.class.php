<?php

class SerializeFormat extends AbstractFormat
{
   function __construct( array $params=null )
   {
      $config = XDebugConfiguration::instance();
      $config->renaming->rename = '%s.xser';
   }

   function render ( array $datas ): string
   {
      ksort($datas);
      foreach ( $datas as $sname => $info ) $this->sortLinesFunctions($info['functions']);
      return serialize($datas);
   }

   static
   function help(): string
   {
      return sprintf(XDBGCOV_FORMAT_WITH_NO_PARAMETER,DataFormater::class2format(__CLASS__));
   }
}
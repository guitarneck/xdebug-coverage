<?php

class ExportFormat extends AbstractFormat
{
   function __construct ( array $params = null )
   {
      $config = XDebugConfiguration::instance();
      $config->renaming->rename = '%s.xexp';
   }

   function render ( array $datas ): string
   {
      ksort($datas);
      foreach ( $datas as $sname => $info ) $this->sortLinesFunctions($info['functions']);

      return var_export($datas, true);
   }

   static
   function help (): string
   {
      return sprintf(XDBGCOV_FORMAT_WITH_NO_PARAMETER,DataFormater::class2format(__CLASS__));
   }
}
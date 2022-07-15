<?php

class DumpFormat extends AbstractFormat
{
   protected $pathInsteadOfBranch;

   function __construct(array $params = null)
   {
      $config = XDebugConfiguration::instance();
      $config->renaming->rename = '%s.dmp';
   }

   function render ( array $datas ): string
   {
      include 'contribs/dump_branch_coverage.php';
      ob_start();
      dump_branch_coverage($datas);
      return ob_get_clean();
   }

   static
   function help(): string
   {
      return sprintf(XDBGCOV_FORMAT_WITH_NO_PARAMETER,DataFormater::class2format(__CLASS__));
   }
}
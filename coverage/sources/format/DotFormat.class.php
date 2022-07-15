<?php

class DotFormat extends AbstractFormat
{
   protected $pathInsteadOfBranch;

   function __construct(array $params = null)
   {
      $config = XDebugConfiguration::instance();
      $config->renaming->rename = '%s.dot';

      if (!isset($params['pathInsteadOfBranch'])) $params['pathInsteadOfBranch'] = true;

      $this->pathInsteadOfBranch = $params['pathInsteadOfBranch'];
   }

   function render ( array $datas ): string
   {
      include 'contribs/branch_coverage_to_dot.php';
      return branch_coverage_to_dot($datas, $this->pathInsteadOfBranch);
   }

   static
   function help(): string
   {
      return sprintf(XDBGCOV_FORMAT_PARMAMETER_HEAD,DataFormater::class2format(__CLASS__),"[?][pathInsteadOfBranch]")
           . sprintf(XDBGCOV_FORMAT_PARMAMETER_PARM,"pathInsteadOfBranch : (boolean) Use paths, not branches.")
           . XDBGCOV_FORMAT_PARMAMETER_FOOT;
   }
}
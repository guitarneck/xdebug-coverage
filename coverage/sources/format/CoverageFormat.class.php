<?php

const COVERAGE_COVERAGE_SKIP_MAIN = true;

const COVERAGE_PERCENTS_SCRIPT   = "%s\n%s\n   - Lines: Hits %0u, Total: %0u (%0u%%)\n\n";
const COVERAGE_PERCENTS_FUNCTION = "   %s\n";
const COVERAGE_PERCENTS_BRANCHES = "      - Branches: Hits %0u, Total %0u (%0u%%)\n";
const COVERAGE_PERCENTS_PATHS    = "      - Paths   : Hits %0u, Total %0u (%0u%%)\n";

class CoverageFormat extends AbstractFormat
{
   protected $pathInsteadOfBranch;

   function __construct( array $params=null )
   {
      $config = XDebugConfiguration::instance();
      $config->renaming->rename = '%s.xcov';
   }

   function render ( array $datas ): string
   {
      $output = '';

      ksort($datas);
      foreach ( $datas as $sname => $file )
      {
         if ( empty( $file['functions'] ) ) continue;

         // lines
         $hits = array();
         $main = $this->mainLine($file['lines']);
         foreach( $file['lines'] as $line => $hit )
         {
            if ( COVERAGE_COVERAGE_SKIP_MAIN && $line === $main ) continue;
            if ( $hit === LINE_UNEXECUTABLE ) continue;
            $hits[] = $hit === LINE_WAS_EXECUTED ? 1 : 0;
         }
         $nb = count($hits);
         $hi = array_sum($hits);

         $output .= sprintf(COVERAGE_PERCENTS_SCRIPT,
                           $sname,
                           str_repeat('-',strlen($sname)),
                           $hi,
                           $nb,
                           round($hi/$nb*100,2));

         // functions
         ksort( $file['functions'] );
         foreach ( $file['functions'] as $fname => $function )
         {
            if ( COVERAGE_COVERAGE_SKIP_MAIN && $this->isMain($fname) ) continue;

            $output .= sprintf(COVERAGE_PERCENTS_FUNCTION,$fname);

            // branches
            $hits = 0;
            $miss = 0;
            foreach ( $function['branches'] as $bnr => $branch )
            {
               if ( $branch['hit'] === HIT_OK )
                  $hits++;
               else
                  $miss++;
            }
            $output .= sprintf(COVERAGE_PERCENTS_BRANCHES,
                       $hits,
                       $hits + $miss,
                       round($hits/($hits + $miss) * 100,2));

            // paths
            $hits = 0;
            $miss = 0;
            foreach( $function['paths'] as $path )
            {
               if ( $path['hit'] === HIT_OK )
                  $hits++;
               else
                  $miss++;
            }
            $output .= sprintf(COVERAGE_PERCENTS_PATHS,
                       $hits,
                       $hits + $miss,
                       round($hits/($hits + $miss) * 100,2));

            $output .= "\n";
         }
      }

      if ( empty($output) ) $output = 'EMPTY: No coverage match the requirments. ';

      return substr($output,0,-1);
   }

   static
   function help(): string
   {
      return sprintf(XDBGCOV_FORMAT_WITH_NO_PARAMETER,DataFormater::class2format(__CLASS__));
   }
}
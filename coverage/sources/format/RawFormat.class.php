<?php

const COVERAGE_RAW_SKIP_MAIN = true;

class RawFormat extends AbstractFormat
{
   protected $options = array();

   function __construct( array $params=null )
   {
      // Acces to the configutation singleton
      $config = XDebugConfiguration::instance();
      $config->renaming->rename = '%s.xraw';
   }

   function render ( array $datas ): string
   {
      $output = '';

      /* Scripts ---
         $sname: The full path of the script
         $info: The xdebug script converage informations, lines & functions
      */
      ksort($datas);
      foreach ( $datas as $sname => $info )
      {
         if ( empty( $info['functions'] ) ) continue;

         $output .= "file: $sname\n";

         /* Lines ---
            $line: The line number
            $hit: The line execution code
         */
         $s_lines_hits = array();
         $s_lines_sums = array();
         $main    = $this->mainLine($info['lines']);
         foreach ( $info['lines'] as $line => $hit )
         {
            if ( COVERAGE_RAW_SKIP_MAIN && $line === $main ) continue;
            if ( $hit ===  LINE_UNEXECUTABLE ) continue;
            if ( $hit < 0 ) $hit = 0;
            $s_lines_hits[$line] = $hit;
            $s_lines_sums[$line] = 0;
         }

         /* Functions ---
            $fname: The function/method name
            $function: The function branches & paths
         */
         $this->sortLinesFunctions($info['functions']);
         foreach ( $info['functions'] as $fname => $function )
         {
            if ( COVERAGE_RAW_SKIP_MAIN && $this->isMain($fname) ) continue;

            $output .= "function: $fname\n";
            /* Branches ---
               $bnr: The branche number
               $branch: The branche details
                  op_start    : The starting opcode. This is the same number as the array index.
                  op_end      : The last opcode in the branch
                  line_start  : The line number of the op_start opcode.
                  line_end    : The line number of the op_end opcode. This can potentially be a
                              number that is lower than line_start due to the way the PHP compiler
                              generates opcodes.
                  hit         : Whether the opcodes in this branch have been executed or not.
                  out         : An array containing the op_start opcodes for branches that can
                              follow this one. (2147483645 = END)
                  out_hit     : Each element matches the same index as in out and indicates whether
                              this branch exit has been reached.
            */
            $branches = array();
            foreach ( $function['branches'] as $bnr => $branch )
            {
               for ( $i=$branch['line_start'] ; $i<=$branch['line_end'] ; $i++ )
               {
                  if ( !isset($s_lines_sums[$i]) ) continue;
                  $branches[$i] = $branch['hit'];
                  $s_lines_sums[$i] += $branch['hit'];
               }
            }
            array_walk($branches,function(&$v,$k){ $v = "branche: $k, hit: $v"; });
            $output .= implode("\n",$branches);
            $output .= "\n";

            /* Paths ---
               $path: The path detail
                  path           : An array containing the op_start opcodes indicating the branches
                                 that make up this path. In the example, 9 features twice because
                                 this path (the loop) has after branch 9 an exit to opcode 5 (the
                                 start of the loop), and opcode 12 (the next branch after the loop).
                  hit            : Whether this specific path has been followed.
            */
            foreach( $function['paths'] as $path )
            {
            }
         }
         $output .= "Lines:\n";
         array_walk($s_lines_sums,function(&$v,$k){ $v = "line: $k, hits: $v"; });
         $output .= implode("\n",$s_lines_sums);

         $output .= "\n";
      }

      return trim($output);
   }

   static
   function help(): string { return ''; }
}
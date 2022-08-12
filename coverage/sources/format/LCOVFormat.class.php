<?php

const COVERAGE_LCOV_SKIP_MAIN = true;

class LCOVFormat extends AbstractFormat
{
   protected $options = array();

   function __construct( array $params=null )
   {
      $config = XDebugConfiguration::instance();
      $config->renaming->rename = '%s_lcov.info';

      if (!isset($params['name'])) $params['name'] = '';

      $this->options['name'] = $params['name'];
   }

   function render ( array $datas ): string
   {
      $output = sprintf("TN:%s\n",$this->options['name']);

      ksort($datas);
      foreach ( $datas as $script => $info )
      {
         if ( empty( $info['functions'] ) ) continue;

         $output .= sprintf("SF:%s\n",$script);

         // Lines
         $s_lines_hits  = array();
         $s_lines_sums  = array();
         $main          = $this->mainLine($info['lines']);
         foreach( $info['lines'] as $line => $hit )
         {
            if ( COVERAGE_LCOV_SKIP_MAIN && $line === $main ) continue;
            if ( $hit === LINE_UNEXECUTABLE ) continue;
            if ( $hit < 0 ) $hit = 0;
            $s_lines_hits[$line] = $hit;
            $s_lines_sums[$line] = 0;
         }

         $functions = array();
         $func_hits = array();
         $branches  = array();

         // functions
         $fmin = PHP_INT_MAX;
         $fmax = PHP_INT_MIN;
         $this->sortLinesFunctions($info['functions']);
         foreach ( $info['functions'] as $fname => $function )
         {
            // ???
            if ( COVERAGE_LCOV_SKIP_MAIN && $this->isMain($fname) ) continue;
            // ???

            $functions[] = sprintf("FN:%u,%s",$function['branches'][0]['line_start'],$fname);
            // $func_hits[] = sprintf("FNDA:%u,%s",$function['branches'][0]['hit'],$fname);
            if ( isset($func_hits[$fname]) )
               $func_hits[$fname] += $function['branches'][0]['hit'];
            else
               $func_hits[$fname] = $function['branches'][0]['hit'];

            $id = $function['branches'][array_keys($function['branches'])[0]]['line_start'];
            foreach ( $function['branches'] as $bnr => $branch )
            {
               for ( $i=$branch['line_start'] ; $i<=$branch['line_end'] ; $i++ )
               {
                  if ( $i < $fmin ) $fmin = $i;
                  if ( $i > $fmax ) $fmax = $i;
                  $branches[] = sprintf("BRDA:%u,%s,%u,%s",$i,$id,$bnr,$branch['hit']?'1':'-');
                  if ( !isset($s_lines_sums[$i]) ) continue;
                  $s_lines_sums[$i] += $branch['hit'];
               }
            }
         }
         $output .= implode("\n",$functions)."\n";
         //$output .= implode("\n",$func_hits)."\n";
         foreach ( $func_hits as $fname => $calls ) sprintf("FNDA:%u,%s\n",$calls,$fname);

         $output .= sprintf("FNF:%u\n",count($functions));
         // $output .= sprintf("FNH:%u\n",count(array_filter($func_hits,function($v){return 'FNDA:0' !== substr($v,0,6);})));
         $output .= sprintf("FNH:%u\n",count(array_filter($func_hits,function($v){return $v > 0;})));

         $output .= implode("\n",$branches)."\n";

         $output .= sprintf("BRF:%u\n",count($branches));
         $output .= sprintf("BRH:%u\n",count(array_filter($branches,function($v){return '-' !== substr($v,-1);})));

         // $output .= implode("\n",$s_lines_hits)."\n";
         $hits  = 0;
         $count = 0;
         foreach ( $s_lines_sums as $line => $hcount )
         {
            if ( $line < $fmin || $line > $fmax ) continue; // only lines covered by a branche
            $output .= sprintf("DA:%u,%u\n",$line,$hcount);
            $hits   += $hcount > 0;
            $count++;
         }

         // $output .= sprintf("LH:%u\n",count(array_filter($s_lines_sums,function($v){return $v > 0;})));
         $output .= sprintf("LH:%u\n",$hits);
         $output .= sprintf("LF:%u\n",$count);

         $output .= "end_of_record\n";
      }

      return substr($output,0,-1);
   }

   static
   function help(): string
   {
      return sprintf(XDBGCOV_FORMAT_PARMAMETER_HEAD,DataFormater::class2format(__CLASS__),"[?][name=]")
           . sprintf(XDBGCOV_FORMAT_PARMAMETER_PARM,"name: (string) A testname to store in the lcov.")
           . XDBGCOV_FORMAT_PARMAMETER_FOOT;
   }
}
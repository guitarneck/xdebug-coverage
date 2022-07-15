<?php

const COVERAGE_LCOV_SKIP_MAIN = true;

class LCOVFormat extends AbstractFormat
{
   protected $options = array();

   function __construct( array $params=null )
   {
      $config = XDebugConfiguration::instance();
      $config->renaming->rename = 'lcov_%s.info';

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

         $functions = array();
         $func_hits = array();
         $branches  = array();

         // functions
         $this->sortLinesFunctions($info['functions']);
         foreach ( $info['functions'] as $fname => $function )
         {
            // ???
            if ( COVERAGE_LCOV_SKIP_MAIN && $this->isMain($fname) ) continue;
            // ???

            $functions[] = sprintf("FN:%u,%s",$function['branches'][0]['line_start'],$fname);
            $func_hits[] = sprintf("FNDA:%u,%s",$function['branches'][0]['hit'],$fname);

            $id = $function['branches'][array_keys($function['branches'])[0]]['line_start'];
            foreach ( $function['branches'] as $bnr => $branch )
            {
               $branches[] = sprintf("BRDA:%u,%s,%u,%s",$branch['line_start'],$id,$bnr,$branch['hit']?'1':'-');
            }
         }
         $output .= implode("\n",$functions)."\n";
         $output .= implode("\n",$func_hits)."\n";

         $output .= sprintf("FNF:%u\n",count($functions));
         $output .= sprintf("FNH:%u\n",count(array_filter($func_hits,function($v){return 'FNDA:0' !== substr($v,0,6);})));

         $output .= implode("\n",$branches)."\n";

         $output .= sprintf("BRF:%u\n",count($branches));
         $output .= sprintf("BRH:%u\n",count(array_filter($branches,function($v){return '-' !== substr($v,-1);})));

         // Lines
         $lines   = array();
         $main    = $this->mainLine($info['lines']);
         foreach( $info['lines'] as $line => $hit )
         {
            if ( COVERAGE_LCOV_SKIP_MAIN && $line === $main ) continue;
            if ( $hit === LINE_UNEXECUTABLE ) continue;
            $lines[] = sprintf('DA:%u,%u',$line,$hit?1:0);
         }

         $output .= implode("\n",$lines)."\n";

         $output .= sprintf("LF:%u\n",count($lines));
         $output .= sprintf("LH:%u\n",count(array_filter($lines,function($v){return '0' !== substr($v,-1);})));

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
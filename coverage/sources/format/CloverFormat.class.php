<?php
const COVERAGE_CLOVER_SKIP_MAIN = true;

class CloverFormat extends AbstractFormat
{
   protected $options = array();

   private $xml;

   function __construct( array $params=null )
   {
      // Acces to the configutation singleton
      $config = XDebugConfiguration::instance();
      $config->renaming->rename = 'clover_%s.xml';

      // Setting default parameter value
      if (!isset($params['name'])) $params['name'] = 'All files';

      // Storing the user (or default) parameter
      $this->options['name'] = $params['name'];
   }

   function render ( array $datas ): string
   {
      $this->xml = new DOMDocument( "1.0", "UTF-8" );

      $time = time();

      $coverage = $this->xml->createElement( "coverage" );
      $coverage->setAttribute('generated', $time);
      $coverage->setAttribute('clover', '3.2.0');
      $this->xml->appendChild($coverage);

      $project = $this->xml->createElement( "project" );
      $project->setAttribute('timestamp', $time);
      $project->setAttribute('name', $this->options['name']);
      $coverage->appendChild($project);

      $pmetrics = array(
         '__root'             => $project,
         'methods'            => 0,
         'coveredmethods'     => 0,
         'statements'         => 0,
         'coveredstatements'  => 0,
         'conditionals'       => 0,
         'coveredconditionals'=> 0,
         'complexity'         => 0,
         'packages'           => 1,
         'files'              => 0
      );

      /* Scripts ---
         $sname: The full path of the script
         $info: The xdebug script converage informations, lines & functions
      */
      ksort($datas);
      foreach ( $datas as $sname => $info )
      {
         if ( empty( $info['functions'] ) ) continue;

         $file = $this->xml->createElement('file');
         $file->setAttribute('name', basename($sname));
         $file->setAttribute('path', $sname);

         $pmetrics['files']++;
         $smetrics = array(
            '__root'             => $file,
            'methods'            => 0,
            'coveredmethods'     => 0,
            'statements'         => 0,
            'coveredstatements'  => 0,
            'elements'           => 0,
            'coveredelements'    => 0,
            'classes'            => null,
            'conditionals'       => null,
            'coveredconditionals'=> null,
            'loc'                => null,
            'ncloc'              => null
         );

         $classes = array();

         /* Functions ---
            $fname: The function/method name
            $function: The function branches & paths
         */
         $this->sortLinesFunctions($info['functions']);
         foreach ( $info['functions'] as $fname => $function )
         {
            if ( COVERAGE_CLOVER_SKIP_MAIN && $this->isMain($fname) ) continue;

            $fmetrics = array(
               '__root'             => $file,
               'methods'            => 0,
               'coveredmethods'     => 0,
               'statements'         => 0,
               'coveredstatements'  => 0,
               'elements'           => 0,
               'coveredelements'    => 0,
               'classes'            => null,
               'conditionals'       => null,
               'coveredconditionals'=> null,
               'loc'                => null,
               'ncloc'              => null
            );

            $name = null;
            if ( ($pos = strpos($fname,'->')) !== false )
            {
               $type = 'method';
               $name = substr($fname,$pos + 2);
               $class= substr($fname,0,$pos);
               if ( !isset($classes[$class]) )
               {
                  $classes[$class] = array(
                     'methods'               => 0,
                     'coveredmethods'        => 0,
                     'conditionals'          => null,
                     'coveredconditionals'   => null,
                     'statements'            => 0,
                     'coveredstatements'     => 0,
                     'elements'              => 0,
                     'coveredelements'       => 0
                  );
               }
            } else {
               $type = 'function';
               $name = $fname;
               $class= null;
            }

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
            $lines = array();
            foreach ( $function['branches'] as $bnr => $branch )
            {
               for ( $l=$branch['line_start'] ; $l<=$branch['line_end'] ; $l++ )
               {
                  if ( ! isset($info['lines'][$l]) ) continue;
                  if ( $info['lines'][$l] === LINE_UNEXECUTABLE ) continue;

                  if ( ! isset($lines[$l]) )
                  {
                     $lines[$l] = array('hit'=>0,'type'=>null,'name'=>null,'classname'=>null);
                  }

                  //$hit = $info['lines'][$l] === LINE_WAS_EXECUTED ? 1 : 0;
                  $lines[$l]['hit'] += $branch['hit'];

                  if ( $bnr === 0 ){
                     $lines[$l]['name'] = $name;
                     if ( $name === '__construct' )
                        $lines[$l]['classname'] = $class;
                  } else {
                     $type = 'stmt';
                  }

                  if ( $lines[$l]['type'] === null ) $lines[$l]['type'] = $type;
                  $name = null;
                  $type = 'stmt';
               }
            }

            foreach ( $lines as $num => $values )
            {
               $line = $this->xml->createElement('line');
               $line->setAttribute('num',$num);
               $line->setAttribute('count',$values['hit']);
               $line->setAttribute('type',$values['type']);
               if ( $values['name'] !== null )
                  $line->setAttribute('name',$values['name']);
               if ( $values['classname'] !== null )
                  $line->setAttribute('classname',$values['classname']);

               $fmetrics['elements']++;
               if ( $values['hit'] > 0 )
                  $fmetrics['coveredelements']++;

               if ( $values['type'] === 'stmt' )
               {
                  $fmetrics['statements']++;
                  if ( $values['hit'] > 0 )
                     $fmetrics['coveredstatements']++;
               }

               if ( $values['type'] === 'method' )
               {
                  $fmetrics['methods']++;
                  if ( $values['hit'] > 0 )
                     $fmetrics['coveredmethods']++;
               }

               $file->appendChild($line);
            }

            $this->addMetrics($fmetrics,$smetrics);

            if ( $class !== null )
               $this->addMetrics($fmetrics,$classes[$class]);
         }

         $firstChild = $file->firstChild;
         foreach ( $classes as $name => $metrics )
         {
            $classnode = $this->xml->createElement('class');
            $classnode->setAttribute('name',$name);

            $metrics['__root'] = $classnode;
            $this->createElementMetrics($metrics);
            $file->insertBefore($classnode,$firstChild);
         }

         $this->createElementMetrics($smetrics);
         $this->addMetrics($smetrics,$pmetrics);
         $project->appendChild($file);
      }

      $this->createElementMetrics($pmetrics);

      $this->xml->formatOutput = true;
      return substr($this->xml->saveXML(),0,-1);
   }

   static
   function help(): string
   {
      return sprintf(XDBGCOV_FORMAT_PARMAMETER_HEAD,DataFormater::class2format(__CLASS__),"[?][name=]")
           . sprintf(XDBGCOV_FORMAT_PARMAMETER_PARM,"name: (string) A name to store in the clover.")
           . XDBGCOV_FORMAT_PARMAMETER_FOOT;
   }

   private
   function addMetrics ( array $from, array & $into ): void
   {
      foreach ( $from as $k => $v )
      {
         if ( $k === '__root' ) continue;
         if ( $v == null ) continue;
         if ( ! isset($into[$k]) || $into[$k] === null ) continue;
         $into[$k] += $v;
      }
   }

   private
   function createElementMetrics ( array $parms ): void
   {
      $metrics = $this->xml->createElement( "metrics" );
      $root    = $parms['__root'];
      unset($parms['__root']);
      foreach ( $parms as $k => $value )
      {
         if ( $value === null ) continue;
         $metrics->setAttribute($k,$value);
      }
      //$root->appendChild($metrics);
      $root->insertBefore($metrics,$root->firstChild);
   }
}
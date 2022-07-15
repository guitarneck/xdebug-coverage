<?php
/*
   This is a template class with global access to the xdebug coverage datas.
*/
class TemplateFormat extends AbstractFormat
{
   protected $options = array();

   function __construct( array $params=null )
   {
      // Acces to the configutation singleton
      $config = XDebugConfiguration::instance();
      $config->renaming->rename = '%s.extension';

      // Setting default parameter value
      if (!isset($params['parameter'])) $params['parameter'] = 'default';

      // Storing the user (or default) parameter
      $this->options['parameter'] = $params['parameter'];
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

         /* Lines ---
            $line: The line number
            $hit: The line execution code
         */
         foreach( $info['lines'] as $line => $hit )
         {
         }

         /* Functions ---
            $fname: The function/method name
            $function: The function branches & paths
         */
         ksort( $info['functions'] );
         foreach ( $info['functions'] as $fname => $function )
         {
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
            foreach ( $function['branches'] as $bnr => $branch )
            {
            }

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
      }

      return $output;
   }

   static
   function help(): string { return ''; }
}
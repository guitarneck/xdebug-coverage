<?php

const LINE_WAS_EXECUTED =  1; // this line was executed
const LINE_NOT_EXECUTED = -1; // this line was not executed
const LINE_UNEXECUTABLE = -2; // this line did not have executable code on it

const HIT_NO = 0;
const HIT_OK = 1;

const BRANCHE_OPCODE_EXIT = 2147483645;

abstract
class AbstractFormat
{
   abstract
   function __construct(array $params = null);

   /**
    * Retrieve formatted test name file and datas.
    *
    * @param string $name     The raw name of the test file.
    * @param array $datas     The xdebug data.
    * @return array           [name, datas]
    */
   abstract
   function render ( array $datas ): string;

   abstract static
   function help(): string;

   /**
    * Retrive the main line number from $file['lines']
    *
    * @param array $lines[]   The lines hits.
    * @return int             The line number of '{main}'.
    */
   protected
   function mainLine ( array $lines )
   {
      $lnbrs = array_keys($lines);
      return array_pop($lnbrs);
   }

   /**
    * Tell the function name is the main of the script.
    *
    * @param string $fname    The function name.
    * @return boolean         True when the function is the main, false otherwise.
    */
   protected
   function isMain ( string $fname )
   {
      return $fname === '{main}';
   }

   // ascending sorting of integers
   private
   function cmpasc ($a,$b)
   {
      return ($a > $b) - ($a < $b);
   }

   /**
    * Sort the branches of the functions by line number.
    *
    * @param arrays &$functions[]   The function informations to sort.
    * @return void
    */
   protected
   function sortLinesFunctions ( & $functions )
   {
      uasort( $functions, function ($a,$b){
         return $this->cmpasc($a['branches'][0]['line_start'], $b['branches'][0]['line_start']);
      });
   }
}
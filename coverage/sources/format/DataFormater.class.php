<?php

include_once   'AbstractFormat.class.php';

const XDBGCOV_FORMATER_TYPE_NOT_FOUND  = "\n\033[31m[ERROR]\033[0m \033[1;30mformater not found:\033[0m \033[31m%s\033[0m\n";
const XDBGCOV_FORMATER_CLASS_NOT_FOUND = "\n\033[31m[ERROR]\033[0m \033[1;30mformater class not found.\033[0m\n";

const XDBGCOV_FORMAT_WITH_NO_PARAMETER = "\n   \033[1m%s\033[0m format required no parameter.\n";
const XDBGCOV_FORMAT_PARMAMETER_HEAD   = "\n   \033[4mFormat parameters\033[0m\n\n   \033[1m%s\033[0m\033[37m%s\033[0m";
const XDBGCOV_FORMAT_PARMAMETER_PARM   = "\n      - %s";
const XDBGCOV_FORMAT_PARMAMETER_FOOT   = "\n";


class DataFormater
{
   protected   $format,
               $params;

   function __construct ( string $format )
   {
      list($this->format,$this->params) = self::parse_str($format);
   }

   static
   function factory ( string $format, stdClass $formats ): AbstractFormat
   {
      $formater = new DataFormater($format);

      if ( !$formater->inFormats($formats) )
         die(sprintf(XDBGCOV_FORMATER_TYPE_NOT_FOUND,$format));

      $class   = $formater->formaterClass($formats);
      $script  = $formater->formaterScript($class);
      if ( !file_exists($script) )
         die(XDBGCOV_FORMATER_CLASS_NOT_FOUND);

      include $script;
      return new $class($formater->params);
   }

   static
   function parse_str ( string $format ): array
   {
      $params = null;

      if ( ($pos = mb_strpos($format,'?')) !== false )
      {
         parse_str(mb_substr($format,$pos+1), $params);
         $format = mb_substr($format,0,$pos);
      }
      return array($format,$params);
   }

   function isValid ( stdClass $formats ): bool
   {
      if ( !$this->inFormats($formats) ) return false;

      $class   = $this->formaterClass($formats);
      $script  = $this->formaterScript($class);
      return file_exists($script);
   }

   function inFormats ( stdClass $formats ): bool
   {
      return isset($formats->{$this->format});
   }

   function formaterClass ( stdClass $formats ): string
   {
      return sprintf('%sFormat',$formats->{$this->format});
   }

   function formaterScript ( string $className ): string
   {
      // return  __DIR__ . DIRECTORY_SEPARATOR . "{$className}.class.php";
      return  sprintf('%s%s%s.class.php',__DIR__,DIRECTORY_SEPARATOR,$className);
   }

   static
   function class2format ( string $classname ): string
   {
      return strtolower(str_replace('Format','',$classname));
   }
}
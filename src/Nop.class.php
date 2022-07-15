<?php
// A nothing class



class Nop
{
   function __construct()
   {

   }
}

class NopTwo
{
   function __construct()
   {
      user_error("This class do nothing and it's what's it is done for.",E_USER_NOTICE);
   }
}

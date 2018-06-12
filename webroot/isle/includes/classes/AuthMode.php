<?php

    namespace ISLE;

    class AuthMode	// Should use extends SplEnum
    {
      const USE_DN			= 0;
      const USE_NAME			= 1;
      const USE_NAME_AT_DOMAIN		= 2;
      const USE_DOMAIN_SLASH_NAME	= 3;
    }
?>

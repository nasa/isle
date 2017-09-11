<?php
    //require_once 'includes/config.php';
    require_once 'includes/secrets.php';

    class ActiveDirectory
    {
      // Active Directory userAccountControl flags:
      const SCRIPT				=     0x0001;
      const ACCOUNTDISABLE			=     0x0002;
      const HOMEDIR_REQUIRED			=     0x0008;
      const LOCKOUT				=     0x0010;
      const PASSWD_NOTREQD			=     0x0020;
      const PASSWD_CANT_CHANGE			=     0x0040;
      const ENCRYPTED_TEXT_PWD_ALLOWED		=     0x0080;
      const TEMP_DUPLICATE_ACCOUNT		=     0x0100;
      const NORMAL_ACCOUNT			=     0x0200;
      const INTERDOMAIN_TRUST_ACCOUNT		=     0x0800;
      const WORKSTATION_TRUST_ACCOUNT		=     0x1000;
      const SERVER_TRUST_ACCOUNT		=     0x2000;
      const DONT_EXPIRE_PASSWORD		=    0x10000;
      const MNS_LOGON_ACCOUNT			=    0x20000;
      const SMARTCARD_REQUIRED			=    0x40000;
      const TRUSTED_FOR_DELEGATION		=    0x80000;
      const NOT_DELEGATED			=   0x100000;
      const USE_DES_KEY_ONLY			=   0x200000;
      const DONT_REQ_PREAUTH			=   0x400000;
      const PASSWORD_EXPIRED			=   0x800000;
      const TRUSTED_TO_AUTH_FOR_DELEGATION	=  0x1000000;
      const PARTIAL_SECRETS_ACCOUNT		= 0x04000000;

      public static $UseraccountcontrolFlags = array(
        self::SCRIPT                            => "SCRIPT",
        self::ACCOUNTDISABLE  	        	=> "ACCOUNTDISABLE",
        self::HOMEDIR_REQUIRED	        	=> "HOMEDIR_REQUIRED",
        self::LOCKOUT                           => "LOCKOUT",
        self::PASSWD_NOTREQD  	        	=> "PASSWD_NOTREQD",
        self::PASSWD_CANT_CHANGE		=> "PASSWD_CANT_CHANGE",
        self::ENCRYPTED_TEXT_PWD_ALLOWED	=> "ENCRYPTED_TEXT_PWD_ALLOWED",
        self::TEMP_DUPLICATE_ACCOUNT		=> "TEMP_DUPLICATE_ACCOUNT",
        self::NORMAL_ACCOUNT	        	=> "NORMAL_ACCOUNT",
        self::INTERDOMAIN_TRUST_ACCOUNT 	=> "INTERDOMAIN_TRUST_ACCOUNT",
        self::WORKSTATION_TRUST_ACCOUNT 	=> "WORKSTATION_TRUST_ACCOUNT",
        self::SERVER_TRUST_ACCOUNT		=> "SERVER_TRUST_ACCOUNT",
        self::DONT_EXPIRE_PASSWORD		=> "DONT_EXPIRE_PASSWORD",
        self::MNS_LOGON_ACCOUNT	        	=> "MNS_LOGON_ACCOUNT",
        self::SMARTCARD_REQUIRED		=> "SMARTCARD_REQUIRED",
        self::TRUSTED_FOR_DELEGATION		=> "TRUSTED_FOR_DELEGATION",
        self::NOT_DELEGATED	        	=> "NOT_DELEGATED",
        self::USE_DES_KEY_ONLY	        	=> "USE_DES_KEY_ONLY",
        self::DONT_REQ_PREAUTH	        	=> "DONT_REQ_PREAUTH",
        self::PASSWORD_EXPIRED		        => "PASSWORD_EXPIRED",
        self::TRUSTED_TO_AUTH_FOR_DELEGATION	=> "TRUSTED_TO_AUTH_FOR_DELEGATION",
        self::PARTIAL_SECRETS_ACCOUNT		=> "PARTIAL_SECRETS_ACCOUNT",
      );

      // SAMAccountType values:
      const SAM_DOMAIN_OBJECT			=        0x0;
      const SAM_GROUP_OBJECT			= 0x10000000;
      const SAM_NON_SECURITY_GROUP_OBJECT	= 0x10000001;
      const SAM_ALIAS_OBJECT			= 0x20000000;
      const SAM_NON_SECURITY_ALIAS_OBJECT	= 0x20000001;
      const SAM_USER_OBJECT			= 0x30000000;
      const SAM_NORMAL_USER_ACCOUNT		= 0x30000000;	// 805306368
      const SAM_MACHINE_ACCOUNT			= 0x30000001;
      const SAM_TRUST_ACCOUNT			= 0x30000002;
      const SAM_APP_BASIC_GROUP			= 0x40000000;
      const SAM_APP_QUERY_GROUP			= 0x40000001;
      const SAM_ACCOUNT_TYPE_MAX		= 0x7fffffff;

      public static $SamaccounttypeValues = array(
        self::SAM_DOMAIN_OBJECT	        	=> "SAM_DOMAIN_OBJECT",
        self::SAM_GROUP_OBJECT	        	=> "SAM_GROUP_OBJECT",
        self::SAM_NON_SECURITY_GROUP_OBJECT	=> "SAM_NON_SECURITY_GROUP_OBJECT",
        self::SAM_ALIAS_OBJECT	        	=> "SAM_ALIAS_OBJECT",
        self::SAM_NON_SECURITY_ALIAS_OBJECT	=> "SAM_NON_SECURITY_ALIAS_OBJECT",
        self::SAM_USER_OBJECT	        	=> "SAM_USER_OBJECT",
        self::SAM_NORMAL_USER_ACCOUNT		=> "SAM_NORMAL_USER_ACCOUNT",
        self::SAM_MACHINE_ACCOUNT		=> "SAM_MACHINE_ACCOUNT",
        self::SAM_TRUST_ACCOUNT	        	=> "SAM_TRUST_ACCOUNT",
        self::SAM_APP_BASIC_GROUP		=> "SAM_APP_BASIC_GROUP",
        self::SAM_APP_QUERY_GROUP		=> "SAM_APP_QUERY_GROUP",
        self::SAM_ACCOUNT_TYPE_MAX		=> "SAM_ACCOUNT_TYPE_MAX",
      );

      // RoleOID:
      const LDAP_MATCHING_RULE_BIT_AND          = "1.2.840.113556.1.4.803";
      const LDAP_MATCHING_RULE_BIT_OR           = "1.2.840.113556.1.4.804";

      // Active Directory instanceType flags:
      const INST_HEAD_OF_NAMING_CNTXT           = 0x00000001;
      const INST_REPLICA_NOT_INSTANTIATED       = 0x00000002;
      const INST_OBJ_WRITABLE_ON_DIR            = 0x00000004;
      const INST_NAMING_CNTXT_ABOVE_DIR_HELD    = 0x00000008;
      const INST_NAMING_CNTXT_CONSTRCTD_W_REPL  = 0x00000010;
      const INST_NAMING_CNTXT_REMOVED_FROM_DSA  = 0x00000020;

      public static $InstancetypeValues = array(
        self::INST_HEAD_OF_NAMING_CNTXT         => "INST_HEAD_OF_NAMING_CNTXT",
        self::INST_REPLICA_NOT_INSTANTIATED     => "INST_REPLICA_NOT_INSTANTIATED",
        self::INST_OBJ_WRITABLE_ON_DIR          => "INST_OBJ_WRITABLE_ON_DIR",
        self::INST_NAMING_CNTXT_ABOVE_DIR_HELD  => "INST_NAMING_CNTXT_ABOVE_DIR_HELD",
        self::INST_NAMING_CNTXT_CONSTRCTD_W_REPL=> "INST_NAMING_CNTXT_CONSTRCTD_W_REPL",
        self::INST_NAMING_CNTXT_REMOVED_FROM_DSA=> "INST_NAMING_CNTXT_REMOVED_FROM_DSA",
      );

      // Stores connection to server:
      private $connection;


      // Constructor:
      function __construct() 
      { 
        // Establish connection to server:
        $this->connection = ldap_connect(Secrets::LDAP_HOST, Secrets::LDAP_PORT)
                            or die("Could not connect to " . Secrets::LDAP_HOST);
        if ($this->connection) {
          ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3)
                          or die('Unable to set LDAP opt protocol version');
          ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0)
                          or die('Unable to set LDAP opt referrals');

          // binding to ldap server
          if (!ldap_bind($this->connection, Secrets::LDAP_USER, Secrets::LDAP_PASSWORD)) {
            ldap_close($this->connection);	// "logout"
            $this->connection = NULL;
            die("Unable to bind to LDAP server " . Secrets::LDAP_HOST);
          }
        }
      }


      // Destructor:
      function __destruct() 
      { 
        ldap_close($this->connection);		// "logout"
        $this->connection = NULL;
      }


      function search($query, $attrs)
      {
        $total_result = array();
        $total_count = 0;
        $cookie = '';
        do {
          if (!ldap_control_paged_result($this->connection, 1000, true, $cookie)) {
            die("ldap_control_paged_result failed!");
          }

          $result = ldap_search($this->connection, Secrets::LDAP_DN, $query, $attrs);
          echo "<p>Error in search query: " . ldap_error($this->connection) . "</p>";
          if ($result) {
            $total_result = array_merge($total_result,
                                        ldap_get_entries($this->connection, $result));
            $total_count += $total_result['count'];	// 'count' gets overwritten.
            //echo "<p>Sub-result:</p>";
            //var_dump($total_result);
          }

          if (!ldap_control_paged_result_response($this->connection, $result, $cookie)) {
            die("ldap_control_paged_result_response failed!");
          }
        } while ($cookie != null && $cookie != '');

        $total_result['count'] = $total_count;
        //echo "<p>Total Result:</p>";
        //var_dump($total_result);
        return $total_result;
      }


      public static function allowed_account($dn)
      {
        if (Secrets::ALLOWED_DNS == NULL or count(Secrets::ALLOWED_DNS) == 0) {
          return true;
        }

        foreach (Secrets::ALLOWED_DNS as $pattern) {
          // MUST USE !== OPERATOR, NOT !=
          if (strpos($dn, $pattern) !== false) {
            return true;
          }
        }
        return false;
      }


      public static function print_UAC_flags($mask)
      {
        // If $mask is a string then convert it to an integer:
        if (is_string($mask)) {
          $mask = intval($mask);
        } else if (!is_int($mask)) {    // Else $mask is something weird:
          die('ERROR: ActiveDirectory::print_UAC_flags: $mask is type '
              . gettype($mask));
        }

        //echo "<br>ActiveDirectory::print_UAC_flags: STARTING<br>";
        $result = "";
        // Iterate over every bitmask:
        foreach (self::$UseraccountcontrolFlags as $key => $value) {
          //echo "<br>ActiveDirectory::print_UAC_flags: key={$key}, value={$value}<br>";
          if ($mask & $key) {
            $result .= $value . ",";
          }
        }
        //echo "<br>ActiveDirectory::print_UAC_flags: EXITING, result={$result}<br>";
        return $result;
      }


      public static function SID_to_string($value)
      {
        // revision - 8bit unsigned int (C1)
        // count - 8bit unsigned int (C1)
        // 2 null bytes
        // ID - 32bit unsigned long, big-endian order
        $sid = @unpack('C1rev/C1count/x2/N1id', $value);
        $subAuthorities = [];
    
        if (!isset($sid['id']) || !isset($sid['rev'])) {
          throw new \UnexpectedValueException(
                      'The revision level or identifier authority was not ' .
                      'found when decoding the SID.'
                    );
        }

        $revisionLevel = $sid['rev'];
        $identifierAuthority = $sid['id'];
        $subs = isset($sid['count']) ? $sid['count'] : 0;
        if ($subs > 15) {
          throw new \UnexpectedValueException('SubAuthorityCount exceeds 15.');
        }
    
        // The sub-authorities depend on the count, so only get as many as
        // the count, regardless of data beyond it:
        for ($i = 0; $i < $subs; $i++) {
          // Each sub-auth is a 32bit unsigned long, little-endian order:
          $subAuthorities[] = unpack('V1sub', hex2bin(substr(bin2hex($value),
                                                             16 + ($i * 8), 8)
                                    )                )['sub'];
        }

        // Tack on the 'S-' and glue it all together...
        return 'S-' . $revisionLevel . '-' .
               $identifierAuthority.implode(preg_filter('/^/', '-', $subAuthorities));
      }


      public static function match_subauth($sid_array, $comp_array)
      {
        $len = count($comp_array);
        if (count($sid_array) < $len + 3) {
          die("SID array is too small!");
        }

        for ($i = 0; $i < count($comp_array); $i++) {
          if ($sid_array[$i + 3] != $comp_array[$i]) {
            return false;
          }
        }

        return true;
      }


      public static function SID_to_userid($sid)
      {
        // If sid is a packed, binary string:
        if (strncmp($sid, "S-", 2) != 0)
        {
            $sid = ActiveDirectory::SID_to_string($sid);
        }

        // sid is now a human-readable string:
        $fields = explode("-", $sid, 18);
        // 0 = 'S-'
        // 1 = revisionLevel
        // 2 = identifierAuthority 
        // 3 ... 17 = subAuthorities

        $num_fields = count($fields);
        if ($num_fields < 4 or $num_fields > 18 or $fields[0] !="S" or
            intval($fields[1]) != Secrets::SID_REV_LVL or 
            intval($fields[2]) != Secrets::SID_ID_AUTH or
            !ActiveDirectory::match_subauth($fields, Secrets::SID_SUBAUTH)) {
          throw new \UnexpectedValueException('Malformed SID.');
        }

        // Which fields vary from person to person?
        $unique_start = 3 + count(Secrets::SID_SUBAUTH);
        $num_unique = $num_fields - $unique_start;
        $uid = 0;
        for ($i = 0; $i < $num_unique; $i++) {
          // Each sub-auth is a 32bit unsigned long:
          $uid += intval($fields[$i + $unique_start]) << (32 * $i);
        }

        return $uid;
      }
    }

?>

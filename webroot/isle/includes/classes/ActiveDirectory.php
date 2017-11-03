<?php
    use ISLE\AuthMode;
    use ISLE\Secrets;

    namespace ISLE;

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
      private $username;	// Use distinguishedName, not sAMAccountName?
      private $password;


      // Constructor:  *** MAY NOT WORK IF SAMACCOUNTNAME PASSED INSTEAD OF DN: ***
      function __construct($username = null, $password = null)
      {
        if ($username == null) {		// PHP is too stupid to allow class
          $username = Secrets::LDAP_USER;	// members as function parameter defaults.
        }
        if ($password == null) {
          $password = Secrets::LDAP_PASSWORD;
        }
        $this->username = $username;
        $this->password = $password;
        //echo "username = " . $username . ", password = " . $password . "<br>";
        // Establish connection to server:
        if (!$this->connection = ldap_connect(Secrets::LDAP_HOST, Secrets::LDAP_PORT)) {
          throw new \UnexpectedValueException('ldap_connect(' . Secrets::LDAP_HOST .
                                              ',' .  Secrets::LDAP_PORT . ') failed');
        }

        if (!ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3)) {
          throw new \UnexpectedValueException('ldap_set_option(' .
                                              'LDAP_OPT_PROTOCOL_VERSION) failed ' .
                                              'with: ' .
                                              ldap_error($this->connection));
        }

        if (!ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0)) {
          throw new \UnexpectedValueException('ldap_set_option(LDAP_OPT_REFERRALS) ' .
                                              'failed with: ' .
                                              ldap_error($this->connection));
        }

        // binding to ldap server
        if (!ldap_bind($this->connection, $this->username, $this->password)) {
          //ldap_close($this->connection);	// "logout"
          throw new \UnexpectedValueException('ldap_bind(...,' . $this->username .
                                              ',' . $this->password .
                                              ') failed with: ' .
                                              ldap_error($this->connection));
        }
      }


      // Destructor:
      function __destruct()
      {
        ldap_close($this->connection);		// "logout"
      }


      public static function authenticate_user($user, $password)
      {
        // NEED TO SANITIZE $user OF WILDCARDS.
        $auth_user = "";

        switch (Secrets::AUTH_MODE) {
          case AuthMode::USE_DN:		// Full DistinguishedName.
            try {
              $ad = new ActiveDirectory();	// Bind with default credentials.
              $query = ActiveDirectory::user_query_string($user);
              $auth_user = $ad->search($query, array("distinguishedname"))[0]["distinguishedname"];
            } catch (Exception $e) {
              echo("authenticate_user failed: " . ldap_error($ad) . ", " .
                   $e->getMessage());
              throw $e;
            }
            break;

          case AuthMode::USE_NAME:
            // Nothing to do.  $user should be correct.
            $auth_user = $user;
            break;

          case AuthMode::USE_NAME_AT_DOMAIN:	// Windows user@domain.
            if (Secrets::WIN_DOMAIN == null or Secrets::WIN_DOMAIN == "") {
              echo("authenticate_user failed: Secrets::WIN_DOMAIN is blank.");
              throw new \UnexpectedValueException('Secrets::WIN_DOMAIN is blank.');
            }
            $auth_user = sprintf("%s@%s", $user, Secrets::WIN_DOMAIN);
            break;

          case AuthMode::USE_DOMAIN_SLASH_NAME:	// Windows domain\user.
            if (Secrets::WIN_DOMAIN == null or Secrets::WIN_DOMAIN == "") {
              echo("authenticate_user failed: Secrets::WIN_DOMAIN is blank.");
              throw new \UnexpectedValueException('Secrets::WIN_DOMAIN is blank.');
            }
            $auth_user = sprintf("%s\\%s", Secrets::WIN_DOMAIN, $user);
            break;

          default:
            echo("authenticate_user failed: Secrets::AUTH_MODE = " .
                 Secrets::AUTH_MODE);
            throw new \UnexpectedValueException('Secrets::AUTH_MODE is illegal value (' .
                                                Secrets::AUTH_MODE . ')');
            break;
        }

        try {
          $ad = new ActiveDirectory($auth_user, $password);
          return $ad->get_userid($user);

        } catch (Exception $e) {
          echo("authenticate_user failed: couldn't get userid, " . $e->getMessage());
          throw $e;
        }
      }


      public static function user_query_string($user, $more_restrictions = "") {
        return "(&(sAMAccountType=" .
                   ActiveDirectory::SAM_NORMAL_USER_ACCOUNT . ")" .
                 "(sAMAccountName=" . $user . ")" .
                 "(userAccountControl:" .
                   ActiveDirectory::LDAP_MATCHING_RULE_BIT_OR .
                   ":=" . ActiveDirectory::NORMAL_ACCOUNT . ")" .
                 "(!(userAccountControl:" .
                     ActiveDirectory::LDAP_MATCHING_RULE_BIT_OR .
                    ":=" .
                     (ActiveDirectory::ACCOUNTDISABLE |
                      ActiveDirectory::PASSWD_NOTREQD |
                      ActiveDirectory::PASSWORD_EXPIRED) . "))" .
                  $more_restrictions . ")";
      }


      // If login fails then throw an exception, else return user ID #.
      public function get_userid($user = NULL, $uid_attr = NULL)
      {
        if ($user == NULL) {
          $user = $this->username;
        }

        if ($uid_attr == NULL) {
          $uid_attr = Secrets::LDAP_UID_ATTR;
        }

        $query = ActiveDirectory::user_query_string($user);
        $result = $this->search($query, Secrets::LDAP_UID_ATTR);
echo("<br/>get_userid() search result = ");
        var_dump($result);

        if (Secrets::USE_SID) {				// Convert Windows SID:
          return ActiveDirectory::SID_to_userid($result[0][Secrets::LDAP_UID_ATTR][0]);
        } else {					// Else assume Posix user ID:
          return intval($result[0][Secrets::LDAP_UID_ATTR][0]);
        }
      }


      public function search($query, $attrs)
      {
        if (is_string($attrs)) {
          $attrs = array($attrs);
        }

        $total_result = array();
        $total_count = 0;
        $cookie = '';
        do {
          if (!ldap_control_paged_result($this->connection, 1000, false, $cookie)) {
            $msg = "ActiveDirectory#search ldap_control_paged_result failed: " .
                   ldap_error($this->connection);
            echo($msg);
            throw new \UnexpectedValueException($msg);
          }

          $result = ldap_search($this->connection, Secrets::LDAP_DN, $query, $attrs);
          if ($result !== false) {			// 0 == false, but 0 !== false.
            $sub_result = ldap_get_entries($this->connection, $result);
            if ($sub_result === false) {		// PHP sucks.
                $msg = "ActiveDirectory#search ldap_get_entries failed: " .
                       ldap_error($this->connection);
                echo($msg);
                throw new \UnexpectedValueException($msg);
            }
            $total_result = array_merge($total_result, $sub_result);
            $total_count += $total_result['count'];	// 'count' gets overwritten.
          } else {
            echo('<br/>$query = ' . $query . '<br/>');
            var_dump($attrs);
            $msg = "ActiveDirectory#search ldap_search failed: " .
                   ldap_error($this->connection);
            echo($msg);
            throw new \UnexpectedValueException($msg);
          }

          // *** GETTING FALSE EVEN THOUGH THERE IS NO ERROR ***
          ldap_control_paged_result_response($this->connection, $result, $cookie);
/*        *** OLD CODE: ***
          if (!ldap_control_paged_result_response($this->connection, $result, $cookie)) {
            $msg = "ldap_control_paged_result_response failed: " .
                   ldap_error($this->connection);
            echo($msg);
            throw new \UnexpectedValueException($msg);
          }
*/
        } while ($cookie !== null && $cookie != '');

        $total_result['count'] = $total_count;
        //echo "<p>Total Result:</p>";
        //var_dump($total_result);
        return $total_result;
      }


      private static function denied_account($dn)
      {
        if (Secrets::DENIED_DNS == NULL or count(Secrets::DENIED_DNS) == 0) {
          return false;			// Do not deny by default.
        }

        foreach (Secrets::DENIED_DNS as $pattern) {
          // MUST USE !== OPERATOR, NOT !=
          if (stripos($dn, $pattern) !== false) {
            return true;		// Pattern was matched.  Deny this user.
          }
        }
        return false;			// No patterns matched.  Don't deny user.
      }


      public static function allowed_account($dn)
      {
        if (Secrets::ALLOWED_DNS == NULL or count(Secrets::ALLOWED_DNS) == 0) {
          return !ActiveDirectory::denied_account($dn);
        }

        foreach (Secrets::ALLOWED_DNS as $pattern) {
          // MUST USE !== OPERATOR, NOT !=
          if (stripos($dn, $pattern) !== false) {	// If user in whitelist:
            return !ActiveDirectory::denied_account($dn);
          }
        }
        return false;			// User not in whitelist.  Deny.
      }


      public static function print_UAC_flags($mask)
      {
        // If $mask is a string then convert it to an integer:
        if (is_string($mask)) {
          $mask = intval($mask);
        } else if (!is_int($mask)) {    // Else $mask is something weird:
          $msg = 'ActiveDirectory::print_UAC_flags: $mask is type ' . gettype($mask);
          echo($msg);
          throw new \UnexpectedValueException($msg);
        }

        $result = "";
        // Iterate over every bitmask:
        foreach (self::$UseraccountcontrolFlags as $key => $value) {
          if ($mask & $key) {
            $result .= $value . ",";
          }
        }

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
    
        if (!isset($sid['id']) or !isset($sid['rev'])) {
          var_dump($sid);
          throw new \UnexpectedValueException(
                      'The revision level or identifier authority was not ' .
                      'found when decoding the SID.'
                    );
        }

        $revisionLevel = $sid['rev'];
        $identifierAuthority = $sid['id'];
        $subs = isset($sid['count']) ? $sid['count'] : 0;
        if ($subs > 15) {
          throw new \OutOfBoundsException('SubAuthorityCount exceeds 15.');
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


      private static function match_subauth($sid_array, $comp_array)
      {
        $len = count($comp_array);
        if (count($sid_array) < $len + 3) {
          throw new \OutOfBoundsException("SID array is too small.");
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
          $sid = ActiveDirectory::SID_to_string($sid);	// Make sid human-readable.
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
          echo("<br/>In SID_to_userid() !!!!!!!!!!!!!!! <br/>");
          var_dump($sid);
          //exit();
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

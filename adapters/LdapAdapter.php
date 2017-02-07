<?php
/**
 * Omeka Side Auth Plugin: LDAP Auth Adapter
 *
 * @license MIT
 */

/**
 * Omeka Side Auth Plugin: LDAP Auth Adapter Class
 *
 * Extends the Zend_Auth_Adapter_Ldap
 * Authenticates LDAP User and adds it to Omeka as researcher (read-only rights).
 *
 * @package Side Auth
 */
class SideAuth_LdapAdapter extends Zend_Auth_Adapter_Ldap
{
    /**
     * Performs an authentication attempt.
     *
     * @return Zend_Auth_Result The result of the authentication.
     */
    public function authenticate()
    {
        // Use the parent method to authenticate the user.
        $result = parent::authenticate();

        // Check if user was authenticated.
        if ($result->isValid()) {
            $user_data = $this->getAccountObject();
            $name = $user_data->cn;
            $username = $user_data->uid;
            $email = $user_data->mail;
            
            // Lookup the user by their username in the user table.
            $user = get_db()->getTable('User')->findBySql(
                'username = ?',
                array($username),
                true
            );
                        
                        
            if ($user) {
                // User found in Omeka User table
                // Update password, as the ldap password might have been updated
                $user->setPassword($this->getPassword());
                $user->save();
                
                
                if($user->active) {
                  // If the user was found and active
                  // return success
                  return new Zend_Auth_Result(
                      Zend_Auth_Result::SUCCESS,
                      $user->id
                  );
                } else {
                  // If the user was found but was inactive
                  // return that the user does not have an active account.
                  return new Zend_Auth_Result(
                      Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND,
                      $username,
                      array(__('User "%s" has an Inactive Account.', $username))
                  );
                }
            }
            
            // If the user was NOT found, create it, return success.
            $user = new User();
            $user_post_data = array(
              'username'  =>  $username,              
              'name'      =>  $name,
              'email'     =>  $email,
              'active'    =>  true);
            $user->setPostData($user_post_data);
            $user->setPassword($this->getPassword());
            $user->role = 'researcher';
            $user->save();          
            return new Zend_Auth_Result(
                Zend_Auth_Result::SUCCESS,
                $user->id
            );                        
        }

        // Otherwise, log messages to error log.
        $messages = $result->getMessages();

        _log(
            'SideAuth_LdapAdapter: '. implode("\n", $messages),
            Zend_Log::ERR
        );

        // Return the parent's result with error message meant for user.
        return new Zend_Auth_Result(
            $result->getCode(),
            $result->getIdentity(),
            array($messages[0])
        );
    }
}

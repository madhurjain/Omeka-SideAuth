<?php
/**
 * Omeka Side Auth Plugin
 *
 * @author Madhur Jain <msj25@njit.edu>
 * @license MIT
 */

if (!defined('SideAuth_PLUGIN_DIR')) {
    define('SideAuth_PLUGIN_DIR', dirname(__FILE__));
}
require_once SideAuth_PLUGIN_DIR . '/adapters/LdapAdapter.php';

/**
 * Omeka Side Auth Plugin: Plugin Class
 *
 * @package Side Auth
 */
class SideAuthPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array Plugin hooks.
     */
    protected $_hooks = array(
        'install',
        'uninstall',
        'config',
        'config_form',
        'admin_head'
    );

    /**
     * @var array Plugin filters.
     */
    protected $_filters = array(
        'login_adapter',
        'public_navigation_admin_bar'
    );

    /**
     * @var array Plugin options.
     */
    protected $_options = array(
        'side_auth_enabled' => true,
        'side_auth_ldap_host' => 'ldap.domain.com',
        'side_auth_ldap_port' => '',
        'side_auth_ldap_bindRequiresDn' => true,
        'side_auth_ldap_baseDn' => 'ou=people,o=domain,c=US',
        'side_auth_ldap_accountFilterFormat' => 'uid=%s'
    );

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Hook to plugin installation.
     *
     * Installs the options for the plugin.
     */
    public function hookInstall()
    {
        $this->_installOptions();
    }

    /**
     * Hook to plugin uninstallation.
     *
     * Uninstalls the options for the plugin.
     */
    public function hookUninstall()
    {
        $this->_uninstallOptions();
    }

    /**
     * Hook to plugin configuration form submission.
     *
     * Sets options submitted by the configuration form.
     */
    public function hookConfig($args)
    {
        foreach (array_keys($this->_options) as $option) {
            if (isset($args['post'][$option])) {
                set_option($option, $args['post'][$option]);
            }
        }
    }

    /**
     * Hook to output plugin configuration form.
     *
     * Include form from config_form.php file.
     */
    public function hookConfigForm()
    {
        include 'config_form.php';
    }
    
    /**
     * Hook to admin header.
     *
     * Prevent's dashboard access to researcher role.
     */
    public function hookAdminHead()
    {
      $user = current_user();
      // If we're logged in, then prevent access to the dashboard for researcher users
      if ($user && $user->role == 'researcher') {
          // $request = Zend_Controller_Front::getInstance()->getRequest();
          $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
          $redirector->gotoUrl(WEB_ROOT);
      }
    }
    
    /**
     * Filter the public navigation bar when logged in.
     *
     * Remove the Admin link for researcher role.
     */
    public function filterPublicNavigationAdminBar($navLinks)
    {
      $user = current_user();
      if ($user && $user->role == 'researcher') {
        unset($navLinks[1]);
      }
      return $navLinks;
    }

    /**
     * Filter the login auth adapter.
     *
     * Attempts to LDAP Authenticate the user.
     * Falls back to Omeka Login if LDAP fails.
     */
    public function filterLoginAdapter($adapter, $args)
    {        
        $form = $args['login_form'];

        // Check if LDAP authenticate is enabled in plugin.
        $ldap = get_option('side_auth_enabled');
        if ($ldap) {
            // Build an array for the LDAP auth adapter from plugin options.
            $options = array();
            $preg = '/^side_auth_ldap_/';

            foreach (array_keys($this->_options) as $option) {
                if (preg_match($preg, $option)) {
                    $key = preg_replace($preg, '', $option);
                    $value = get_option($option);

                    if (!empty($value)) {
                        $options[$key] = $value;
                    }
                }
            }

            // Create new auth adapter with the options, username and password.
            $adapterLdap = new SideAuth_LdapAdapter(
                array('ldap' => $options),
                $form->getValue('username'),
                $form->getValue('password')
            );
            
            // Attempt to authenticate using LDAP.
            $result = $adapterLdap->authenticate();

            // Return the LDAP auth adapter only if user was validated against LDAP.
            if ($result->isValid()) {
                return $adapterLdap;
            }
        }
        
        // Return the database auth adapter after setting username/password.
        return $adapter
            ->setIdentity($form->getValue('username'))
            ->setCredential($form->getValue('password'));
    }
}

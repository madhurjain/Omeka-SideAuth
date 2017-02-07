<?php
/**
 * Omeka Side Auth: Configuration Form
 *
 * Outputs the configuration form for the config_form hook.
 *
 * @author Madhur Jain <msj25@njit.edu>
 * @license MIT
 */

$sections = array(
    'LDAP - Lightweight Directory Access Protocol' => array(
        array(
            'name' => 'side_auth_enabled',
            'label' => __('Enabled'),
            'checkbox' => true,
            'explanation' => __(
                'Enable LDAP Authentication.'
            )
        ),
        array(
            'name' => 'side_auth_ldap_host',
            'label' => __('Host')
        ),
        array(
            'name' => 'side_auth_ldap_port',
            'label' => __('Port'),
            'explanation' => __(
                'Leave blank for default by protocol.'
            )
        ),
        array(
            'name' => 'side_auth_ldap_bindRequiresDn',
            'label' => __('Bind Requires DN'),
            'checkbox' => true,
            'explanation' => __(
                'Whether to automatically retrieve the DN corresponding to'.
                ' the username being authenticated if it is not already in DN'.
                ' form, and then re-bind with the proper DN'
            )
        ),
        array(
            'name' => 'side_auth_ldap_baseDn',
            'label' => __('Base DN'),
            'explanation' => __(
                'The DN under which all accounts being authenticated are'.
                ' located. This option is required.'
            )
        ),
        array(
            'name' => 'side_auth_ldap_accountFilterFormat',
            'label' => __('Account Filter Format'),
            'explanation' => __(
                'The LDAP search filter used to search for accounts. This'.
                ' string is a sprintf() style expression that must contain'.
                ' one %s to accommodate the username. Leave blank for the'.
                ' default based upon the LDAP Requires DN setting.'
            )
        )
    )
);
?>

<?php foreach ($sections as $section => $fields): ?>
    <h2><?php echo $section; ?></h2>

    <?php foreach ($fields as $field): ?>
        <div class="field">
            <div class="two columns alpha">
                <label for="<?php echo $field['name']; ?>">
                    <?php echo $field['label']; ?>
                </label>
            </div>
            <div class="inputs five columns omega">
                <?php if (isset($field['select'])): ?>
                    <select name="<?php echo $field['name']; ?>" id="<?php echo $field['name']; ?>">
                        <?php foreach ($field['select'] as $value => $option): ?>
                            <option value="<?php echo $value; ?>"<?php if (get_option($field['name']) == $value) echo ' selected'; ?>>
                                <?php echo $option; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php elseif (isset($field['checkbox'])): ?>
                    <input type="hidden" name="<?php echo $field['name']; ?>" value="">
                    <input type="checkbox" name="<?php echo $field['name']; ?>" id="<?php echo $field['name']; ?>" value="<?php echo $field['checkbox']; ?>"<?php if (get_option($field['name']) == $field['checkbox']) echo ' checked'; ?>>
                <?php else: ?>
                    <input type="<?php print(empty($field['password']) ? 'text' : 'password'); ?>" name="<?php echo $field['name']; ?>" id="<?php echo $field['name']; ?>" value="<?php echo get_option($field['name']); ?>">
                <?php endif; ?>

                <?php if (isset($field['explanation'])): ?>
                    <p class="explanation">
                        <?php echo $field['explanation']; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>

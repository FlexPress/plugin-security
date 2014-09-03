<?php

namespace FlexPress\Plugins\Security\Hooks;

use FlexPress\Components\Hooks\HookableTrait;

class Security
{

    use HookableTrait;

    const PASSWORD_EXPIRY_META_KEY = 'fp_password_expires';
    const COOKIE_CHECK_KEY = 'forecms-cookie-check';

    /**
     * Hooks into the activation of a user to add the meta key which is set
     * to the current date forcing the user to change their password on login
     *
     * @author Tim Perry
     * @type action
     */
    public function wpmuActivateUser($user_id, $password, $meta)
    {
        add_user_meta($user_id, self::PASSWORD_EXPIRY_META_KEY, time());
    }

    /**
     *
     * Disable XMLRPC
     *
     * @type filter
     * @return bool
     * @author Tim Perry
     *
     */
    public function xmlrpcEnabled()
    {
        return false;
    }

    /**
     * Regenerates the session id on logout
     *
     * @type action
     * @author Tim Perry
     *
     */
    public function wp_logout()
    {
        session_regenerate_id(true);
    }

    /**
     *
     * Shorten the login for password protected posts
     *
     * @type filter
     *
     * @return int
     * @author Tim Perry
     *
     */
    public function postPasswordExpires()
    {
        return 60 * MINUTE_IN_SECONDS;
    }

    /**
     *
     * Shorten the login timeout
     *
     * @type filter
     *
     * @return int
     * @author Tim Perry
     *
     */
    public function authCookieExpiration()
    {
        return 30 * MINUTE_IN_SECONDS;
    }

    /**
     * Hook into init
     *
     * @author - Tim Perry
     * @type action
     */
    public function init()
    {

        if (get_option(UI::OPTION_NAME_DISABLE_PASSWORD_RESETS)
            && $GLOBALS['pagenow'] == 'wp-login.php'
            && isset($_GET['action'])
            && preg_match('/resetpass|rp|lostpassword|retrievepassword/', $_GET['action'])
        ) {

            wp_die(
                'For security reasons password resets have been disabled, please speak to your administrator to have your password reset.'
            );

        }
    }

    /**
     * Hook into when a user resets their password
     *
     * @author - Tim Perry
     * @type action
     */
    public function passwordReset($user, $new_pass)
    {
        if (isset($user->ID)) {
            $this->updatePasswordExpiry($user->ID);
        }
    }

    /**
     * Hook into the admin notices
     *
     * @author Tim Perry
     * @type action
     *
     */
    public function adminNotices()
    {
        if ($this->currentUsersPasswordHasExpired()) {

            ?>
            <div class="error">
                <p><strong>ERROR</strong>: Your password has expired, please change it below before continuing.</p>
            </div>
        <?php

        }
    }

    /**
     * Hook into the profile being updated
     *
     * @author Tim Perry
     * @type action
     * @params 2
     *
     */
    public function profileUpdate($user_id, $old_user_data)
    {

        $user = new \WP_User($user_id);

        if ($user->data->user_pass != $old_user_data->user_pass) {
            $this->updatePasswordExpiry($user_id);
        }

    }

    /**
     * Check fields before a user is created, if errors then output error message and halt register
     *
     * @param object
     *
     * @return object
     * @author Adam Bulmer
     * @type action
     *
     */
    public function adminInit()
    {

        $url = admin_url("profile.php");
        if ($this->currentUsersPasswordHasExpired() && ($GLOBALS['pagenow'] != 'profile.php')) {

            wp_redirect($url);
            exit;

        }
    }

    /**
     *
     * @type action
     * @author Tim Perry
     *
     */
    public function loginHead()
    {
        ?>
        <script>
            jQuery(document).ready(function () {
                jQuery(":input").attr("autocomplete", "off");
            });
        </script>
    <?php
    }

    /**
     * Returns the evaluation of if the current users password has expired
     *
     * @return bool
     * @author Tim Perry
     */
    protected function currentUsersPasswordHasExpired()
    {

        $password_expires = get_user_meta(get_current_user_id(), self::PASSWORD_EXPIRY_META_KEY, true);

        if (empty($password_expires)) {
            return true;
        }

        return ($password_expires <= time());

    }

    /**
     * Used to set the expiry of the password
     *
     * @param $user_id
     *
     * @return string
     * @author Tim Perry
     */
    protected function updatePasswordExpiry($user_id)
    {

        $days = apply_filters('fpsecurity_password_expiry_days', get_option(UI::OPTION_NAME_PASSWORD_EXPIRY_DAYS, 90));
        $nextPasswordExpirtyDate = strtotime("+" . $days . " day");

        update_user_meta($user_id, self::PASSWORD_EXPIRY_META_KEY, $nextPasswordExpirtyDate);

    }
}

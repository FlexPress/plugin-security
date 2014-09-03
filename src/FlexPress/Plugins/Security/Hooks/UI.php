<?php

namespace FlexPress\Plugins\Security\Hooks;

use FlexPress\Components\Hooks\HookableTrait;

class UI
{

    use HookableTrait;

    const SETTINGS_GROUP_NAME = 'fpsecurity_settings_group';
    const OPTION_NAME_PASSWORD_EXPIRY_DAYS = 'fp_password_expirty_days';
    const OPTION_NAME_DISABLE_PASSWORD_RESETS = 'fp_disable_password_resets';

    protected $dic;

    /**
     * Require a dic so we can get the base dir for rendering our views
     *
     * @param $dic
     */
    public function __construct($dic)
    {
        $this->dic = $dic;
    }

    /**
     *
     * Add the settings
     *
     * @author Tim Perry
     * @type action
     *
     */
    public function adminInit()
    {
        register_setting(self::SETTINGS_GROUP_NAME, self::OPTION_NAME_PASSWORD_EXPIRY_DAYS);
        register_setting(self::SETTINGS_GROUP_NAME, self::OPTION_NAME_DISABLE_PASSWORD_RESETS);
    }

    /**
     *
     * Add the options page
     *
     * @author Tim Perry
     * @type action
     *
     */
    public function adminMenu()
    {
        add_options_page(
            'Security',
            'Security',
            'manage_options',
            'flexpress-security-options',
            array($this, 'optionsPageCallback')
        );
    }

    /**
     *
     *  Callback for the options page
     *
     * @author Tim Perry
     *
     */
    public function optionsPageCallback()
    {

        if (!class_exists('Timber')) {

            echo '<p>Please install and enable timber.</p>';
            return;

        }

        $context = \Timber::get_context();

        $context['settingsGroupName'] = self::SETTINGS_GROUP_NAME;

        $context['fieldNames'] = array(
            'expiryDays' => self::OPTION_NAME_PASSWORD_EXPIRY_DAYS,
            'disablePasswordResets' => self::OPTION_NAME_DISABLE_PASSWORD_RESETS
        );

        $context['currentValues'] = array(
            'expiryDays' => get_option(self::OPTION_NAME_PASSWORD_EXPIRY_DAYS, 90),
            'disablePasswordResets' => get_option(self::OPTION_NAME_DISABLE_PASSWORD_RESETS)
        );

        \Timber::render($this->dic['securityPlugin']->getPath() . '/views/options-page.twig', $context);
    }

}
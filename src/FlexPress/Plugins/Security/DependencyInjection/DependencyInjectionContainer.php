<?php

namespace FlexPress\Plugins\Security\DependencyInjection;

use FlexPress\Components\Hooks\Hooker;
use FlexPress\Plugins\Security\Hooks\UI;
use FlexPress\Plugins\Security\Security as SecurityPlugin;
use FlexPress\Plugins\Security\Hooks\Security as SecurityHookable;

class DependencyInjectionContainer extends \Pimple
{

    public function init()
    {
        $this['objectStorage'] = function () {
            return new \SplObjectStorage();
        };

        $this['securityHookable'] = function ($c) {
            return new SecurityHookable($c);
        };

        $this['uiHookable'] = function ($c) {
            return new UI($c);
        };

        $this['hooker'] = function ($c) {
            return new Hooker($c['objectStorage'], array(
                $c['securityHookable'],
                $c['uiHookable']
            ));
        };

        $this['securityPlugin'] = function ($c) {
            return new SecurityPlugin($c['hooker']);
        };
    }
}

<?php

namespace FlexPress\Plugins\Security;

use FlexPress\Plugins\AbstractPlugin;
use FlexPress\Components\Hooks\Hooker;

class Security extends AbstractPlugin
{

    /**
     * @var Hooker
     */
    protected $hooker;

    /**
     * Depends on hooker
     *
     * @param $hooker
     */
    public function __construct(Hooker $hooker)
    {
        $this->hooker = $hooker;
    }

    /**
     *
     * Set up the hooker
     *
     * @param $file
     * @author Tim Perry
     *
     */
    public function init($file)
    {

        parent::init($file);
        $this->hooker->hookUp();

    }

    /**
     *
     * Returns the plugins directory
     *
     * @author Tim Perry
     *
     */
    public function getPath()
    {
        return $this->path;
    }

}

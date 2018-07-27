<?php

namespace Xe_SuperAcessManager\Inc;
use Xe_SuperAcessManager\Inc\Admin\AccessManager;
use Xe_SuperAcessManager\Inc\Admin\ListHandler;

/**
 * Class Xeweb_sam_main
 */
class Main
{

    public function __construct() {

        // Load Classes
        new AccessManager();
        new ListHandler();

    }
}
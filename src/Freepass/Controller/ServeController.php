<?php
/**
 * Serve controller file
 *
 * @package Freepass
 */

namespace Freepass\Controller;

use Nerdery\Plugin\Controller\Controller;

/**
 * Serve Controller
 *
 * @uses Controller
 * @package Freepass
 * @author Jansen Price <jansen.price@nerdery.com>
 * @version $Id$
 */
class ServeController extends Controller
{
    /**
     * indexAction
     *
     * @return void
     */
    public function indexAction()
    {
        $plugin = $this->getContainer();
        $proxy = $plugin->getProxy();

        $routePattern = $proxy->getOption('freepass_virtual_folder');
        $basePath = ABSPATH . $proxy->getOption('freepass_real_path');
        $basePath = realpath($basePath);

        $scriptUrl = ltrim($_SERVER['SCRIPT_URL'], '/');

        $url = str_replace($routePattern, '', $scriptUrl);
        $path = $basePath . $url;

        if (file_exists($path)) {
            readfile($path);
        }
    }
}


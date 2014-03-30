<?php
/*
Plugin Name: Static Subdir
Plugin URI:
Description: Serve static files from a specific path on server
Version: 1.0.0
Author: Jansen Price
Author URI: http://nerdery.com
*/

namespace StaticSubdir;

require_once 'vendor/autoload.php';

use Nerdery\Plugin\Plugin;
use Nerdery\Plugin\Router\Route;
use StaticSubdir\WordPress\Proxy;

/**
 * Bootstrap the plugin
 *
 * @return void
 */
function bootstrap()
{
    /*
     * Create a new plugin factory
     * 
     * Configure the plugin by passing an array of settings
     * to the Factory.
     * 
     * - templatePath, this should be the path to where you will
     *   put your Twig templates. Recommend: "resources/scripts/"
     *
     * - prefix, any database tables that this plugin creates will
     *   add this prefix to the table names, the plugin will also
     *   prepend the WordPress prefix to this.
     *
     * - slug, This is an arbitrary slug that this plugin will use
     *   to generate custom hooks and events. This slug should be
     *   unique.
     */
    $factory = new \Nerdery\Plugin\Factory\Factory(array(
        'templatePath' => dirname(__FILE__) . '/resources/views',
        'prefix' => 'staticsubdir_',
        'slug' => 'staticsubdir',
    ));
    $plugin = $factory->make();
    
    // Load in our extended proxy
    $plugin[Plugin::CONTAINER_KEY_WP_PROXY] = function ($c) {
        return new Proxy();
    };

    // This is the name of the plugin according to WP plugins page
    $plugin['plugin.actionlink.name'] = basename(__DIR__) . '/' . basename(__FILE__);

    /*
     * Register controllers
     *
     * All of these controllers are loaded immediately every time this plugin
     * is loaded. Keep this in mind when considering performance. However, as
     * the controllers are the "meat and potatoes" of the plugin, it only makes
     * sense that they be loaded immediately so that they can hook into the
     * necessary WordPress event calls they need to respond to.
     */
    $plugin['controller.settings'] = new \StaticSubdir\Controller\SettingsController($plugin);
    $plugin['controller.serve'] = new \StaticSubdir\Controller\ServeController($plugin);

    // Register the custom route
    $plugin['controller.settings']->registerStaticSubdirRoute();

    // Run the plugin!
    $plugin->run();
}

bootstrap();

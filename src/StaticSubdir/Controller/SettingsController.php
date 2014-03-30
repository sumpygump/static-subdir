<?php
/**
 * Settings controller file
 *
 * @package StaticSubdir
 */

namespace StaticSubdir\Controller;

use Nerdery\Plugin\Controller\Controller;
use Nerdery\Plugin\Router\Route;

/**
 * SettingsController
 *
 * @uses Controller
 * @package StaticSubdir
 * @author Jansen Price <jansen.price@nerdery.com>
 * @version $Id$
 */
class SettingsController extends Controller
{
    /**
     * Constants
     */
    const SETTINGS_PAGE_SLUG = 'staticsubdir_settings';

    const SETTING_VIRTUAL_PATH = 'virtual_path';
    const SETTING_VIRTUAL_PATH_LABEL = 'Virtual Path';

    const SETTING_REAL_PATH = 'real_path';
    const SETTING_REAL_PATH_LABEL = 'Real Path';

    /**
     * settings
     *
     * @var array
     */
    private $settings = array(
        array(
            'name' => self::SETTING_VIRTUAL_PATH,
            'label' => self::SETTING_VIRTUAL_PATH_LABEL,
            'template' => 'settings/fields/virtual-path.twig',
        ),
        array(
            'name' => self::SETTING_REAL_PATH,
            'label' => self::SETTING_REAL_PATH_LABEL,
            'template' => 'settings/fields/real-path.twig',
        ),
    );

    /**
     * Initialize controller
     *
     * @return self
     */
    public function initialize()
    {
        $proxy = $this->getProxy();
        $container = $this->getContainer();
    }

    /**
     * Initialize the admin interface
     *
     * @return $this
     */
    public function initializeAdmin()
    {
        foreach ($this->settings as $field) {
            $this->getProxy()->registerSetting(
                self::SETTINGS_PAGE_SLUG,
                $this->getOptionName($field['name'])
            );
        }
    }

    /**
     * Get option name specific to this plugin
     *
     * @param mixed $name
     * @return void
     */
    public function getOptionName($name)
    {
        $slug = $this->getContainer()->getSlug();

        return $slug . '_' . $name;
    }

    /**
     * Get option specific to this plugin
     *
     * @param string $name Option name
     * @return string
     */
    public function getPluginOption($name)
    {
        return $this->getProxy()->getOption($this->getOptionName($name));
    }

    /**
     * Register admin menu
     *
     * @return void
     */
    public function registerAdminRoutes()
    {
        $controller = $this;
        $proxy = $this->getProxy();

        // Register the settings page
        $hook = $proxy->addMenuPage(
            'StaticSubdir',
            'StaticSubdir',
            'manage_options',
            self::SETTINGS_PAGE_SLUG,
            function () use ($controller) {
                echo $controller->indexAction();
            }
        );

        // Register hook for when the settings page is loaded
        $proxy->addAction(
            'load-' . $hook,
            function () use ($controller) {
                return $controller->onLoadSettingsPage();
            }
        );

        // Add an action link
        $proxy->addFilter(
            'plugin_action_links_' . $this->getContainer()->get('plugin.actionlink.name'),
            function ($links) use ($controller) {
                $url = $controller->getProxy()->getAdminUrl(null, "admin.php?page=" . self::SETTINGS_PAGE_SLUG);
                $link = $controller->getContainer()->getViewRenderer()->render(
                    'settings/anchor.twig',
                    array(
                        'url' => $url,
                        'body' => 'Settings',
                    )
                );

                array_unshift($links, $link);
                return $links;
            }
        );
    }

    /**
     * Admin settings page
     *
     * @return void
     */
    public function indexAction()
    {
        $proxy = $this->getProxy();
        $container = $this->getContainer();

        $settingsMarkup = $proxy->settingsFields(self::SETTINGS_PAGE_SLUG);
        $fieldMarkup = $this->buildFieldMarkup();

        $output = array(
            'settingsMarkup' => $settingsMarkup,
            'fieldMarkup' => $fieldMarkup,
        );

        return $this->render('settings/index.twig', $output);
    }

    /**
     * Method that is called when the settings page is loaded
     *
     * @return void
     */
    public function onLoadSettingsPage()
    {
        $container = $this->getContainer();

        // After user has saved the settings form
        if ($container->getRequest()->get('settings-updated')) {
            if ($this->registerStaticSubdirRoute()) {
                // Since we changed the route we need to re-initialize it
                $container->getRouter()->initRoutes();

                $this->getFlashBag()->add('notice', 'Settings saved');
            }
        }

        $routePattern = $this->getPluginOption(self::SETTING_VIRTUAL_PATH);

        if (trim(rtrim($routePattern, '/')) == '') {
            $this->getFlashBag()->add('error', 'Static Subdir is disabled due to an invalid virtual path');
        }
    }

    /**
     * Register StaticSubdir Route
     *
     * @return bool
     */
    public function registerStaticSubdirRoute()
    {
        // WP has a hard time registering the route when it ends in '/'
        $routePattern = rtrim($this->getPluginOption(self::SETTING_VIRTUAL_PATH), '/');

        if (trim($routePattern) == '') {
            return false;
        }

        // Define the custom freepass route...
        $route = new Route(
           '^' . $routePattern,
           'controller.serve:indexAction'
        );

        $this->getContainer()->registerRoute($route);

        return true;
    }

    /**
     * Build the settings field markup
     *
     * @return string
     */
    public function buildFieldMarkup()
    {
        $container = $this->getContainer();
        $viewRenderer = $container->getViewRenderer();

        $slug = $container->getSlug();
        $fieldMarkup = '';

        foreach ($this->settings as $field) {
            $fieldMarkup .= $viewRenderer->render(
                $field['template'],
                array(
                    'name' => $slug . '_' . $field['name'],
                    'label' => $field['label'],
                    'value' => $this->getPluginOption($field['name']),
                    'abspath' => ABSPATH,
                    'homeUrl' => $this->getHomeUrl(),
                )
            );
        }

        return $fieldMarkup;
    }

    /**
     * Get the home URL for current site
     *
     * @return string
     */
    public function getHomeUrl()
    {
        $homeUrl = $this->getProxy()->getOption('home');

        // Include blog prefix if needed.
        if ($this->getProxy()->isMultisite()
            && $this->getProxy()->isSubdomainInstall()
            && $this->getProxy()->isMainSite()
        ) {
            $homeUrl .= "/blog";
        }

        return $homeUrl . '/';
    }
}

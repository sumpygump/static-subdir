<?php
/**
 * Settings controller file
 *
 * @package Freepass
 */

namespace Freepass\Controller;

use Nerdery\Plugin\Controller\Controller;

/**
 * SettingsController
 *
 * @uses Controller
 * @package Freepass
 * @author Jansen Price <jansen.price@nerdery.com>
 * @version $Id$
 */
class SettingsController extends Controller
{
    /**
     * Constants
     */
    const SETTINGS_PAGE_SLUG = 'freepass_settings';

    const SETTING_PLUGIN_NAME = 'virtual_folder';
    const SETTING_PLUGIN_NAME_LABEL = 'Virtual Folder';

    const SETTING_PLUGIN_DESCRIPTION = 'real_path';
    const SETTING_PLUGIN_DESCRIPTION_LABEL = 'Real Path';

    /**
     * settings
     *
     * @var array
     */
    private $settings = array(
        array(
            'name' => self::SETTING_PLUGIN_NAME,
            'label' => self::SETTING_PLUGIN_NAME_LABEL,
            'template' => 'settings/fields/virtual-folder.twig',
        ),
        array(
            'name' => self::SETTING_PLUGIN_DESCRIPTION,
            'label' => self::SETTING_PLUGIN_DESCRIPTION_LABEL,
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
        $proxy = $this->getProxy();
        $slug = $this->getContainer()->getSlug();
        foreach ($this->settings as $field) {
            $proxy->registerSetting(self::SETTINGS_PAGE_SLUG, $slug . '_' . $field['name']);
        }
    }

    /**
     * Register admin menu
     *
     * @return void
     */
    public function registerAdminRoutes()
    {
        // Register the settings page
        $controller = $this;
        $proxy = $this->getProxy();
        $proxy->addMenuPage(
            'Freepass',
            'Freepass',
            'manage_options',
            self::SETTINGS_PAGE_SLUG,
            function () use ($controller) {
                echo $controller->indexAction();
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
     * Build the settings field markup
     *
     * @return string
     */
    public function buildFieldMarkup()
    {
        $proxy = $this->getProxy();
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
                    'value' => $proxy->getOption($slug . '_' . $field['name']),
                    'abspath' => ABSPATH,
                )
            );
        }

        return $fieldMarkup;
    }

    public function passAction()
    {
        echo 'a';
    }
}

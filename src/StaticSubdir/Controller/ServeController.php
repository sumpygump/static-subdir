<?php
/**
 * Serve controller file
 *
 * @package StaticSubdir
 */

namespace StaticSubdir\Controller;

use Symfony\Component\HttpFoundation\Response;
use Nerdery\Plugin\Controller\Controller;

/**
 * Serve Controller
 *
 * @uses Controller
 * @package StaticSubdir
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
        $settings = $plugin['controller.settings'];

        $basePath = ABSPATH . $settings->getPluginOption(SettingsController::SETTING_REAL_PATH);
        $basePath = realpath($basePath);

        $targetFile = $this->getTargetFileFromUrl(
            $this->getRequest()->server->get('SCRIPT_URL'),
            $settings->getPluginOption(SettingsController::SETTING_VIRTUAL_PATH)
        );

        // If basePath turned out to be false, that means the
        // real path doesn't exist, bail out.
        if (false === $basePath) {
            $this->serveFileNotFound($targetFile);
            return false;
        }

        $path = rtrim($basePath, '/') . '/' . $targetFile;

        if (file_exists($path) && is_readable($path)) {
            $this->serveFile($path);
        } else {
            $this->serveFileNotFound($targetFile);
        }
    }

    /**
     * Get Target file from URL
     *
     * Because we don't know what part of the URL belongs to the sub-folder of 
     * the root of the WP install, we are going to find our virtual folder in 
     * the URL and return whatever comes after that.
     *
     * Example:
     *     http://domain/subfolder/subsubfolder/virtualfolder/item.html
     *     Will return 'item.html' because that is the file we wanted
     *
     * @param mixed $url
     * @param mixed $virtualFolder
     * @return string
     */
    private function getTargetFileFromUrl($url, $virtualFolder)
    {
        $url = rtrim($url, '/');
        $virtualFolder = rtrim($virtualFolder, '/');

        $virtualFolderStart = strpos($url, $virtualFolder);

        $targetFile = substr($url, $virtualFolderStart + strlen($virtualFolder));

        // Default to a static index file
        if ($targetFile == '' || substr($targetFile, -1) == '/') {
            $targetFile = $targetFile . 'index.html';
        }

        return ltrim($targetFile, '/');
    }

    /**
     * Serve file
     *
     * @param string $file Path to file
     * @return void
     */
    private function serveFile($file)
    {
        $response = new Response();

        $contentType = $this->getContentTypeFromExtension($file);

        if ($contentType == 'text/php') {
            $response->headers->set('Content-Type', 'text/html');
            $response->sendHeaders();
            highlight_file($file);
        } else {
            $response->headers->set('Content-Type', $contentType);
            $response->sendHeaders();
            readfile($file);
        }
    }

    /**
     * serveNotFound
     *
     * @param mixed $file
     * @return void
     */
    private function serveFileNotFound($file)
    {
        $response = new Response();

        $response->setStatusCode(404);

        $response->setContent(
            $this->render(
                'error/notfound.twig',
                array(
                    'heading' => '404 Not Found',
                    'message' => "File '$file' not found",
                )
            )
        );

        $response->send();
    }

    /**
     * getContentTypeFromExtension
     *
     * @param string $file Path to file
     * @return string
     */
    private function getContentTypeFromExtension($file)
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        // TODO: This mini-server is looking kinda gross. Figure out a more 
        // automatic and robust way to send the correct mime types for each 
        // file

        $contentType = 'text/html';

        switch ($extension) {
            case 'css':
                $contentType = 'text/css';
                break;
            case 'ico':
            case 'png':
            case 'jpg':
                $contentType = sprintf('image/%s', $extension);
                break;
            case 'json':
                $contentType = 'application/json';
                break;
            case 'js':
                $contentType = 'text/javascript';
                break;
            case 'txt':
            case 'log':
                $contentType = 'text/plain';
                break;
            case 'php':
                $contentType = 'text/php';
                break;
            case 'gz':
                $contentType = 'application/x-gzip';
                break;
            case 'rar':
                $contentType = 'application/x-rar-compressed';
                break;
            case 'tar':
                $contentType = 'application/x-tar';
                break;
            case 'zip':
                $contentType = 'application/zip';
                break;
            case 'html': // pass through
            default:
                break;
        }

        return $contentType;
    }
}

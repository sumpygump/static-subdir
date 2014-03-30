<?php
/**
 * Proxy class file
 *
 * @package StaticSubdir
 */

namespace StaticSubdir\WordPress;

use Nerdery\WordPress\Proxy as WordPressProxy;

/**
 * Proxy
 *
 * An extension of the Nerdery WordPress Proxy class
 *
 * @uses WordPressProxy
 * @package StaticSubdir
 * @author Jansen Price <jansen.price@gmail.com>
 */
class Proxy extends WordPressProxy
{
    /**
     * isMultisite
     *
     * Determine whether Multisite support is enabled.
     *
     * @see http://codex.wordpress.org/Function_Reference/is_multisite
     * @return bool
     */
    public function isMultisite()
    {
        return is_multisite();
    }

    /**
     * isSubdomainInstall
     *
     * Whether a sub-domain configuration is enabled.
     *
     * @see https://codex.wordpress.org/Function_Reference/is_subdomain_install
     * @return bool
     */
    public function isSubdomainInstall()
    {
        return is_subdomain_install();
    }

    /**
     * isMainSite
     *
     * Test if site is main site, given site id
     *
     * @see http://codex.wordpress.org/Function_Reference/is_main_site
     * @param int $blogId Optional site id to test (defaults to current site)
     * @return void
     */
    public function isMainSite($blogId = '')
    {
        if ($blogId) {
            return is_main_site($blogId);
        }

        return is_main_site();
    }

    /**
     * Get admin URL
     *
     * Retrieve the url to the admin area for a given site.
     *
     * @see http://codex.wordpress.org/Function_Reference/get_admin_url
     * @param int $blogId Blog id, defaults to current site
     * @param string $path Path to append to URL
     * @param string $scheme URL scheme, 'admin', 'http', or 'https'
     * @return string
     */
    public function getAdminUrl($blogId = null, $path = null, $scheme = null)
    {
        return get_admin_url($blogId, $path, $scheme);
    }
}

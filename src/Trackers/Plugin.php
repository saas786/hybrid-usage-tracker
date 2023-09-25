<?php

namespace Hybrid\Usage\Tracker\Trackers;

use Hybrid\Usage\Tracker\Contracts\CollectionInterface;
use Hybrid\Usage\Tracker\Contracts\Tracker;

/**
 * Plugin.
 */
class Plugin implements Tracker, CollectionInterface {

    /**
     * Returns the tracker data.
     *
     * @return array
     */
    public function get() {
        return [ 'plugins' => self::getData() ];
    }

    /**
     * Returns all plugins.
     *
     * @return array
     */
    protected static function getData() {
        return self::getAllPlugins();
    }

    /**
     * Get all plugins grouped into activated or not.
     *
     * @return array
     */
    private static function getAllPlugins() {
        // Ensure get_plugins function is loaded
        if ( ! function_exists( 'get_plugins' ) ) {
            include ABSPATH . '/wp-admin/includes/plugin.php';
        }

        $plugins             = get_plugins();
        $active_plugins_keys = get_option( 'active_plugins', [] );
        $active_plugins      = [];

        $plugins = array_map( [ __CLASS__, 'formatPluginData' ], $plugins );

        foreach ( $plugins as $k => $v ) {
            if ( in_array( $k, $active_plugins_keys ) ) {
                // Remove active plugins from list so we can show active and inactive separately.
                unset( $plugins[ $k ] );
                $active_plugins[ $k ] = $v;
            } else {
                $plugins[ $k ] = $v;
            }
        }

        return [
            'active_plugins'   => $active_plugins,
            'inactive_plugins' => $plugins,
        ];
    }

    /**
     * Formats the plugin array.
     *
     * @param  array $plugin The plugin details.
     * @return array The formatted array.
     */
    protected static function formatPluginData( array $plugin ) {
        return [
            'author'  => [
                'name' => wp_strip_all_tags( $plugin['Author'], true ),
                'url'  => $plugin['AuthorURI'],
            ],
            'name'    => $plugin['Name'],
            'network' => $plugin['Network'],
            'url'     => $plugin['PluginURI'],
            'version' => $plugin['Version'],
        ];
    }

}

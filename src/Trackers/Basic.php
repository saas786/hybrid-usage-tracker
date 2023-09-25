<?php

namespace Hybrid\Usage\Tracker\Trackers;

use Hybrid\Usage\Tracker\Contracts\CollectionInterface;
use Hybrid\Usage\Tracker\Contracts\Tracker;

/**
 * Basic.
 */
class Basic implements Tracker, CollectionInterface {

    /**
     * Returns the tracker data.
     *
     * @return array
     */
    public function get() {
        return [
            'siteTitle'      => get_option( 'blogname' ),
            '@timestamp'     => (int) gmdate( 'Uv' ),
            'wpVersion'      => static::getWordpressVersion(),
            'homeURL'        => home_url(),
            'adminURL'       => admin_url(),
            'isMultisite'    => is_multisite(),
            'siteLanguage'   => get_bloginfo( 'language' ),
            'gmt_offset'     => get_option( 'gmt_offset' ),
            'timezoneString' => get_option( 'timezone_string' ),
            'countPosts'     => static::getPostCount( 'post' ),
            'countPages'     => static::getPostCount( 'page' ),
            'users'          => static::getUserCounts(),
        ];
    }

    /**
     * Returns the WordPress version.
     *
     * @return string
     */
    protected static function getWordpressVersion() {
        global $wp_version;

        return $wp_version;
    }

    /**
     * Get user totals based on user role.
     *
     * @return array
     */
    private static function getUserCounts() {
        $user_count          = [];
        $user_count_data     = count_users();
        $user_count['total'] = $user_count_data['total_users'];

        // Get user count based on user role.
        foreach ( $user_count_data['avail_roles'] as $role => $count ) {
            $user_count[ $role ] = $count;
        }

        return $user_count;
    }

    /**
     * Returns the number of posts of a certain type.
     *
     * @param string $post_type The post type return the count for.
     * @return int The count for this post type.
     */
    protected function getPostCount( $post_type ) {
        $count = wp_count_posts( $post_type );

        if ( isset( $count->publish ) ) {
            return $count->publish;
        }

        return 0;
    }

}

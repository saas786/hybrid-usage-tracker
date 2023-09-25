<?php

namespace Hybrid\Usage\Tracker\Trackers;

use Hybrid\Usage\Tracker\Contracts\CollectionInterface;
use Hybrid\Usage\Tracker\Contracts\Tracker;
use WP_Theme;

/**
 * Theme.
 */
class Theme implements Tracker, CollectionInterface {

    /**
     * Returns the tracker data.
     *
     * @return array
     */
    public function get() {
        $theme = wp_get_theme();

        return [
            'theme' => [
                'author'       => [
                    'name' => $theme->get( 'Author' ),
                    'url'  => $theme->get( 'AuthorURI' ),
                ],
                'name'         => $theme->get( 'Name' ),
                'parent_theme' => self::getParentTheme( $theme ),
                'url'          => $theme->get( 'ThemeURI' ),
                'version'      => $theme->get( 'Version' ),
            ],
        ];
    }

    /**
     * Returns the name of the parent theme.
     *
     * @param  \WP_Theme $theme The theme object.
     * @return string|null
     */
    private static function getParentTheme( WP_Theme $theme ) {
        if ( is_child_theme() ) {
            return $theme->get( 'Template' );
        }

        return null;
    }

}

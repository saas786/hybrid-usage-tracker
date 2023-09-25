<?php

namespace Hybrid\Usage\Tracker\Contracts;

/**
 * Collects the data from the added collection objects.
 */
abstract class CollectorAbstract {

    /**
     * Holds the collections.
     *
     * @var array<\Hybrid\Usage\Tracker\Contracts\CollectionInterface>
     */
    protected $collections = [];

    /**
     * Adds a collection object to the collections.
     */
    public function add_collection( CollectionInterface $collection ) {
        $this->collections[] = $collection;
    }

    /**
     * Collects the data from the collection objects.
     *
     * @return array
     */
    public function collect() {
        $data = [];

        foreach ( $this->collections as $collection ) {
            $data = array_merge( $data, $collection->get() );
        }

        return $data;
    }

    /**
     * Returns the collected data as a JSON encoded string.
     *
     * @return false|string The encode string.
     */
    public function get_data_as_json() {
        return $this->format_json_encode( $this->collect() );
    }

    /**
     * Prepares data for outputting as JSON.
     *
     * @param array $data
     * @param array $debug
     * @return bool|string The prepared JSON string.
     */
    public function format_json_encode( $data, $debug = false ) {
        $flags = ( JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

        if ( $debug ) {
            $flags = ( $flags | JSON_PRETTY_PRINT );
        }

        return wp_json_encode( $data, $flags );
    }

}

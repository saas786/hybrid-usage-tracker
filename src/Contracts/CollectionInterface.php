<?php

namespace Hybrid\Usage\Tracker\Contracts;

/**
 * Interface that represents a collection.
 */
interface CollectionInterface {

    /**
     * Returns the collection data.
     *
     * @return array The collection data.
     */
    public function get();

}

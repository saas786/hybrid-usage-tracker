<?php

namespace Hybrid\Usage\Tracker;

/**
 * Format bytes into a human readable representation, e.g.
 * 6815744 => 6.5M
 *
 * @param     $bytes
 * @param int   $precision
 * @return string
 */
function bytes_to_human_readable( $bytes, $precision = 2 ) {
    $suffixes = [ '', 'k', 'M', 'G', 'T', 'P' ];
    $base     = log( $bytes ) / log( 1024 );

    return round( pow( 1024, $base - floor( $base ) ), $precision ) . $suffixes[ floor( $base ) ];
}

/**
 * Convert human readable formatting of bytes to bytes, e.g.
 * 6.5M => 6815744
 *
 * @param $string
 * @return int
 */
function human_readable_to_bytes( $string ) {
    $suffix = strtolower( substr( $string, -1 ) );
    $result = substr( $string, 0, -1 );

    switch ( $suffix ) {
        case 'p': // Petabytes
            $result *= 1024;
        case 't': // Terabytes
            $result *= 1024;
        case 'g': // Gigabytes
            $result *= 1024;
        case 'm': // Megabytes
            $result *= 1024;
        case 'k': // Kilobytes
            $result *= 1024;
            break;
        default:
            // No suffix or unknown suffix, return as is.
            return $string;
    }

    return ceil( $result );
}

<?php

namespace Hybrid\Usage\Tracker\Trackers;

use Hybrid\Usage\Tracker\Contracts\CollectionInterface;
use Hybrid\Usage\Tracker\Contracts\Tracker;
use function Hybrid\Usage\Tracker\human_readable_to_bytes;

/**
 * Server.
 */
class Server implements Tracker, CollectionInterface {

    /**
     * Returns the tracker data.
     *
     * @return array
     */
    public function get() {
        return [ 'server' => self::getData() ];
    }

    /**
     * Returns the server details.
     *
     * @return array
     */
    protected static function getData() {
        $server_data = [];

        if ( array_key_exists( 'SERVER_SOFTWARE', $_SERVER ) && ! empty( $_SERVER['SERVER_SOFTWARE'] ) ) {
            $server_data['software'] = $_SERVER['SERVER_SOFTWARE'];
        }

        // Validate if the server address is a valid IP-address.
        $ipaddress = filter_input( INPUT_SERVER, 'SERVER_ADDR', FILTER_VALIDATE_IP );

        if ( $ipaddress ) {
            $server_data['ip']       = $ipaddress;
            $server_data['Hostname'] = gethostbyaddr( $ipaddress );
        }

        if ( function_exists( 'phpversion' ) ) {
            $server_data['php_version'] = phpversion();
        } elseif ( defined( 'PHP_VERSION' ) ) {
            $server_data['php_version'] = PHP_VERSION;
        }

        if ( function_exists( 'ini_get' ) ) {
            $server_data['php_post_max_size']  = size_format( human_readable_to_bytes( ini_get( 'post_max_size' ) ) );
            $server_data['php_time_limit']     = ini_get( 'max_execution_time' );
            $server_data['php_max_input_vars'] = ini_get( 'max_input_vars' );
            $server_data['php_suhosin']        = extension_loaded( 'suhosin' ) ? 'Yes' : 'No';
        }

        global $wpdb;
        $server_data['mysql_version'] = $wpdb->db_version();

        $server_data['os']                   = php_uname( 's r' );
        $server_data['php_max_upload_size']  = size_format( wp_max_upload_size() );
        $server_data['php_default_timezone'] = date_default_timezone_get();
        $server_data['php_soap']             = class_exists( 'SoapClient' ) ? 'Yes' : 'No';
        $server_data['php_fsockopen']        = function_exists( 'fsockopen' ) ? 'Yes' : 'No';
        $server_data['php_curl']             = self::getCurlInfo();
        $server_data['php_extensions']       = self::getPHPExtensions();

        return $server_data;
    }

    /**
     * Returns details about the curl version.
     *
     * @return array|null
     */
    protected static function getCurlInfo() {
        if ( ! function_exists( 'curl_version' ) ) {
            return null;
        }

        $curl = curl_version();

        $ssl_support = true;
        if ( ! $curl['features'] && CURL_VERSION_SSL ) {
            $ssl_support = false;
        }

        return [
            'ssl_support' => $ssl_support,
            'version'     => $curl['version'],
        ];
    }

    /**
     * Returns a list with php extensions.
     *
     * @return array
     */
    protected static function getPHPExtensions() {
        if ( ! function_exists( 'extension_loaded' ) ) {
            return [];
        }

        return [
            'bcmath'  => extension_loaded( 'bcmath' ),
            'filter'  => extension_loaded( 'filter' ),
            'imagick' => extension_loaded( 'imagick' ),
            'modXml'  => extension_loaded( 'modXml' ),
            'pcre'    => extension_loaded( 'pcre' ),
            'xml'     => extension_loaded( 'xml' ),
        ];
    }

}

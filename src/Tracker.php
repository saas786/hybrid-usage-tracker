<?php

namespace Hybrid\Usage\Tracker;

use Hybrid\Contracts\Bootable;
use Hybrid\Usage\Tracker\Contracts\CollectorAbstract;
use Hybrid\Usage\Tracker\Trackers\Basic;
use Hybrid\Usage\Tracker\Trackers\Plugin;
use Hybrid\Usage\Tracker\Trackers\Server;
use Hybrid\Usage\Tracker\Trackers\Theme;

/**
 * This class handles the tracking routine.
 */
class Tracker extends CollectorAbstract implements Bootable {

    /**
     * The tracking option name.
     *
     * @var string
     */
    public $option_name = 'hybrid_usage_tracker_last_request';

    /**
     * The limit for the option.
     *
     * @var int
     */
    protected $threshold = 0;

    /**
     * The endpoint to send the data to.
     *
     * @var string
     */
    protected $endpoint = '';

    /**
     * The current time.
     *
     * @var int
     */
    private $current_time;

    /**
     * The tracking prefix for cron job.
     *
     * @var string
     */
    public $cron_prefix = 'hybrid_usage_tracker_last_request';

    /**
     * Constructor.
     *
     * @param string $endpoint  The endpoint to send the data to.
     * @param int    $threshold The limit for the option.
     */
    public function __construct( $endpoint, $threshold ) {
        if ( ! $this->tracking_enabled() ) {
            return;
        }

        $this->endpoint     = $endpoint;
        $this->threshold    = $threshold;
        $this->current_time = time();
    }

    /**
     * Registers all hooks to WordPress.
     *
     * @return void
     */
    public function boot() {
        if ( ! $this->tracking_enabled() ) {
            return;
        }

        // Send tracking data on `admin_init`.
        add_action( 'admin_init', [ $this, 'send' ], 1 );
    }

    /**
     * Sends the tracking data.
     *
     * @param bool $force Whether to send the tracking data ignoring the two
     *                    weeks time threshold. Default false.
     * @return void
     */
    public function send( $force = false ) {
        if ( ! $this->should_send_tracking( $force ) ) {
            return;
        }

        // Set a 'content-type' header of 'application/json'.
        $tracking_request_args = [
            'headers' => [
                'content-type:' => 'application/json',
            ],
        ];

        $this->init_collector();

        $request = new Request( $this->endpoint, $tracking_request_args );
        $request->set_body( $this->get_data_as_json() );
        $request->send();

        update_option( $this->option_name, $this->current_time, 'yes' );
    }

    /**
     * Determines whether to send the tracking data.
     *
     * Returns false if tracking is disabled or the current page is one of the
     * admin plugins pages. Returns true when there's no tracking data stored or
     * the data was sent more than two weeks ago. The two weeks interval is set
     * when instantiating the class.
     *
     * @param bool $ignore_time_treshhold Whether to send the tracking data ignoring
     *                                    the two weeks time treshhold. Default false.
     * @return bool True when tracking data should be sent.
     */
    protected function should_send_tracking( $ignore_time_treshhold = false ) {
        global $pagenow;

        // Only send tracking on the main site of a multi-site instance. This returns true on non-multisite installs.
        if ( is_network_admin() || ! is_main_site() ) {
            return false;
        }

        // Because we don't want to possibly block plugin actions with our routines.
        if ( in_array( $pagenow, [ 'plugins.php', 'plugin-install.php', 'plugin-editor.php' ], true ) ) {
            return false;
        }

        $last_time = get_option( $this->option_name );

        // When tracking data haven't been sent yet or when sending data is forced.
        if ( ! $last_time || $ignore_time_treshhold ) {
            return true;
        }

        return $this->exceeds_treshhold( $this->current_time - $last_time );
    }

    /**
     * Checks if the given amount of seconds exceeds the set threshold.
     *
     * @param int $seconds The amount of seconds to check.
     * @return bool True when seconds is bigger than threshold.
     */
    protected function exceeds_treshhold( $seconds ) {
        return $seconds > $this->threshold;
    }

    /**
     * Init the collector for collecting the data.
     */
    public function init_collector() {
        $this->add_collection( new Basic() );
        $this->add_collection( new Server() );
        $this->add_collection( new Theme() );
        $this->add_collection( new Plugin() );
    }

    /**
     * See if we should run tracking at all.
     *
     * @return bool True when we can track, false when we can't.
     */
    public function tracking_enabled() {
        return false;
    }

}

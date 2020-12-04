<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if (!class_exists('wpd_Performance')) {
    /**
     * Performance measurement and storage
     *
     * @class 		wpd_Performance
     * @version		1.0
     * @package		WPDreams/Classes
     * @category	Class
     * @author 		Ernest Marcinko
     */
    class wpd_Performance {

        /**
         * @var array of performance values
         */
        private $records;
        /**
         * @var array default values for the records array
         */
        private $default = array(
            'run_count' => 0,
            'average_runtime' => 0,
            'average_memory' => 0,
            'last_runtime' => 0,
            'last_memory' => 0
        );
        /**
         * @var int current runtime
         */
        private $runtime;
        /**
         * @var int actual memory usage
         */
        private $memory;
        /**
         * @var string the name of the storage
         */
        private $key;

        /**
         * Setup Class
         *
         * @param string $key
         */
        function __construct($key = "plugin_performance") {
            $this->key = $key;
            $this->records = get_option($key, $this->default);
        }

        /**
         * Deletes the storage
         */
        function reset() {
            delete_option($this->key);
        }

        /**
         * Gets the storage
         *
         * @return array
         */
        function get_data() {
            return $this->records;
        }

        /**
         * Starts the measurement
         */
        function start_measuring() {
            $this->runtime = microtime(true);
            $this->memory = memory_get_usage(true);
        }

        /**
         * Stops the measurement
         */
        function stop_measuring() {
            $this->runtime = microtime(true) - $this->runtime;
            $this->memory = memory_get_peak_usage(true) - $this->memory;
            $this->save();
        }

        /**
         * Dump for debugging
         */
        function dump_data() {
            var_dump($this->records);
        }

        /**
         * Saves the values
         */
        private function save() {
            $this->count_averages();

            $this->records['last_runtime'] = $this->runtime > 15 ? 15 : $this->runtime;
            $this->records['last_memory'] = $this->memory;
            ++$this->records['run_count'];

            update_option($this->key, $this->records);
        }

        /**
         * Calculates the final averages before writing to database
         */
        private function count_averages() {
            $this->records['average_runtime'] =
                ($this->records['average_runtime'] * $this->records['run_count'] + $this->runtime) / ($this->records['run_count'] + 1);
            $this->records['average_memory'] =
                ($this->records['average_memory'] * $this->records['run_count'] + $this->memory) / ($this->records['run_count'] + 1);
        }
    }
}
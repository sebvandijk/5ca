<?php
if (!defined('ABSPATH')) die('-1');
/**
 * Class WD_ASP_Globals
 *
 * A container class for the global variables
 *
 * @class         WD_ASP_Globals
 * @version       1.0
 * @package       AjaxSearchPro/Classes/Core
 * @category      Class
 * @author        Ernest Marcinko
 */
class WD_ASP_Globals {

    /**
     * The plugin options and defaults
     *
     * @var array
     */
    public $options;

    /**
     * The plugin options and defaults (shorthand)
     *
     * @var array
     */
    public $o;

    /**
     * Instance of the init class
     *
     * @var WD_ASP_Init
     */
    public $init;

    /**
     * Instance of the instances class
     *
     * @var WD_ASP_Instances
     */
    public $instances;

    /**
     * Instance of the instances class
     *
     * @var WD_ASP_Priority_Groups
     */
    public $priority_groups;


    /**
     * Instance of the updates manager
     *
     * @var asp_updates
     */
    public $updates;

    /**
     * Instance of the database manager
     *
     * @var WD_ASP_DBMan
     */
    public $db;

    /**
     * Instance of the manager
     *
     * @var WD_ASP_Manager
     */
    public $manager;

    /**
     * Instance of the manager
     *
     * @var WD_ASP_FrontFilters
     */
    public $front_filters;

    /**
     * Instance of the manager
     *
     * @var WD_ASP_Instant
     */
    public $instant;

    /**
     * Array of ASP tables
     *
     * @var array
     */
    public $tables;

    /**
     * Holds the correct table prefix for ASP tables
     *
     * @var string
     */
    public $_prefix;

    /**
     * The upload directory for the plugin
     *
     * @var string
     */
    public $upload_dir = "asp_upload";

    /**
     * The upload directory for the BFI thumb library
     *
     * @var string
     */
    public $bfi_dir = "bfi_thumb";

    /**
     * The upload path
     *
     * @var string
     */
    public $upload_path;

    /**
     * The BFI lib upload path
     *
     * @var string
     */
    public $bfi_path;

    /**
     * The upload URL
     *
     * @var string
     */
    public $upload_url;
}
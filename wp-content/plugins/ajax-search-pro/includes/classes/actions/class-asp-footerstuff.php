<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_FooterStuff_Action")) {
    /**
     * Class WD_ASP_FooterStuff_Action
     *
     * Some things to do in the footer
     *
     * @class         WD_ASP_FooterStuff_Action
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Actions
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_FooterStuff_Action extends WD_ASP_Action_Abstract {

        public function handle() {
            // Blur for isotopic
            ?>
            <div class='asp_hidden_data' id="asp_hidden_data" style="display: none !important;">
                <svg style="position:absolute" height="0" width="0">
                    <filter id="aspblur">
                        <feGaussianBlur in="SourceGraphic" stdDeviation="4"/>
                    </filter>
                </svg>
                <svg style="position:absolute" height="0" width="0">
                    <filter id="no_aspblur"></filter>
                </svg>
            </div>
        <?php

        }

        // ------------------------------------------------------------
        //   ---------------- SINGLETON SPECIFIC --------------------
        // ------------------------------------------------------------
        public static function getInstance() {
            if ( ! ( self::$_instance instanceof self ) ) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }
    }
}
<?php
if (!class_exists("wpdreamsCustomPostTypesEditable")) {
    /**
     * Class wpdreamsCustomPostTypesEditable
     *
     * A custom post types selector UI element with editable titles.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2014, Ernest Marcinko
     */
    class wpdreamsCustomPostTypesEditable extends wpdreamsType {
        private $selected, $_t;

        function getType() {
            parent::getType();
            $this->processData();
            $this->types = get_post_types(array(
                "public" => true,
                "_builtin" => false
            ), "objects", "OR");
            $exclude = array("revision", "nav_menu_item", "attachment", 'peepso-post', 'peepso-comment', "acf",
                "oembed_cache", "user_request", "wp_block", "shop_coupon", "avada_page_options",
                "_pods_template", "_pods_pod", "_pods_field", "bp-email",
                "lbmn_archive", "lbmn_footer", "mc4wp-form",
                "elementor-front", "elementor-icon",
                "fusion_template", "fusion_element", "wc_product_tab", "customize_changeset",
                "wpcf7_contact_form", "dslc_templates", "acf-field", "acf-group", "acf-groups", "acf-field-group", "custom_css");
            foreach ($this->types as $k => $v) {
                if (in_array($k, $exclude)) {
                    unset($this->types[$k]);
                    continue;
                }
            }
            echo "
      <div class='wpdreamsCustomPostTypesEditable' id='wpdreamsCustomPostTypesEditable-" . self::$_instancenumber . "'>
        <fieldset>
          <legend>" . $this->label . "</legend>";
            echo '<div class="sortablecontainer" id="sortablecontainer' . self::$_instancenumber . '">
            <p>' . __('Available post types', 'ajax-search-pro') . '</p><ul id="sortable' . self::$_instancenumber . '" class="connectedSortable">';
            if ($this->types != null && is_array($this->types)) {
                foreach ($this->types as $k => $v) {
                    if ($this->selected == null || !wd_in_array_r($v, $this->selected)) {
                        echo '<li class="ui-state-default ui-left" style="background: #ddd;">
              <label>' . $k . '</label>
              <input type="text" value="' . $v->label  . '"/>
              </li>';
                    }
                }
            }
            echo "</ul></div>";
            echo '<div class="sortablecontainer"><p>' . __('Drag here the post types you want to use!', 'ajax-search-pro') . '</p>
                  <ul id="sortable_conn' . self::$_instancenumber . '" class="connectedSortable">';
            if ($this->selected != null && is_array($this->selected)) {
                foreach ($this->selected as $k => $v) {
                    echo '<li class="ui-state-default ui-left" style="background: #ddd;">
            <label>' . esc_html($v[0]) . '</label>
            <input type="text" value="' . esc_attr($v[1]) . '"/>
            </li>';
                }
            }
            echo "</ul></div>";
            echo "
         <input isparam=1 type='hidden' value='" . esc_attr($this->data) . "' name='" . $this->name . "'>";
            echo "
         <input type='hidden' value='wpdreamsCustomPostTypesEditable' name='classname-" . $this->name . "'>";
            echo "
        </fieldset>
      </div>";
        }

        function processData() {
            $this->data = stripslashes(str_replace("\n", "", $this->data));
            if ($this->data != "") {
                $this->_t = explode("|", $this->data);
                foreach ($this->_t as $k => $v) {
                    $this->selected[] = explode(';', $v);
                }
            } else {
                $this->selected = null;
            }
        }

        final function getData() {
            return $this->data;
        }

        final function getSelected() {
            return $this->selected;
        }
    }
}
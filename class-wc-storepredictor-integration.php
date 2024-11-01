<?php

/**
 *
 * @package   Woocommerce storePredictor Integration
 * @category Integration
 * @author   storePredictor.com
 */
if (!class_exists('WC_storePredictor_Integration')) :
    class WC_storePredictor_Integration extends WC_Integration
    {
        /**
         * Init and hook in the integration.
         */
        public function __construct()
        {
            global $woocommerce;
            $this->id = 'storepredictor-integration';
            $this->method_title = __('storePredictor Integration');
            $this->method_description = __('Insert your store code to track data for https://my.storepredictor.com service.');
            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();
            // Define user set variables.
            $this->sp_code = $this->get_option('sp_code');
            // Actions.
            add_action('woocommerce_update_options_integration_' . $this->id, [$this, 'process_admin_options']);
        }

        /**
         * Initialize integration settings form fields.
         */
        public function init_form_fields()
        {
            $this->form_fields = [
                'sp_code' => [
                    'title' => __('storePredictor Store Code'),
                    'type' => 'text',
                    'description' => __('storePredictor code get at https://my.storepredictor.com'),
                    'desc_tip' => true,
                    'default' => '',
                    'css' => 'width:170px;',
                ],
            ];
        }
    }
endif;
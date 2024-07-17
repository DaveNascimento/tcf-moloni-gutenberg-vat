<?php
/*
Plugin Name: WooCommerce Gutenberg Checkout VAT field for Moloni
Description: Adds a Moloni-compatible VAT field to WooCommerce Checkout Gutenberg billing fields block. Validation is done only for Portugese VATs.
Version: 1.0
Author: The Creative Farm
Text Domain: tcf-moloni-gutenberg-vat
*/

// Exit if accessed directly
if (!defined("ABSPATH")) {
    exit();
}

/**
 * Define VAT field constant
 */
if (!function_exists("tcf_mgv_define_vat_field_constant")) {
    function tcf_mgv_define_vat_field_constant()
    {
        if (!defined("TCF_MGV_VAT_FIELD")) {
            define("TCF_MGV_VAT_FIELD", "_billing_vat");
        }
    }
}
add_action("plugins_loaded", "tcf_mgv_define_vat_field_constant", 1);

/**
 * Initialize plugin
 */
if (!function_exists("tcf_mgv_initialize_plugin")) {
    function tcf_mgv_initialize_plugin()
    {
        if (!class_exists("WooCommerce")) {
            add_action("admin_notices", "tcf_mgv_woocommerce_missing_notice");
            return;
        }

        add_action("woocommerce_init", "tcf_mgv_register_vat_field");
        add_action(
            "woocommerce_validate_additional_field",
            "tcf_mgv_validate_vat_field",
            10,
            3
        );
        add_action(
            "woocommerce_set_additional_field_value",
            "tcf_mgv_set_vat_field_value",
            10,
            4
        );
    }
}
add_action("plugins_loaded", "tcf_mgv_initialize_plugin");

/**
 * Display admin notice if WooCommerce is not active
 */
if (!function_exists("tcf_mgv_woocommerce_missing_notice")) {
    function tcf_mgv_woocommerce_missing_notice()
    {
        ?>
        <div class="error">
            <p><?php esc_html_e(
                "WooCommerce Gutenberg Checkout VAT field for Moloni requires WooCommerce to be installed and active.",
                "tcf-moloni-gutenberg-vat"
            ); ?></p>
        </div>
        <?php
    }
}

/**
 * Register VAT field
 */
if (!function_exists("tcf_mgv_register_vat_field")) {
    function tcf_mgv_register_vat_field()
    {
        woocommerce_register_additional_checkout_field([
            "id" => "tcf-moloni-gutenberg-vat/vat-number",
            "label" => __("NIF", "tcf-moloni-gutenberg-vat"),
            "location" => "address",
            "required" => false,
            "attributes" => [
                "autocomplete" => "vat-number",
                "pattern" => "[0-9]{9}",
                "title" => __(
                    "Número Contribuinte",
                    "tcf-moloni-gutenberg-vat"
                ),
            ],
        ]);
    }
}

/**
 * Validate VAT field
 *
 * @param WP_Error $errors    Validation errors.
 * @param string   $field_key Field key.
 * @param string   $field_value Field value.
 * @return WP_Error
 */
if (!function_exists("tcf_mgv_validate_vat_field")) {
    function tcf_mgv_validate_vat_field($errors, $field_key, $field_value)
    {
        if ("tcf-moloni-gutenberg-vat/vat-number" === $field_key) {
            if (!preg_match('/^[0-9]{9}$/', $field_value)) {
                $errors->add(
                    "invalid_vat_number",
                    __(
                        "Por favor introduza um NIF válido.",
                        "tcf-moloni-gutenberg-vat"
                    )
                );
            }
        }
        return $errors;
    }
}

/**
 * Set VAT field value
 *
 * @param string $key       Field key.
 * @param string $value     Field value.
 * @param string $group     Field group.
 * @param object $wc_object WooCommerce object.
 */
if (!function_exists("tcf_mgv_set_vat_field_value")) {
    function tcf_mgv_set_vat_field_value($key, $value, $group, $wc_object)
    {
        if ("tcf-moloni-gutenberg-vat/vat-number" !== $key) {
            return;
        }
        $billing_vat = TCF_MGV_VAT_FIELD;
        $wc_object->update_meta_data($billing_vat, $value, true);
    }
}

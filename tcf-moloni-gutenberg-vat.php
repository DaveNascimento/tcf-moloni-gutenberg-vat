<?php
/*
Plugin Name: WooCommerce Gutenberg Checkout VAT field for Moloni
Description: Adds a Moloni-compatible VAT field to WooCommerce Checkout Gutenberg billing fields block. Validation is done only for Portugese VATs.
Version: 1.0
Author: The Creative Farm
*/

add_action("plugins_loaded", "tcf_define_vat_field_constant", 1);

function tcf_define_vat_field_constant()
{
    if (!defined("VAT_FIELD")) {
        define("VAT_FIELD", "_billing_vat");
    }
}

add_action("woocommerce_init", function () {
    woocommerce_register_additional_checkout_field([
        "id" => "tcf-moloni-gutenberg-vat/vat-number",
        "label" => __("NIF", "tcf-moloni-gutenberg-vat"),
        "location" => "address",
        "required" => false,
        "attributes" => [
            "autocomplete" => "vat-number",
            "pattern" => "[0-9]{9}",
            "title" => __("Número Contribuinte", "tcf-moloni-gutenberg-vat"),
        ],
    ]);

    add_action(
        "woocommerce_validate_additional_field",
        function (WP_Error $errors, $field_key, $field_value) {
            if ("tcf-moloni-gutenberg-vat/vat-number" === $field_key) {
                $match = preg_match('/^[0-9]{9}$/', $field_value);

                if (0 === $match || false === $match) {
                    $errors->add(
                        "invalid_vat_number",
                        __(
                            "Por favor introduza um NIF válido.",
                            "tcf-moloni-gutenberg-vat"
                        )
                    );
                }
            }
            return $error;
        },
        10,
        3
    );
});

add_action(
    "woocommerce_set_additional_field_value",
    function ($key, $value, $group, $wc_object) {
        if ("tcf-moloni-gutenberg-vat/vat-number" !== $key) {
            return;
        }

        if ("billing" === $group) {
            $billing_vat = "_billing_vat";
        } else {
            $billing_vat = "_billing_vat";
        }

        $wc_object->update_meta_data($billing_vat, $value, true);
    },
    10,
    4
);

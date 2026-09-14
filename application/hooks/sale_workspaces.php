<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Ventas simultaneas para el modulo de Ventas.
 *
 * Cada venta abierta utiliza un cart_id distinto de PHPPOSCartSale, por lo
 * que articulos, cliente, descuentos y pagos permanecen separados dentro de
 * la misma sesion del cajero.
 */
function setup_sale_workspaces()
{
    $CI =& get_instance();

    if (strtolower($CI->router->fetch_class()) !== 'sales')
    {
        return;
    }

    if (!class_exists('PHPPOSCartSale') || !isset($CI->cart))
    {
        return;
    }

    $workspaces = $CI->session->userdata('sale_workspaces');
    $counter = (int) $CI->session->userdata('sale_workspace_counter');

    if (!is_array($workspaces) || empty($workspaces))
    {
        $workspaces = array(
            'sale' => array('label' => 'Venta 1')
        );
        $counter = 1;
    }

    // Elimina entradas mal formadas que pudieran quedar de una sesion previa.
    foreach ($workspaces as $workspace_id => $workspace)
    {
        if (!is_string($workspace_id) || !preg_match('/^sale(?:_[a-zA-Z0-9]+)?$/', $workspace_id))
        {
            unset($workspaces[$workspace_id]);
        }
    }

    if (empty($workspaces))
    {
        $workspaces = array(
            'sale' => array('label' => 'Venta 1')
        );
        $counter = 1;
    }

    if ($counter < 1)
    {
        $counter = count($workspaces);
    }

    $current_cart_id = $CI->session->userdata('current_sale_cart_id');

    if (!$current_cart_id || !isset($workspaces[$current_cart_id]))
    {
        reset($workspaces);
        $current_cart_id = key($workspaces);
    }

    $action = $CI->input->get('sale_workspace_action', TRUE);

    if ($action === 'new')
    {
        // Limite de seguridad para evitar crear ventas accidentalmente sin fin.
        if (count($workspaces) < 10)
        {
            $counter++;
            $workspace_id = 'sale_' . substr(md5(uniqid((string) mt_rand(), TRUE)), 0, 12);
            $workspaces[$workspace_id] = array('label' => 'Venta ' . $counter);
            $current_cart_id = $workspace_id;
        }

        $CI->session->set_userdata('sale_workspaces', $workspaces);
        $CI->session->set_userdata('sale_workspace_counter', $counter);
        $CI->session->set_userdata('current_sale_cart_id', $current_cart_id);
        redirect(site_url('sales'));
        exit;
    }

    if ($action === 'switch')
    {
        $requested_cart_id = $CI->input->get('cart_id', TRUE);

        if ($requested_cart_id && isset($workspaces[$requested_cart_id]))
        {
            $current_cart_id = $requested_cart_id;
        }

        $CI->session->set_userdata('sale_workspaces', $workspaces);
        $CI->session->set_userdata('sale_workspace_counter', $counter);
        $CI->session->set_userdata('current_sale_cart_id', $current_cart_id);
        redirect(site_url('sales'));
        exit;
    }

    if ($action === 'close')
    {
        $requested_cart_id = $CI->input->get('cart_id', TRUE);

        if ($requested_cart_id && isset($workspaces[$requested_cart_id]) && count($workspaces) > 1)
        {
            unset($workspaces[$requested_cart_id]);
            $CI->session->unset_userdata($requested_cart_id);

            if ($current_cart_id === $requested_cart_id || !isset($workspaces[$current_cart_id]))
            {
                reset($workspaces);
                $current_cart_id = key($workspaces);
            }
        }

        $CI->session->set_userdata('sale_workspaces', $workspaces);
        $CI->session->set_userdata('sale_workspace_counter', $counter);
        $CI->session->set_userdata('current_sale_cart_id', $current_cart_id);
        redirect(site_url('sales'));
        exit;
    }

    $CI->session->set_userdata('sale_workspaces', $workspaces);
    $CI->session->set_userdata('sale_workspace_counter', $counter);
    $CI->session->set_userdata('current_sale_cart_id', $current_cart_id);

    // Sustituye el carrito unico creado por Sales::__construct por el carrito
    // correspondiente a la venta activa.
    $CI->cart = PHPPOSCartSale::get_instance($current_cart_id);

    if (function_exists('cache_item_and_item_kit_cart_info'))
    {
        cache_item_and_item_kit_cart_info($CI->cart->get_items());
    }

    // Disponible para cualquier vista que quiera utilizar estos datos.
    $CI->view_data['sale_workspaces'] = $workspaces;
    $CI->view_data['current_sale_cart_id'] = $current_cart_id;
}

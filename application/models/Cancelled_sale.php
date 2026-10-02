<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cancelled_sale extends MY_Model
{
    public function is_ready()
    {
        if (!$this->db->table_exists('cancelled_sales')) return FALSE;
        foreach (array('cancelled_sale_id', 'employee_id', 'sold_by_employee_id', 'location_id',
            'register_id', 'customer_id', 'reason', 'comment', 'subtotal', 'total',
            'ticket_data', 'cancelled_at') as $field)
        {
            if (!$this->db->field_exists($field, 'cancelled_sales')) return FALSE;
        }
        return TRUE;
    }

    public function record($cart, $reason)
    {
        $ticket = array('mode' => $cart->get_mode(), 'items' => array(), 'payments' => array());
        foreach ($cart->get_items() as $line => $item)
        {
            $ticket['items'][] = array(
                'line' => $line,
                'item_id' => isset($item->item_id) ? $item->item_id : NULL,
                'item_kit_id' => isset($item->item_kit_id) ? $item->item_kit_id : NULL,
                'variation_id' => isset($item->variation_id) ? $item->variation_id : NULL,
                'name' => $item->name, 'quantity' => $item->quantity,
                'unit_price' => $item->unit_price, 'discount' => $item->discount,
                'description' => $item->description
            );
        }
        foreach ($cart->get_payments() as $payment)
        {
            // Store amounts and methods only, never processor credentials or card data.
            $ticket['payments'][] = array('payment_type' => $payment->payment_type,
                'payment_amount' => $payment->payment_amount);
        }
        $ticket_json = json_encode($ticket, JSON_UNESCAPED_UNICODE);
        if ($ticket_json === FALSE) return FALSE;
        $data = array(
            'employee_id' => $this->Employee->get_logged_in_employee_info()->person_id,
            'sold_by_employee_id' => $cart->sold_by_employee_id ?: NULL,
            'location_id' => $this->Employee->get_logged_in_employee_current_location_id(),
            'register_id' => $this->Employee->get_logged_in_employee_current_register_id() ?: NULL,
            'customer_id' => $cart->customer_id ?: NULL, 'reason' => $reason,
            'comment' => $cart->comment, 'subtotal' => $cart->get_subtotal(),
            'total' => $cart->get_total(), 'ticket_data' => $ticket_json,
            'cancelled_at' => date('Y-m-d H:i:s')
        );
        $debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;
        $saved = $this->db->insert('cancelled_sales', $data);
        $this->db->db_debug = $debug;
        if (!$saved) log_message('error', 'Could not persist active sale cancellation audit.');
        return $saved;
    }

    private function filter_period($location_id, $start, $end)
    {
        $this->db->where('c.location_id', $location_id);
        $this->db->where('c.cancelled_at >=', $start);
        $this->db->where('c.cancelled_at <', $end);
    }

    public function get_audit($location_id, $start, $end, $offset)
    {
        $this->db->select('COUNT(*) AS operations, COALESCE(SUM(c.total), 0) AS total', FALSE);
        $this->db->from($this->db->dbprefix('cancelled_sales').' AS c');
        $this->filter_period($location_id, $start, $end);
        $summary = $this->db->get()->row_array();

        $this->db->select('c.*, p.first_name, p.last_name, r.name AS register_name');
        $this->db->from($this->db->dbprefix('cancelled_sales').' AS c');
        $this->db->join($this->db->dbprefix('people').' AS p', 'p.person_id = c.employee_id', 'left');
        $this->db->join($this->db->dbprefix('registers').' AS r', 'r.register_id = c.register_id', 'left');
        $this->filter_period($location_id, $start, $end);
        $this->db->order_by('c.cancelled_at', 'DESC');
        $this->db->order_by('c.cancelled_sale_id', 'DESC');
        $this->db->limit(100, $offset);
        return array('summary' => $summary, 'rows' => $this->db->get()->result_array());
    }
}

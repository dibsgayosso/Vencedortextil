<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_cancelled_sales_audit extends MY_Migration
{
    public function up()
    {
        $table = $this->db->dbprefix('cancelled_sales');
        $sql = "CREATE TABLE IF NOT EXISTS `".$table."` (
            `cancelled_sale_id` bigint(20) NOT NULL AUTO_INCREMENT,
            `employee_id` int(11) NOT NULL,
            `sold_by_employee_id` int(11) DEFAULT NULL,
            `location_id` int(11) NOT NULL,
            `register_id` int(11) DEFAULT NULL,
            `customer_id` int(11) DEFAULT NULL,
            `reason` text NOT NULL,
            `comment` text DEFAULT NULL,
            `subtotal` decimal(23,10) NOT NULL DEFAULT 0,
            `total` decimal(23,10) NOT NULL DEFAULT 0,
            `ticket_data` longtext NOT NULL,
            `cancelled_at` datetime NOT NULL,
            PRIMARY KEY (`cancelled_sale_id`),
            KEY `cancelled_sales_employee_id` (`employee_id`),
            KEY `cancelled_sales_location_id` (`location_id`),
            KEY `cancelled_sales_cancelled_at` (`cancelled_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        if (!$this->db->query($sql))
        {
            show_error('No se pudo crear la tabla de auditoría de cancelaciones.', 500);
            exit;
        }
        foreach (array('cancelled_sale_id', 'employee_id', 'sold_by_employee_id', 'location_id',
            'register_id', 'customer_id', 'reason', 'comment', 'subtotal', 'total',
            'ticket_data', 'cancelled_at') as $field)
        {
            if (!$this->db->field_exists($field, 'cancelled_sales'))
            {
                show_error('La tabla de cancelaciones está incompleta: falta '.$field.'.', 500);
                exit;
            }
        }
    }

    public function down()
    {
        // Audit history is intentionally retained on rollback.
    }
}

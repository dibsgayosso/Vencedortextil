<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Multi_owner_dashboard extends CI_Model {
    public function snapshot() {
        $sources = array(
            array('name'=>'Base principal','db'=>$this->db),
        );
        $config_file = APPPATH.'config/owner_databases.local.php';
        if (is_file($config_file)) {
            $extra = require $config_file;
            if (is_array($extra)) foreach ($extra as $name=>$settings) {
                if (!is_array($settings) || empty($settings['database']) || empty($settings['username']) || !isset($settings['password'])) continue;
                $settings = array_merge(array('hostname'=>'127.0.0.1','dbdriver'=>'mysqli','dbprefix'=>'phppos_','pconnect'=>FALSE,'db_debug'=>FALSE,'char_set'=>'utf8','dbcollat'=>'utf8_general_ci','save_queries'=>FALSE),$settings);
                $sources[] = array('name'=>(string)$name,'config'=>$settings);
            }
        }
        $rows=array(); $totals=array('sales'=>0,'operations'=>0,'expenses'=>0,'credit'=>0);
        foreach ($sources as $source) {
            $row=array('name'=>$source['name'],'sales'=>0,'operations'=>0,'expenses'=>0,'credit'=>0,'last_sale'=>NULL,'error'=>NULL);
            $db=NULL;
            try {
                $db=isset($source['db']) ? $source['db'] : $this->load->database($source['config'],TRUE);
                if (!$db || !$db->conn_id) throw new Exception('No fue posible conectar');
                $prefix=$db->dbprefix;
                $start=date('Y-m-d 00:00:00'); $end=date('Y-m-d 00:00:00',strtotime('+1 day'));
                $q=$db->query("SELECT COUNT(*) operations, COALESCE(SUM(total),0) sales, MAX(sale_time) last_sale FROM ".$db->protect_identifiers('sales',TRUE)." WHERE deleted=0 AND suspended=0 AND store_account_payment=0 AND sale_time >= ? AND sale_time < ?",array($start,$end));
                if (!$q) throw new Exception('Consulta de ventas no disponible');
                $v=$q->row_array();$row['sales']=(float)$v['sales'];$row['operations']=(int)$v['operations'];$row['last_sale']=$v['last_sale'];
                $q=$db->query("SELECT COALESCE(SUM(expense_amount),0) amount FROM ".$db->protect_identifiers('expenses',TRUE)." WHERE expense_date >= ? AND expense_date < ? AND deleted=0",array($start,$end));
                if ($q) $row['expenses']=(float)$q->row()->amount;
                foreach ($totals as $key=>$_) $totals[$key]+=$row[$key];
            } catch (Throwable $e) {
                log_message('error','Multi owner '.$source['name'].': '.$e->getMessage());
                $row['error']='Conexión o consulta no disponible';
            }
            if (!isset($source['db']) && $db) $db->close();
            $rows[]=$row;
        }
        return array('sources'=>$rows,'totals'=>$totals,'updated_at'=>time());
    }
}

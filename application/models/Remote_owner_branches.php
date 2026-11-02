<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Remote_owner_branches extends CI_Model {
    private function rows($db,$sql,$args=array()) {
        $q=$db->query($sql,$args);
        if (!$q) throw new Exception('Consulta de indicadores no disponible');
        return $q->result_array();
    }
    public function snapshot() {
        $file=APPPATH.'config/owner_databases.local.php';
        if (!is_file($file)) return array('branches'=>array(),'errors'=>array());
        $configs=require $file;
        if (!is_array($configs)) return array('branches'=>array(),'errors'=>array('Configuración de bases inválida'));
        $branches=array();$errors=array();$number=0;
        foreach ($configs as $name=>$settings) {
            $number++;
            $db=NULL;
            try {
                if (!is_array($settings) || empty($settings['database']) || empty($settings['username']) || !isset($settings['password'])) throw new Exception('Configuración incompleta');
                $settings=array_merge(array('hostname'=>'127.0.0.1','dbdriver'=>'mysqli','dbprefix'=>'phppos_','pconnect'=>FALSE,'db_debug'=>FALSE,'char_set'=>'utf8','dbcollat'=>'utf8_general_ci','save_queries'=>FALSE),$settings);
                $settings['db_debug']=FALSE;$settings['pconnect']=FALSE;
                $db=$this->load->database($settings,TRUE);
                if (!$db || !$db->conn_id) throw new Exception('Conexión no disponible');
                $p=$db->dbprefix;
                $locations=$this->rows($db,"SELECT location_id,name,timezone FROM ".$p."locations WHERE deleted=0");
                foreach ($locations as $location) {
                    $id=(int)$location['location_id'];
                    $zone=!empty($location['timezone']) && in_array($location['timezone'],timezone_identifiers_list(),TRUE)?$location['timezone']:date_default_timezone_get();
                    $now=new DateTimeImmutable('now',new DateTimeZone($zone));
                    $start=$now->format('Y-m-d 00:00:00');$end=$now->modify('+1 day')->format('Y-m-d 00:00:00');
                    $b=Owner_dashboard::empty_totals();
                    $b=array_merge($b,array('id'=>-($number*100000+$id),'name'=>(string)$name.' · '.$location['name'],'day'=>$now->format('d/m/Y'),'timezone'=>$zone,'payments'=>array(),'expenses'=>array(),'pending_requests'=>array(),'can_authorize'=>FALSE,'can_view_profit'=>FALSE,'profit_available'=>FALSE,'cash_out_available'=>FALSE,'can_view_difference'=>FALSE,'last_close'=>FALSE,'last_sale'=>NULL,'remote'=>TRUE));
                    $s=$this->rows($db,"SELECT COUNT(*) AS cnt,COALESCE(SUM(total),0) AS amount,UNIX_TIMESTAMP(MAX(sale_time)) AS last_sale FROM ".$p."sales WHERE location_id=? AND deleted=0 AND suspended=0 AND store_account_payment=0 AND sale_time>=? AND sale_time<?",array($id,$start,$end));
                    $b['sales_count']=(int)$s[0]['cnt'];$b['sales_total']=(float)$s[0]['amount'];
                    $last=$this->rows($db,"SELECT UNIX_TIMESTAMP(MAX(sale_time)) AS last_sale FROM ".$p."sales WHERE location_id=? AND deleted=0 AND suspended=0 AND store_account_payment=0 AND total>=0",array($id));
                    $b['last_sale']=$last[0]['last_sale']===NULL?NULL:(int)$last[0]['last_sale'];
                    $r=$this->rows($db,"SELECT COUNT(*) cnt,COALESCE(SUM(total),0) amount FROM ".$p."receivings WHERE location_id=? AND deleted=0 AND suspended=0 AND store_account_payment=0 AND total_quantity_received>0 AND receiving_time>=? AND receiving_time<?",array($id,$start,$end));
                    $b['receiving_count']=(int)$r[0]['cnt'];$b['receiving_total']=(float)$r[0]['amount'];
                    $expenses=$this->rows($db,"SELECT COALESCE(ec.name,'Sin categoría') category,COUNT(*) operations,COALESCE(SUM(e.expense_amount+e.expense_tax),0) amount FROM ".$p."expenses e LEFT JOIN ".$p."expenses_categories ec ON ec.id=e.category_id WHERE e.location_id=? AND e.deleted=0 AND e.expense_date>=? AND e.expense_date<? GROUP BY e.category_id,ec.name",array($id,$start,$end));
                    $b['expenses']=$expenses;
                    foreach ($expenses as $e) {$b['expenses_count']+=(int)$e['operations'];$b['expenses_total']+=(float)$e['amount'];}
                    $c=$this->rows($db,"SELECT COALESCE(SUM(CASE WHEN balance>0 THEN balance ELSE 0 END),0) debt,COALESCE(SUM(CASE WHEN balance<0 THEN -balance ELSE 0 END),0) in_favor,COALESCE(SUM(CASE WHEN balance>0 THEN 1 ELSE 0 END),0) customers FROM ".$p."customers WHERE location_id=? AND deleted=0",array($id));
                    $b['credit_balance']=(float)$c[0]['debt'];$b['credit_in_favor']=(float)$c[0]['in_favor'];$b['credit_customers']=(int)$c[0]['customers'];
                    $profit=$this->rows($db,"SELECT COALESCE(SUM(profit),0) amount FROM ".$p."sales WHERE location_id=? AND deleted=0 AND suspended=0 AND store_account_payment=0 AND sale_time>=? AND sale_time<?",array($id,$start,$end));
                    $b['profit_total']=(float)$profit[0]['amount'];
                    $b['can_view_profit']=TRUE;$b['profit_available']=TRUE;
                    $cash=$this->rows($db,"SELECT COUNT(*) operations,COALESCE(SUM(-a.amount),0) amount FROM ".$p."register_log_audit a JOIN ".$p."register_log l ON l.register_log_id=a.register_log_id JOIN ".$p."registers r ON r.register_id=l.register_id WHERE r.location_id=? AND l.deleted=0 AND a.payment_type='common_cash' AND a.amount<0 AND a.date>=? AND a.date<?",array($id,$start,$end));
                    $b['cash_out_total']=(float)$cash[0]['amount'];$b['cash_out_count']=(int)$cash[0]['operations'];$b['cash_out_available']=TRUE;
                    $payments=$this->rows($db,"SELECT sp.payment_type label,COUNT(DISTINCT sp.sale_id) operations,COALESCE(SUM(sp.payment_amount),0) total FROM ".$p."sales_payments sp INNER JOIN ".$p."sales s ON s.sale_id=sp.sale_id WHERE s.location_id=? AND s.deleted=0 AND s.suspended=0 AND s.store_account_payment=0 AND s.sale_time>=? AND s.sale_time<? GROUP BY sp.payment_type",array($id,$start,$end));
                    foreach ($payments as $payment) $b['payments'][]=array('label'=>$payment['label'],'operations'=>(int)$payment['operations'],'total'=>(float)$payment['total']);
                    // Mirror Register::get_last_closed_register_summary on the external connection.
                    $closed=$this->rows($db,"SELECT l.register_log_id,l.shift_end,l.employee_id_close,r.name register_name FROM ".$p."register_log l JOIN ".$p."registers r ON r.register_id=l.register_id WHERE r.location_id=? AND l.deleted=0 AND l.shift_end IS NOT NULL AND l.shift_end<>'0000-00-00 00:00:00' ORDER BY l.shift_end DESC,l.register_log_id DESC LIMIT 1",array($id));
                    $b['can_view_difference']=TRUE;
                    if ($closed) {
                        $close=$closed[0];
                        $differences=$this->rows($db,"SELECT COALESCE(SUM(rp.close_amount-rp.open_amount-rp.payment_sales_amount-rp.total_payment_additions+rp.total_payment_subtractions),0) difference FROM ".$p."register_log_payments rp JOIN ".$p."register_log l ON l.register_log_id=rp.register_log_id JOIN ".$p."people po ON po.person_id=l.employee_id_open WHERE rp.register_log_id=?",array($close['register_log_id']));
                        $close['difference']=(float)$differences[0]['difference'];
                        $close['employee_name']='';
                        if (!empty($close['employee_id_close'])) {
                            $employee=$this->rows($db,"SELECT first_name,last_name FROM ".$p."people WHERE person_id=? LIMIT 1",array($close['employee_id_close']));
                            if ($employee) $close['employee_name']=trim($employee[0]['first_name'].' '.$employee[0]['last_name']);
                        }
                        $b['last_close']=$close;
                    }
                    $branches[]=$b;
                }
            } catch (Throwable $e) {
                log_message('error','Remote owner branch '.$name.': '.$e->getMessage());
                $errors[]=(string)$name.' no disponible; sus datos no están incluidos.';
            }
            if ($db) $db->close();
        }
        return array('branches'=>$branches,'errors'=>$errors);
    }
}

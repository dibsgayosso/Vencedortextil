<?php
define('BASEPATH',__DIR__);class CI_Model {}
function html_escape($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}function to_currency($n){return '$'.number_format($n,2);}function site_url($p){return '/index.php/'.$p;}
require dirname(__DIR__).'/application/models/Owner_dashboard.php';
class OwnerFixture extends Owner_dashboard {
 protected function branch($id,$day,$person){return array_merge(self::empty_totals(),array('last_sale'=>time()-120,'can_view_profit'=>TRUE,'profit_total'=>$id===1?70:-20,'cash_out_total'=>$id===1?30:60,'cash_out_count'=>1,'receiving_count'=>2,'receiving_total'=>100,'credit_balance'=>500,'credit_in_favor'=>40,'credit_customers'=>2,'expenses_total'=>15,'expenses_count'=>1,'expenses'=>array(array('category'=>'Luz <script>','operations'=>1,'amount'=>15)),'pending_requests'=>array(),'can_authorize'=>FALSE,'can_view_difference'=>$id===1,'last_close'=>array('difference'=>-30,'shift_end'=>'2026-10-09 20:00:00','employee_name'=>'Empleado','register_name'=>'Caja')));}
}
$m=new OwnerFixture();$m->Employee=new class {
 public $enabled=TRUE;function has_module_permission($module,$person){return $this->enabled;}
 function get_authenticated_location_ids($person){return array(1,2,3);}
 function has_module_action_permission($module,$action,$person,$id){return $action==='view_owner_dashboard' && $id<3;}
};
$m->Location=new class{function get_info($id){return (object)array('location_id'=>$id,'deleted'=>0,'name'=>'Sucursal <script>'.$id,'timezone'=>$id===1?'America/Mexico_City':'America/Tijuana');}};
$original=date_default_timezone_get();$seen=array();
$owner_dashboard=$m->snapshot(10,function($id)use(&$seen){$seen[]=array($id,date_default_timezone_get());return array('sales_total'=>$id===1?200:-50,'sales_count'=>1,'rows'=>array(array('label'=>'Transferencia','operations'=>1,'total'=>$id===1?200:-50)));});
if($seen!==array(array(1,'America/Mexico_City'),array(2,'America/Tijuana')) || date_default_timezone_get()!==$original || $owner_dashboard['totals']['sales_total']!==150 || $owner_dashboard['totals']['credit_balance']!==1000 || $owner_dashboard['totals']['profit_total']!==50 || $owner_dashboard['totals']['cash_out_total']!==90 || $owner_dashboard['totals']['cash_out_count']!==2)throw new Exception('Owner rollup or branch permissions failed');
ob_start();require dirname(__DIR__).'/application/views/owner_panel.php';$html=ob_get_clean();
if(strpos($html,'<script>')!==FALSE || substr_count($html,'class="ow-branch"')!==2 || strpos($html,'Faltante')===FALSE || strpos($html,'Sin permiso para ver diferencias')===FALSE)throw new Exception('Owner rendering or cut permission failed');
if(strpos($html,'Ganancias en tiempo real')===FALSE || strpos($html,'Salidas de efectivo')===FALSE || strpos($html,'Créditos por cobrar')!==FALSE)throw new Exception('Requested KPI replacement failed');
$m->Employee->enabled=FALSE;if($m->snapshot(10,function(){throw new Exception('Unauthorized financial query');})['branches'])throw new Exception('Unauthorized access');
echo "Owner permissions, consolidated totals, branch timezones and rendering checks passed\n";

// Exercise the real branch loader rather than only the snapshot fixture.
class OwnerQueryFixture extends Owner_dashboard {
 public function load_branch($id,$day){return $this->branch($id,$day,10);}
}
class OwnerQueryDb {
 public $queries=array(); private $query=array();
 function dbprefix($table){return 'tenant_'.$table;}
 function select($sql,$escape=TRUE){$this->query['select']=$sql;return $this;}
 function from($table){$this->query['table']=$table;return $this;}
 function join($table,$on,$kind='inner'){$this->query['joins'][]=array($table,$on);return $this;}
 function where($key,$value){$this->query['where'][$key]=$value;return $this;}
 function group_by($key){return $this;}
 function get(){
  $q=$this->query;$this->queries[]=$q;$this->query=array();
  if($q['table']==='sales')$row=strpos($q['select'],'profit')!==FALSE?array('amount'=>-12.5):array('last_sale'=>NULL);
  elseif($q['table']==='register_log_audit')$row=array('amount'=>125,'operations'=>2);
  elseif($q['table']==='customers')$row=array('debt'=>0,'in_favor'=>0,'customers'=>0);
  else $row=array('amount'=>0,'operations'=>0);
  return new class($row){private $row;function __construct($row){$this->row=$row;}function row_array(){return $this->row;}function result_array(){return array();}};
 }
}
$q=new OwnerQueryFixture;$q->db=new OwnerQueryDb;
$q->Employee=new class {public $profit=TRUE;function has_module_action_permission($module,$action,$person,$id){return $action==='show_profit' && $this->profit;}};
$day=array('2026-10-11 00:00:00','2026-10-12 00:00:00');
$branch=$q->load_branch(7,$day);
if($branch['profit_total']!==-12.5 || $branch['cash_out_total']!==125.0 || $branch['cash_out_count']!==2)throw new Exception('Signed profit or withdrawal aggregation failed');
foreach($q->db->queries as $query){
 if($query['table']==='register_log_audit'){
  $w=$query['where'];
  if($w['registers.location_id']!==7 || $w['register_log_audit.payment_type']!=='common_cash' || $w['register_log_audit.amount <']!==0 || $w['register_log_audit.date >=']!==$day[0] || $w['register_log_audit.date <']!==$day[1] || strpos($query['select'],'tenant_register_log_audit.amount')===FALSE)throw new Exception('Cash withdrawals lack location, date, sign, currency or table prefix filters');
 }
 if($query['table']==='sales' && strpos($query['select'],'profit')!==FALSE){
  $w=$query['where'];
  if($w['location_id']!==7 || $w['deleted']!==0 || $w['suspended']!==0 || $w['store_account_payment']!==0 || $w['sale_time >=']!==$day[0] || $w['sale_time <']!==$day[1])throw new Exception('Profit includes invalid sales or dates');
 }
}
$q->Employee->profit=FALSE;$q->db->queries=array();$hidden=$q->load_branch(7,$day);
foreach($q->db->queries as $query)if($query['table']==='sales' && strpos($query['select'],'profit')!==FALSE)throw new Exception('Profit queried without permission');
if($hidden['profit_total']!==0 || $hidden['can_view_profit'])throw new Exception('Unauthorized profit returned');
$owner_dashboard['branches'][0]['can_view_profit']=FALSE;
$owner_dashboard['can_view_profit']=Owner_dashboard::rollup($owner_dashboard['branches'])['can_view_profit'];
ob_start();require dirname(__DIR__).'/application/views/owner_panel.php';$hidden_html=ob_get_clean();
if(strpos($hidden_html,'Sin permiso</strong>')===FALSE)throw new Exception('Partial branch profit must not be presented as the consolidated profit');
echo "Profit permissions, signed returns, daily cash withdrawals and SQL scope checks passed\n";


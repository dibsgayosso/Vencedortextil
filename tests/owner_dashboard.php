<?php
define('BASEPATH',__DIR__);class CI_Model {}
function html_escape($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}function to_currency($n){return '$'.number_format($n,2);}function site_url($p){return '/index.php/'.$p;}
require dirname(__DIR__).'/application/models/Owner_dashboard.php';
class OwnerFixture extends Owner_dashboard {
 protected function branch($id,$day,$person){return array_merge(self::empty_totals(),array('last_sale'=>time()-120,'receiving_count'=>2,'receiving_total'=>100,'credit_balance'=>500,'credit_in_favor'=>40,'credit_customers'=>2,'expenses_total'=>15,'expenses_count'=>1,'expenses'=>array(array('category'=>'Luz <script>','operations'=>1,'amount'=>15)),'pending_requests'=>array(),'can_authorize'=>FALSE,'can_view_difference'=>$id===1,'last_close'=>array('difference'=>-30,'shift_end'=>'2026-10-09 20:00:00','employee_name'=>'Empleado','register_name'=>'Caja')));}
}
$m=new OwnerFixture();$m->Employee=new class {
 public $enabled=TRUE;function has_module_permission($module,$person){return $this->enabled;}
 function get_authenticated_location_ids($person){return array(1,2,3);}
 function has_module_action_permission($module,$action,$person,$id){return $action==='view_owner_dashboard' && $id<3;}
};
$m->Location=new class{function get_info($id){return (object)array('location_id'=>$id,'deleted'=>0,'name'=>'Sucursal <script>'.$id,'timezone'=>$id===1?'America/Mexico_City':'America/Tijuana');}};
$original=date_default_timezone_get();$seen=array();
$owner_dashboard=$m->snapshot(10,function($id)use(&$seen){$seen[]=array($id,date_default_timezone_get());return array('sales_total'=>$id===1?200:-50,'sales_count'=>1,'rows'=>array(array('label'=>'Transferencia','operations'=>1,'total'=>$id===1?200:-50)));});
if($seen!==array(array(1,'America/Mexico_City'),array(2,'America/Tijuana')) || date_default_timezone_get()!==$original || $owner_dashboard['totals']['sales_total']!==150 || $owner_dashboard['totals']['credit_balance']!==1000)throw new Exception('Owner rollup or branch permissions failed');
ob_start();require dirname(__DIR__).'/application/views/owner_panel.php';$html=ob_get_clean();
if(strpos($html,'<script>')!==FALSE || substr_count($html,'class="ow-branch"')!==2 || strpos($html,'Faltante')===FALSE || strpos($html,'Sin permiso para ver diferencias')===FALSE)throw new Exception('Owner rendering or cut permission failed');
$m->Employee->enabled=FALSE;if($m->snapshot(10,function(){throw new Exception('Unauthorized financial query');})['branches'])throw new Exception('Unauthorized access');
echo "Owner permissions, consolidated totals, branch timezones and rendering checks passed\n";

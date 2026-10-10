<?php
define('BASEPATH',__DIR__);class CI_Model {}
function site_url($path){return '/index.php/'.$path;}function to_currency($n){return '$'.number_format($n,2);}function html_escape($s){return htmlspecialchars($s,ENT_QUOTES,'UTF-8');}
require dirname(__DIR__).'/application/models/Supervisor_dashboard.php';
class Fixture extends Supervisor_dashboard {
 public $seen=array();
 function activity($id){$this->seen[]=date_default_timezone_get();return array('last_sale'=>date('Y-m-d H:i:s',time()-300),'expenses'=>array('amount'=>10,'tax'=>2,'operations'=>1));}
}
$m=new Fixture();
$m->Employee=new class {
 public $enabled=TRUE;
 function has_module_permission($module,$person){return $this->enabled;}
 function get_authenticated_location_ids($person){return array(1,2,3);}
 function has_module_action_permission($module,$action,$person,$id){return $action==='view_supervisor_dashboard' && $id<3;}
};
$m->Location=new class {function get_info($id){return (object)array('location_id'=>$id,'deleted'=>0,'name'=>'Sucursal <script>'.$id,'timezone'=>$id===1?'America/Mexico_City':'America/Tijuana');}};
$original=date_default_timezone_get();$loaded=array();
$dashboard=$m->snapshot(10,function($id)use(&$loaded){$loaded[]=$id;return array('rows'=>array(array('label'=>'Transferencia','total'=>300,'operations'=>2),array('label'=>'Efectivo','total'=>1000,'operations'=>9)));});
if($loaded!==array(1,2) || $dashboard['branches'][0]['payments']['total']!==300.0 || $dashboard['branches'][0]['can_authorize']!==FALSE || $dashboard['ready']!==FALSE)throw new Exception('Permission or transfer filtering failed');
if(date_default_timezone_get()!==$original || $m->seen!==array('America/Mexico_City','America/Tijuana'))throw new Exception('Branch timezones failed');
$supervisor_dashboard=$dashboard;ob_start();require dirname(__DIR__).'/application/views/supervisor_panel.php';$html=ob_get_clean();
if(strpos($html,'<script>')!==FALSE || strpos($html,'Efectivo')!==FALSE || strpos($html,'home/supervisor_transfers/1')===FALSE || strpos($html,'todavía no están habilitadas')===FALSE)throw new Exception('Unsafe rendering or missing links');
$m->Employee->enabled=FALSE;$loaded=array();if($m->snapshot(10,function(){throw new Exception('Unauthorized loading');})['branches'])throw new Exception('Unauthorized access');
foreach(array('spanish','english')as $language){ob_start();require dirname(__DIR__).'/application/language/'.$language.'/common_lang.php';if(ob_get_clean()!=='' || !isset($lang['common_view_supervisor_dashboard']))throw new Exception('Permission translation failed');}
echo "Supervisor permissions, transfer filtering, branch timezones, rendering and language tests passed\n";

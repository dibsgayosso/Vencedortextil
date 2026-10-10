<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Migration_owner_dashboard extends MY_Migration {
 public function up() {
  $table=$this->db->dbprefix('modules_actions');
  foreach(array(array('view_owner_dashboard','common_view_owner_dashboard',260),array('view_register_difference','common_view_register_difference',259)) as $permission)
   if ($this->db->query("INSERT IGNORE INTO `$table` (module_id,action_id,action_name_key,sort) VALUES ('reports',?,?,?)",$permission)===FALSE) throw new RuntimeException('No se pudo crear el permiso del panel del propietario.');
 }
 public function down() {}
}

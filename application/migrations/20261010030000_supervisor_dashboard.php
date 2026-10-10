<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Migration_supervisor_dashboard extends MY_Migration {
 public function up() {
 $table=$this->db->dbprefix('modules_actions');
 if ($this->db->query("INSERT IGNORE INTO `$table` (module_id,action_id,action_name_key,sort) VALUES ('reports','view_supervisor_dashboard','common_view_supervisor_dashboard',261)")===FALSE) throw new RuntimeException('No se pudo crear el permiso del panel del supervisor.');
 }
 public function down() {}
}

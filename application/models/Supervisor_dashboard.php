<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Supervisor_dashboard extends CI_Model
{
    public function allowed_locations($person)
    {
        if (!$this->Employee->has_module_permission('reports',$person)) return array();
        $allowed=array();
        foreach ($this->Employee->get_authenticated_location_ids($person) as $location) {
            if ($this->Employee->has_module_action_permission('reports','view_supervisor_dashboard',$person,$location)) $allowed[]=(int)$location;
        }
        return array_values(array_unique($allowed));
    }
    public static function transfer_payments($payments)
    {
        $result=array('rows'=>array(),'total'=>0,'operations'=>0);
        foreach ($payments['rows'] as $row) {
            // Preserve configured labels; only clearly identified transfer methods qualify.
            if (!preg_match('/^(transferencia(?:s)?\b|transfer\b|bank transfer\b|spei\b)/iu',trim($row['label']))) continue;
            $result['rows'][]=$row;
            $result['total']+=(float)$row['total'];
            $result['operations']+=(int)$row['operations'];
        }
        return $result;
    }
    public static function transfer_sales($sales,$payments)
    {
        $report=array('rows'=>array(),'total'=>0,'operations'=>0,'tickets'=>0);
        foreach ($sales as $sale) {
            $summary=array();
            foreach (isset($payments[$sale['sale_id']]) ? $payments[$sale['sale_id']] : array() as $payment) {
                if ((float)$payment['payment_amount']==0) continue;
                $label=$payment['payment_type'];
                if (!preg_match('/^(transferencia(?:s)?\\b|transfer\\b|bank transfer\\b|spei\\b)/iu',trim($label))) continue;
                if (!isset($summary[$label])) $summary[$label]=0;
                $summary[$label]+=(float)$payment['payment_amount'];
            }
            if ($summary) $report['tickets']++;
            foreach ($summary as $label=>$amount) {
                $report['rows'][]=array('sale_id'=>$sale['sale_id'],'sale_time'=>$sale['sale_time'],'method'=>$label,'amount'=>$amount);
                $report['total']+=$amount; $report['operations']++;
            }
        }
        return $report;
    }
    public function snapshot($person,$payment_loader)
    {
        $branches=array(); $zone=date_default_timezone_get();
        try {
            foreach ($this->allowed_locations($person) as $id) {
                $location=$this->Location->get_info($id);
                if (!$location || !$location->location_id || $location->deleted) continue;
                $timezone=$location->timezone ?: $zone;
                if (!in_array($timezone,timezone_identifiers_list(),TRUE)) $timezone=$zone;
                date_default_timezone_set($timezone);
                $activity=$this->activity($id);
                $activity['last_sale_at']=$activity['last_sale'] ? strtotime($activity['last_sale']) : NULL;
                $branches[]=array('id'=>$id,'name'=>$location->name,'day'=>date('d/m/Y'),
                    'activity'=>$activity,'pending'=>$this->pending($id),
                    'can_authorize'=>FALSE,
                    'payments'=>self::transfer_payments(call_user_func($payment_loader,$id)));
            }
        } finally { date_default_timezone_set($zone); }
        return array('branches'=>$branches,'ready'=>$this->ready());
    }
    public function ready() { return FALSE; }
    public function pending($location) { return array(); }
    public function activity($location)
    {
        $last = $this->db->select_max('sale_time','last_sale')->from('sales')->where('location_id',$location)
            ->where('deleted',0)->where('suspended',0)->where('store_account_payment',0)->where('total >=',0)->get()->row_array();
        $expenses = $this->db->select('COUNT(*) AS operations, COALESCE(SUM(expense_amount),0) AS amount, COALESCE(SUM(expense_tax),0) AS tax',FALSE)
            ->from('expenses')->where('location_id',$location)->where('deleted',0)
            ->where('expense_date >=',date('Y-m-d 00:00:00'))->where('expense_date <',date('Y-m-d 00:00:00',strtotime('+1 day')))->get()->row_array();
        return array('last_sale'=>$last['last_sale'],'expenses'=>$expenses);
    }
}

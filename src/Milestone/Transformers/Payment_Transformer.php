<?php

namespace WeDevs\PM\Milestone\Transformers;

use League\Fractal\TransformerAbstract;
use WeDevs\PM\Common\Traits\Resource_Editors;


class Payment_Transformer extends TransformerAbstract {
    use Resource_Editors;

    public function transform(  $item ) {
        return [
            'id'         => $item->id,
            'order_no' => $this->get_order_id($item, $item->id),
            'status'    => $this->get_order_status($item),
            'purchase_date' => $this->get_purchase_date($item),
            'date' => $this->get_date($item),
            'hour' => $this->get_order_purchased_hour($item),
            'rate' => $this->get_order_rate($item),
            'amount' => $this->get_order_amount($item),
            'ref_id' => $this->get_order_refID($item),
            'particulars' => $this->get_order_particulars($item),
            'status_name' => $this->get_order_status_name($item),
            'order_general_view' => $this->get_order_key($item),
            'is_admin_front_view' => $this->get_is_admin_front_view($item),
            'site_admin_url' => $this->get_site_admin_url(),
        ];
    }

    protected function get_site_admin_url(){
        $admin_url = admin_url( 'admin.php?page=wc-orders&action=edit&id=' );

        return $admin_url;
    }

    protected function get_is_admin_front_view($item_data){
        $view = 'no';

        if($item_data->entity_type === 'yes'){
            $view = 'yes';
        }

        return $view;
    }

    protected function get_order_key($item_data){
        global $wpdb;
        $order_key = '';
        $meta_value = $item_data->meta_value;
        $meta_value = maybe_unserialize($meta_value);
        
        if($meta_value['order_type'] === 'invoice_by_woo'){
            $order_id = $meta_value['order_id'];

            $query = "SELECT *
                FROM {$wpdb->prefix}wc_order_operational_data as operational_data
                WHERE operational_data.order_id = $order_id";

            $operational_data = $wpdb->get_results( $query );
            if(count($operational_data)){
                $operational_data = $operational_data[0];
                $order_key = $operational_data->order_key;
            }
            error_log("operational_data : ".json_encode($order_key));
        }

        return $order_key;
    }

    protected function get_order_id($item_data, $item_id){
        $meta_value = $item_data->meta_value;
        $meta_value = maybe_unserialize($meta_value);
        $order_id = '';
        
        if($meta_value['order_type'] === 'invoice_by_woo'){
            $order_id = $meta_value['order_id'];

            return $order_id;
        }
        if($meta_value['order_type'] === 'manual_adjustment'){
            return $item_id;
        }
    }

    protected function get_order_status_name($item_data){
        $order_status = '';
        $meta_value = $item_data->meta_value;
        $meta_value = maybe_unserialize($meta_value);
        $order_particulars = $meta_value['order_type'];

        if($order_particulars === 'invoice_by_woo'){
            $order_no = $meta_value['order_id'];
            $order = wc_get_order( $order_no );

            if($order){
                $order_status = $order->get_status();
                $order_status = 'wc-'.$order_status;
                $stats = $order_status;
                $statuses = wc_get_order_statuses();

                foreach($statuses as $key => $status){
                    if($key === $order_status){
                        $stats = $status;
                    }
                }

                return $stats;
            }
        }
        if($order_particulars === 'manual_adjustment'){
            $stats = $meta_value['status'];

            if(!empty($stats)){
                $stats = 'Paid';
            }

            return $stats;
        }
    }
    
    protected function get_order_amount($item_data){
        $amount = 0;
        $order = null;
        $meta_value = $item_data->meta_value;
        $meta_value = maybe_unserialize($meta_value);
        $order_type = $meta_value['order_type'];

        if( $order_type === 'invoice_by_woo' ) {
            $order = wc_get_order( $meta_value['order_id'] );
        }

        if($order){
            $amount = $order->get_total();
        }

        if( $order_type === 'manual_adjustment' ) {
            $amount = $meta_value['calculated_price'];
        }

        $amount = wc_format_decimal( $amount, wc_get_price_decimals() );

        return $amount;
    }

    protected function get_order_rate($item_data){
        $rate = wc_format_decimal( 0, wc_get_price_decimals() );
        $meta_value = $item_data->meta_value;
        $meta_value = maybe_unserialize($meta_value);
        // $order = wc_get_order( $order_no );

        $rate = $meta_value['rate'];
        // $rate = floatval($rate);

        if(!empty($rate)){
            $rate = wc_format_decimal( $rate, wc_get_price_decimals() );
        }
        
        return $rate;
    }

    protected function get_purchase_date($item_data){
        $meta_value = $item_data->meta_value;
        $meta_value = maybe_unserialize($meta_value);
        $purchase_date = date("Y-m-d", strtotime($meta_value['purchase_date']));

        return $purchase_date;
    }

    protected function get_date($item_data){
        $meta_value = $item_data->meta_value;
        $meta_value = maybe_unserialize($meta_value);
        $purchase_date = date("d-m-Y", strtotime($meta_value['purchase_date']));

        return $purchase_date;
    }

    protected function get_order_purchased_hour($item_data){
        $meta_value = $item_data->meta_value;
        $meta_value = maybe_unserialize($meta_value);

        $purchased_hour = $meta_value['unpaid_hours'];

        return $purchased_hour;

    }

    protected function get_order_refID($item_data){
        $meta_value = $item_data->meta_value;
        $meta_value = maybe_unserialize($meta_value);
        $ref_ID = $meta_value['ref_id'];

        return $ref_ID;
    }

    protected function get_order_particulars($item_data){
        $particulars = '';
        $meta_value = $item_data->meta_value;
        $meta_value = maybe_unserialize($meta_value);
        $order_particulars = $meta_value['order_type'];

        if($order_particulars === 'invoice_by_woo'){
            $particulars = 'Invoice by woo';
        }
        if($order_particulars === 'manual_adjustment'){
            $particulars = 'Manual Adjustment';
        }

        return $particulars;
    }

    protected function get_order_status($item_data){
        $order_status = '';
        $meta_value = $item_data->meta_value;
        $meta_value = maybe_unserialize($meta_value);
        $order_type = $meta_value['order_type'];

        if($order_type === 'manual_adjustment' ){
            $order_status = 'Paid';
        } 
        if($order_type === 'invoice_by_woo'){
            $order_id = $meta_value['order_id'];
            $order = wc_get_order( $order_id );
            if($order){
                $order_status = $order->get_status();
            }
        }

        return $order_status;
    }
}
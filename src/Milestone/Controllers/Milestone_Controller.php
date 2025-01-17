<?php

namespace WeDevs\PM\Milestone\Controllers;

use WP_REST_Request;
use WeDevs\PM\Milestone\Models\Milestone;
use League\Fractal;
use League\Fractal\Resource\Item as Item;
use League\Fractal\Resource\Collection as Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use WeDevs\PM\Common\Traits\Transformer_Manager;
use WeDevs\PM\Milestone\Transformers\Milestone_Transformer;
use WeDevs\PM\Common\Traits\Request_Filter;
use WeDevs\PM\Common\Models\Meta;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Pagination\Paginator;
use WeDevs\PM\Milestone\Transformers\Order_Status_Transformer;
use WeDevs\PM\Milestone\Transformers\Payment_Transformer;

class Milestone_Controller {

    use Transformer_Manager, Request_Filter;

    private static $_instance;

    public static function getInstance() {
        if ( !self::$_instance ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function index( WP_REST_Request $request ) {
        $project_id = intval( $request->get_param( 'project_id' ) );
        $per_page   = intval( $request->get_param( 'per_page' ) );
        $status     = sanitize_text_field($request->get_param( 'status' ) );
        $per_page   = $per_page ? $per_page : -1;

        $page       = intval( $request->get_param( 'page' ) );
        $page       = $page ? $page : 1;

        Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });

        $milestones = Milestone::with('metas')
            ->where( 'project_id', $project_id );

        if ( ! empty( $status ) ) {
            $milestones = $milestones->where( 'status',  $status);
        }

        $milestones = apply_filters("pm_milestone_index_query", $milestones, $project_id, $request );

        if ( $per_page == '-1' ) {
            $per_page = $milestones->count();
        }

        $milestones = $milestones->paginate( $per_page );

        $milestone_collection = $milestones->getCollection();

        $resource = new Collection( $milestone_collection, new Milestone_Transformer );
        $resource->setPaginator( new IlluminatePaginatorAdapter( $milestones ) );
        return $this->get_response( $resource );
    }

    /**
     * weLabs Modifications [Start]
     * Get time log list
     * REST endpoint: self.base_url + 'pm/v2/projects/'+self.project_id+'/payment-log?'
     * URL query params: ?start_date=2023-12-20&end_date=2023-12-21
     */

     public function payment_log(WP_REST_Request $request){

        // $is_admin = $request->get_param('is_admin');
        $admin = $request->get_param('is_admin');
        $administrator = current_user_can('administrator');
        $is_admin = current_user_can('manage_options'); //$request->get_param('is_admin');
        $c_user = '';

        if(empty($is_admin)){
            $c_user = wp_get_current_user();
            $c_user = $c_user->data;
            $c_user = $c_user->ID;
        }


        $start_date = '';
        $end_date = '';
        $params = $request->get_params();
        if(isset($params['start_date'])){
            $start_date = $params['start_date'];
            $start_date = date("Y-m-d", strtotime($start_date));
            $start_date = "'".$start_date."'";
        }
        if(isset($params['end_date'])){
            $end_date = $params['end_date'];
            $end_date = date("Y-m-d", strtotime($end_date));
            $end_date = "'".$end_date."'";
        }
        
        $project_id = $request->get_param('project_id');
        global $wpdb;
        $invoice_by_woo = \WeLabs\Key4ceProjectMangerExtension\Helpers::INVOICE_BY_WOO;
        $manual_adjustment = \WeLabs\Key4ceProjectMangerExtension\Helpers::MANUAL_ADJUSTMENT;
        
        $sql = "SELECT* FROM wp_pm_meta WHERE project_id = $project_id AND( meta_key LIKE '%{$invoice_by_woo}%'
                    or meta_key LIKE '%{$manual_adjustment}%')";
        
        // if(!empty($c_user)){
        //     $sql = "SELECT* FROM wp_pm_meta WHERE project_id = $project_id AND( meta_key LIKE '%{$invoice_by_woo}%'
        //         or meta_key LIKE '%{$manual_adjustment}%')";
        // }
            
        if(!empty($start_date) && !empty($end_date)){
            $sql = "SELECT* FROM wp_pm_meta WHERE project_id = $project_id AND( meta_key LIKE '%{$invoice_by_woo}%'
                    or meta_key LIKE '%{$manual_adjustment}%')
                    AND cast(created_at as date) >= $start_date
                    AND cast(created_at as date) <= $end_date";
        }

        $payments_data = $wpdb->get_results( $sql );

        if(empty($admin) && $administrator && $is_admin){
            foreach($payments_data as $key => $data){
                $data->entity_type = 'yes';
            }
        }

        $resource = new Collection($payments_data, new Payment_Transformer);

        return $this->get_response( $resource );
    }

    public function create_order(WP_REST_Request $request){
        $order_type = $request->get_param('orderType');
        $data = [];

        if($order_type === 'invoice_by_woo') {
            $unpaid_hours = $request->get_param('unpaidHours');
            $hour_rate = $request->get_param('hourRate');
            $data['unpaid_hours'] = $unpaid_hours;
            $data['rate'] = $hour_rate;
            $data['project_id'] = $request->get_param('project_id');
            $data['order_type'] = $order_type;
        } else {
            $manual_date = $request->get_param('purchaseDate');
            $manual_hour = $request->get_param('adjustHours');
            $manual_rate = $request->get_param('adjustRate');
            // $manual_status = $request->get_param('assign_status');
            $manual_refID = $request->get_param('adjustRefID');
            $data['purchase_date'] = $manual_date;
            $data['unpaid_hours'] = $manual_hour;
            $data['rate'] = $manual_rate;
            // $data['status'] = $manual_status;
            $data['project_id'] = $request->get_param('project_id');
            $data['ref_id'] = $manual_refID;
            $data['order_type'] = $order_type;
        }

        $order_id = \WeLabs\Key4ceProjectMangerExtension\Helpers::create_order($data);

        $resource['success'] = 'unsuccessful';

        if($order_id){
            $resource['success'] = 'successful';

            wp_send_json( $resource );
        }

        wp_send_json( $resource );
    }

    public function delete_manual_order(WP_REST_Request $request){
        $pm_meta_id = $request->get_params('id');
        $pm_meta_id = $pm_meta_id['id'];

        \WeLabs\Key4ceProjectMangerExtension\Helpers::update_hour_on_manual_order_delete($pm_meta_id);

        $resource['success'] = 'successful';

        wp_send_json( $resource );
    }

    public function get_status(WP_REST_Request $request){
        $order_statuses = array();
        $statuses = wc_get_order_statuses();

        foreach($statuses as $key => $status){
            $data['key'] = $key;
            $data['status'] = $status;

            array_push($order_statuses, $data);
        }

        $resource = new Collection($order_statuses, new Order_Status_Transformer);

        return $this->get_response( $resource );
    }

    public function update_manual_order(WP_REST_Request $request){
        global $wpdb;
        $r_params = $request->get_params();
        $manual_status = null;
        $pm_meta_id = $request->get_param('orderId');
        $manual_date = $request->get_param('purchaseDate');
        $manual_hour = $request->get_param('adjustHours');
        $manual_rate = $request->get_param('adjustRate');
        $manual_refID = $request->get_param('adjustRefID');
        $data['purchase_date'] = $manual_date;
        $data['unpaid_hours'] = $manual_hour;
        $data['rate'] = $manual_rate;
        // $data['status'] = $status;
        $data['ref_id'] = $manual_refID;

        $pm_meta_id = \WeLabs\Key4ceProjectMangerExtension\Helpers::update_order($pm_meta_id, $data);
    }

    public function get_manual_order(WP_REST_Request $request){
        global $wpdb;
        $pm_meta_id = $request->get_param('id');
        $query = "SELECT *
            FROM {$wpdb->prefix}pm_meta as pm_meta
            WHERE pm_meta.id = $pm_meta_id";

        $adjust_data = $wpdb->get_results( $query );
        $resource = new Collection($adjust_data, new Payment_Transformer);

        return $this->get_response( $resource );
    }
    /***********
     * weLabs Modifications [End]
     * ***********
     */

    private function get_milestone_collection( $metas = [] ) {
        $milestones = [];

        foreach ($metas as $meta) {
            $milestones[] = $meta->milestone;
        }

        return $milestones;
    }

    public function show( WP_REST_Request $request ) {
        $project_id   = intval( $request->get_param( 'project_id' ) );
        $milestone_id = intval( $request->get_param( 'milestone_id' ) );

        $milestone = Milestone::where( 'id', $milestone_id )
            ->where( 'project_id', $project_id );
        $milestone = apply_filters( "pm_milestone_show_query", $milestone, $project_id, $request );
        $milestone = $milestone->first();
        if ( $milestone == NULL ) {
            return $this->get_response( null,  [
                'message' => pm_get_text('success_messages.no_element')
            ] );
        }
        $resource = new Item( $milestone, new Milestone_Transformer );

        return $this->get_response( $resource );
    }

    public static function create_milestone( $data ) {
        $self = self::getInstance();
        $is_private    = $data[ 'privacy' ];
        $data['is_private']    = $is_private == 'true' || $is_private === true ? 1 : 0;
        // Milestone achieve date
        $achieve_date = $data['achieve_date'];

        // Create a milestone
        $milestone    = Milestone::create( $data );

        // Set 'achieve_date' as milestone meta data
        Meta::create([
            'entity_id'   => $milestone->id,
            'entity_type' => 'milestone',
            'meta_key'    => 'achieve_date',
            'meta_value'  => $achieve_date ? date( 'Y-m-d H:i:s', strtotime( $achieve_date ) ) : null,
            'project_id'  => $milestone->project_id,
        ]);

        do_action("pm_new_milestone_before_response", $milestone, $data );
        // Transform milestone data
        $resource  = new Item( $milestone, new Milestone_Transformer );

        $message = [
            'message' => pm_get_text('success_messages.milestone_created')
        ];
        $response = $self->get_response( $resource, $message );

        do_action( 'cpm_milestone_new', $milestone->id, $data[ 'project_id' ], $data );
        do_action("pm_after_new_milestone", $response, $data );

        return $response;
    }

    public function store( WP_REST_Request $request ) {
        // Grab non empty user input
        $data = $this->extract_non_empty_values( $request );
        $is_private    = sanitize_text_field( $request->get_param( 'privacy' ) );
        $data['is_private']    = $is_private == 'true' || $is_private === true ? 1 : 0;

        // Milestone achieve date
        $achieve_date = $request->get_param( 'achieve_date' );

        // Create a milestone
        $milestone    = Milestone::create( $data );

        // Set 'achieve_date' as milestone meta data
        Meta::create([
            'entity_id'   => $milestone->id,
            'entity_type' => 'milestone',
            'meta_key'    => 'achieve_date',
            'meta_value'  => $achieve_date ? date( 'Y-m-d H:i:s', strtotime( $achieve_date ) ) : null,
            'project_id'  => $milestone->project_id,
        ]);

        do_action("pm_new_milestone_before_response", $milestone, $request->get_params() );
        // Transform milestone data
        $resource  = new Item( $milestone, new Milestone_Transformer );

        $message = [
            'message' => pm_get_text('success_messages.milestone_created')
        ];
        $response = $this->get_response( $resource, $message );

        do_action( 'cpm_milestone_new', $milestone->id, $request->get_param( 'project_id' ), $request->get_params() );
        do_action("pm_after_new_milestone", $response, $request->get_params() );

        return $response;
    }

    public function update( WP_REST_Request $request ) {
        // Grab non empty user data
        $data         = $this->extract_non_empty_values( $request );
        $achieve_date = $request->get_param( 'achieve_date' );
        $status       = $request->get_param( 'status' );

        $is_private    = $request->get_param( 'privacy' );
        $data['is_private']    = $is_private == 'true' || $is_private === true ? 1 : 0;

        // Set project id from url parameter
        $project_id   = $request->get_param( 'project_id' );

        // Set milestone id from url parameter
        $milestone_id = $request->get_param( 'milestone_id' );

        // Find milestone associated with project id and milestone id
        $milestone    = Milestone::where( 'id', $milestone_id )
            ->where( 'project_id', $project_id )
            ->first();

        if ( $milestone ) {
            $milestone->update_model( $data );
        }

        if ( $milestone && $achieve_date ) {
            $meta = Meta::firstOrCreate([
                'entity_id'   => $milestone->id,
                'entity_type' => 'milestone',
                'meta_key'    => 'achieve_date',
                'project_id'  => $milestone->project_id,
            ]);

            $meta->meta_value = date( 'Y-m-d H:i:s', strtotime( $achieve_date ) );
            $meta->save();
        }

        do_action( 'cpm_milestone_update', $milestone_id, $project_id, $request->get_params() );
        do_action("pm_update_milestone_before_response", $milestone, $request->get_params() );
        $resource = new Item( $milestone, new Milestone_Transformer );

        $message = [
            'message' => pm_get_text('success_messages.milestone_updated')
        ];

        $response = $this->get_response( $resource, $message );
        do_action("pm_after_update_milestone", $response, $request->get_params() );

        return $response;
    }

    public function destroy( WP_REST_Request $request ) {
        $project_id   = $request->get_param( 'project_id' );
        $milestone_id = $request->get_param( 'milestone_id' );

        $milestone = Milestone::where( 'id', $milestone_id )
            ->where( 'project_id', $project_id )
            ->first();

        $milestone->boardables()->delete();
        $milestone->metas()->delete();
        $milestone->delete();

        $message = [
            'message' => pm_get_text('success_messages.milestone_deleted')
        ];
        do_action( 'cpm_milestone_delete', $milestone_id, false );

        return $this->get_response(false, $message);
    }
    public function privacy( WP_REST_Request $request ) {
        $project_id = $request->get_param( 'project_id' );
        $milestone_id = $request->get_param( 'milestone_id' );
        $privacy = $request->get_param( 'is_private' );
        $milestone = Milestone::find( $milestone_id );
        $milestone->update_model( [
            'is_private' => $privacy
        ] );
        pm_update_meta( $milestone_id, $project_id, 'milestone', 'privacy', $privacy );
        return $this->get_response( NULL);
    }
}

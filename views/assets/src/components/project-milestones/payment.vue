<template>
    <div class="pm-wrap pm-front-end" id="pm-payments-page">
        <pm-header></pm-header>
        <pm-heder-menu></pm-heder-menu>

        <div class="pm-content" style="margin-bottom: 20px;">
            <h3 class="pm-page-title">  {{ __( 'Payments', 'wedevs-project-manager') }}</h3>
            <div class="pm-display-flex pm-filter-wrapper">
                    <div class="input_field">
                        <pm-date-picker class="pm-datepickter-from" dependency="pm-datepickter-to" name="start_date" id="begin" :placeholder="__('Start', 'pm-pro')" v-model="startDate" v-bind:date-value="startDate" :value="startDate"></pm-date-picker>
                    </div>
                    <div>
                        <pm-date-picker class="pm-datepickter-from" dependency="pm-datepickter-to" name="end_date" id="end" :placeholder="__('End', 'pm-pro')" value="" v-model="endDate" v-bind:date-value="endDate" :value="endDate"></pm-date-picker>
                    </div>
                    <div class="input_field">
                    </div>
                    <div>
                        <a href="#" v-if="isFetchPayments" class="pm--btn pm--btn-default pm-mr-10" @click.prevent="getDateLogsReports">{{  __('GO', 'pm-pro' ) }}</a>
                    </div>
                    <a href="#" v-if="isFetchPayments && shouldShowField" class="pm--btn pm--btn-default pm-mr-10" @click.prevent="openCreateOrderModal">{{  __('Create Woo Order', 'pm-pro' ) }}</a>
                    <a href="#" v-if="isFetchPayments && shouldShowField" class="pm--btn pm--btn-default pm-mr-10" @click.prevent="openManualAdjustmentModal">{{  __('Adjust Manually', 'pm-pro' ) }}</a>
                </div>
        </div>
        <div class="pm-report-page" >

            <modal :is-active="showCreateOrderModal" @close="showCreateOrderModal=false" :width="400">
                <div class="pm-filter-report-modal">
                    <div class="pm-filter-modal-header" slot="header">
                        <h3 class="pm-mb-0">{{ __('Create Woo Order', 'pm-pro' ) }}</h3>
                    </div>

                    <div class="pm-filter-modal-body">
                        <form @submit.prevent="cratePaymentOrder()">
                            <div>
                                <div class='subtask-date new-task-calendar-wrap'>
                                    <label>{{ __( 'Unpaid Hours', 'pm-pro' ) }}</label>
                                    <div class="pm--row">
                                        <input v-model="unpaidHours" type="text" placeholder="Unpaid Hours">
                                    </div>
                                </div>
                                
                                <div class="subtask-date new-task-calendar-wrap">
                                    <label>{{ __('Rate', 'pm-pro') }}</label>
                                    <div class="pm--row">
                                        <input v-model="hourRate" type="text" placeholder="Rate">
                                    </div>
                                </div>
                            </div>
                            <div class="pm-modal-form-buttons">
                                <input slot="footer" class="button button-primary pm-doc-btn" :value="submit_order"  type="submit">
                            </div>
                        </form>
                    </div>


                </div>

            </modal>

            <modal :is-active="showManualAdjustmentModal" @close="showManualAdjustmentModal=false" :width="400">
                        <div class="pm-filter-report-modal">
                            <div class="pm-filter-modal-header" slot="header">
                                <h3 class="pm-mb-0">{{ __('Manual Adjustment', 'pm-pro' ) }}</h3>
                            </div>

                            <div class="pm-filter-modal-body">
                                <form @submit.prevent="crateManualOrder()">
                                    <div>
                                        <div class='subtask-date new-task-calendar-wrap' style="padding-left: 10px;">
                                            <label>{{ __( 'Purchase Date', 'pm-pro' ) }}</label>
                                            <div class="pm--row">
                                                <pm-date-picker v-model="purchaseDate" class="pm-datepickter-from" dependency="pm-datepickter-to" :placeholder="__('Purchase date', 'pm-pro')"></pm-date-picker>
                                            </div>
                                        </div>
                                        <div class='subtask-date new-task-calendar-wrap' style="margin-bottom: 20px; padding-left: 10px;">
                                            <label>{{ __( 'Unpaid Hours', 'pm-pro' ) }}</label>
                                            <div class="pm--row">
                                                <input v-model="adjustHours" type="text" placeholder="Hours">
                                            </div>
                                        </div>

                                        <div class='subtask-date new-task-calendar-wrap' style="margin-bottom: 20px; padding-left: 10px;">
                                            <label>{{ __( 'Rate', 'pm-pro' ) }}</label>
                                            <div class="pm--row">
                                                <input v-model="adjustRate" type="text" placeholder="Rate">
                                            </div>
                                        </div>
                                        <div class='subtask-date new-task-calendar-wrap' style="margin-bottom: 20px; padding-left: 10px;">
                                            <label>{{ __( 'Status', 'pm-pro' ) }}</label>
                                            <div class="pm--row">
                                                <input v-model="manualStatus" type="text" placeholder="" :readonly="true">
                                            </div>
                                        </div>
                                        <!-- <div class="subtask-date new-task-calendar-wrap" style="margin-bottom: 20px;">
                                            <label style="padding-left: 8px;">{{ __('Select Status', 'pm-pro') }}</label>
                                            <multiselect
                                                v-model="assign_status"
                                                :options="assigned_status"
                                                :multiple="false"
                                                :searchable="true"
                                                :allow-empty="true"
                                                :placeholder="select_status"
                                                label="status"
                                                track-by="key">
                                            </multiselect>
                                        </div> -->
                                        <div class='subtask-date new-task-calendar-wrap' style="padding-left: 10px;">
                                            <label>{{ __( 'Ref. ID', 'pm-pro' ) }}</label>
                                            <div class="pm--row">
                                                <input v-model="adjustRefID" type="text" placeholder="Ref. ID">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pm-modal-form-buttons">
                                        <input slot="footer" class="button button-primary pm-doc-btn" :value="submit_manual_order"  type="submit">
                                    </div>
                                </form>
                            </div>


                        </div>

                    </modal>

                    <modal :is-active="showManualAdjustmentModalUpdate" @close="showManualAdjustmentModalUpdate=false" :width="400">
                        <div class="pm-filter-report-modal">
                            <div class="pm-filter-modal-header" slot="header">
                                <h3 class="pm-mb-0">{{ __('Update Manual Adjustment', 'pm-pro' ) }}</h3>
                            </div>

                            <div class="pm-filter-modal-body">
                                <form @submit.prevent="updateManualOrder()">
                                    <div>
                                        <div class='subtask-date new-task-calendar-wrap' style="margin-bottom: 20px; padding-left: 10px;">
                                            <label>{{ __( 'Purchase Date', 'pm-pro' ) }}</label>
                                            <div class="pm--row">
                                                <!-- <input type="date" v-model="manualOrderUpdateData.purchaseDate" id="manual-order-update-date" format="yyyy-mm-dd" @input="getDate($event.target.value)"> -->
                                                <pm-date-picker v-model="manualOrderUpdateData.purchaseDate" v-bind:date-value="manualOrderUpdateData.purchaseDate" :value="manualOrderUpdateData.purchaseDate"></pm-date-picker>
                                            </div>
                                        </div>
                                        <div class='subtask-date new-task-calendar-wrap' style="margin-bottom: 20px; padding-left: 10px;">
                                            <label>{{ __( 'Unpaid Hours', 'pm-pro' ) }}</label>
                                            <div class="pm--row">
                                                <input v-model="manualOrderUpdateData.hour" type="text" value="">
                                            </div>
                                        </div>

                                        <div class='subtask-date new-task-calendar-wrap' style="margin-bottom: 20px; padding-left: 10px;">
                                            <label>{{ __( 'Rate', 'pm-pro' ) }}</label>
                                            <div class="pm--row">
                                                <input v-model="manualOrderUpdateData.rate" type="text" placeholder="Rate">
                                            </div>
                                        </div>
                                        <div class='subtask-date new-task-calendar-wrap' style="margin-bottom: 20px; padding-left: 10px;">
                                            <label>{{ __( 'Status', 'pm-pro' ) }}</label>
                                            <div class="pm--row">
                                                <input v-model="manualStatus" type="text" placeholder="" :readonly="true">
                                            </div>
                                        </div>
                                        <!-- <div class="subtask-date new-task-calendar-wrap" style="margin-bottom: 20px;">
                                            <label style="padding-left: 8px;">{{ __('Select Status', 'pm-pro') }}</label><label style="margin-left: 10px; color: black; font-size: medium;" for="">{{manualOrderUpdateData.orderStatus}}</label>
                                            <multiselect
                                                v-model="update_assign_status"
                                                :options="assigned_status"
                                                :multiple="false"
                                                :searchable="true"
                                                :allow-empty="true"
                                                :placeholder="select_status"
                                                label="status"
                                                track-by="key">
                                            </multiselect>
                                        </div> -->
                                        <div class='subtask-date new-task-calendar-wrap' style="padding-left: 10px;">
                                            <label>{{ __( 'Ref. ID', 'pm-pro' ) }}</label>
                                            <div class="pm--row">
                                                <input v-model="manualOrderUpdateData.refID" type="text" placeholder="Ref. ID">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pm-modal-form-buttons">
                                        <input slot="footer" class="button button-primary pm-doc-btn" :value="update_manual_order"  type="submit">
                                    </div>
                                </form>
                            </div>


                        </div>

                    </modal>


            <div class="payments-current">
                    <table class="wp-list-table widefat fixed striped posts current-payments-table">
                    <thead>
                        <tr>
                            <th class="pointer">Purchase Date</th>
                            <th class="pointer">Hours</th>
                            <th class="pointer">Rate</th>
                            <th class="pointer">Amount</th>
                            <th class="pointer">Status</th>
                            <th class="pointer">Ref. ID</th>
                            <th class="pointer">Particulars</th>
                            <th class="pointer">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr v-for="payment in payments_log" :key="payment.id">
                                <td style="display: none;">
                                    <input type="hidden" :value="payment.id">
                                </td>
                                <td>{{ payment.date || 'N/A' }}</td>
                                <td>{{ payment.hour || 'N/A' }}</td>
                                <td>{{ payment.rate ||'N/A' }}</td>
                                <td>{{ payment.amount||'N/A' }}</td>
                                <td>{{ payment.statusName || 'N/A' }}</td>
                                <td>{{ payment.ref_id || 'N/A' }}</td>
                                <td>{{ payment.particulars || 'N/A' }}</td>
                                <td v-if="shouldShowField && payment.particulars === 'Manual Adjustment'">
                                    <a href="#" @click.prevent="editOrder(payment.id)">Edit</a>
                                    <span class="vertical-line">|</span>
                                    <a href="#" @click.prevent="deleteOrder(payment.id)">Delete</a>
                                </td>
                                <td v-else-if="payment.particulars === 'Invoice by woo'">
                                    <a v-if="shouldShowField" v-bind:href="payment.adminOrderDetails">View</a>
                                    <a v-else="" v-bind:href="payment.clientOrderDetails">View</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <pm-pagination
                        :total_pages="total_pages"
                        :current_page_number="current_page_number"
                        component_name='payments_pagination'
                    />
                </div>
        </div>
        
        <!-- <pm-do-action hook="component-lazy-load"></pm-do-action> -->
    </div>
</template>

<script>
import header from './../common/header.vue';
import Mixins from './mixin';
import modal from './modal.vue';
import date_picker from './payment-date-picker.vue';

export default {
    mixins: [PmMixin.projectTaskLists],
    beforeRouteEnter (to, from, next) {
        next(vm => {
                vm.getSelfPayments();
                // vm.createOrder();
                vm.getStatus();
                // vm.set_manual_order_update_data();
            });
    },
    mixins: [Mixins],
    data () {
        return {
            manualStatus: 'Paid',
            startDate: '',
            endDate: '',
            unpaidHours: '',
            adjustHours: '',
            showCreateOrderModal: false,
            showManualAdjustmentModal: false,
            showManualAdjustmentModalUpdate: false,
            assign_status: null,
            update_assign_status: null,
            submit_order: __('Submit', 'pm-pro' ),
            submit_manual_order: __('Submit', 'pm-pro' ),
            update_manual_order: __('Update', 'pm-pro' ),
            current_page_number: 1,
            manualOrderUpdateData: {
                orderId: '',
                hour: '',
                rate: '',
                refID: '',
                purchaseDate: '',
                orderStatus: '',
            }
        }
    },
    watch: {
        '$route' (route) {
            this.getSelfPayments(this);
        }
    },
    mounted () {
            pm.NProgress.done();
    },
    components: {
        'pm-header': header,
        modal : modal,
        multiselect: pm.Multiselect.Multiselect,
        'pm-date-picker': date_picker,
    },
    computed: {
        // assigned_status(){
        //     var data = this.$store.state.projectMilestones.orderStatuses;
        //     const statuses = [];
        //     data.forEach(status => {
        //         var key = status['data']['key'];
        //         var stat = status['data']['status'];

        //         const statusObj = {
        //             'key': key,
        //             'status': stat,
        //         };

        //         statuses.push(statusObj);
        //     });

        //     return statuses.length ? statuses : [];
        // },
        set_manual_order_update_data(){
        },
        shouldShowField() {
            return this.can_view_client();
        },
        payments_log() {
            var task = this.$store.state.projectTaskLists.totalLoggedTimes;
            // console.log("TASK DATA: ",task);
            if(task !== null){
                var availableTime = task.available_times.available_hours;
                var taskUnpaidHours = task.unpaid_hours;
                const hourData = taskUnpaidHours.split(":");
                var hours = hourData[0];
                var minutes = hourData[1];
                // var totalMinutes = task.available_times.available_minutes;
                // totalMinutes = Math. abs(totalMinutes);
                // var hours = totalMinutes;
                // hours = Math. abs(hours);
                // hours = hours / 60;
                // hours = hours.toFixed(2);
                // hours = parseInt(hours);
                // var hourToMinutes = hours * 60;
                // var extraMinutes = totalMinutes - hourToMinutes;
                var formatHours = hours + ':' + minutes;
                this.unpaidHours = formatHours;
                this.adjustHours = formatHours;
            }
            var data = this.$store.state.projectMilestones.payments;
            var pathname = window.location.pathname;
            var orderDetails = pathname + '?page=wc-orders&action=edit&id=';
            var clientOrderDetails = window.location.origin + '/my-account/view-order/';
            var urlLoc = window.location.origin;

            var startDate = '';
            var end_date = this.getUrlVar('end_date');
            this.endDate = end_date;

            var anotherPart = window.location.hash.split('#')[1];
            anotherPart = anotherPart.split("?");

            if(anotherPart.length > 1){
                anotherPart = anotherPart[1];
                anotherPart = anotherPart.split("&");

                if(anotherPart.length > 1){
                    anotherPart = anotherPart[0];
                    anotherPart = anotherPart.split("=");

                    if(anotherPart.length > 1){
                        startDate = anotherPart[1];
                    }
                }
            }

            this.startDate = startDate;

            data.forEach(element => {
                var id = element.order_no;
                var adminOrderDetails = orderDetails;
                var generalOrderView = urlLoc + '/checkout/order-received/' + element.order_no+'/?key='+element.order_general_view;
                adminOrderDetails = adminOrderDetails + id;
                element.adminOrderDetails = adminOrderDetails;
                element.clientOrderDetails = clientOrderDetails + id;
                // element.clientOrderDetails = generalOrderView;

                if(element.is_admin_front_view === 'yes'){
                    element.adminOrderDetails = element.site_admin_url + id;
                }
            });

            var results = data.map(data =>({
                id: data.id,
                hour: data.hour,
                rate: data.rate,
                amount: data.amount,
                status: data.status,
                ref_id: data.ref_id,
                purchase_date: this.convertDateFormat(data.purchase_date),
                date: data.date,
                particulars: data.particulars,
                adminOrderDetails: data.adminOrderDetails,
                clientOrderDetails: data.clientOrderDetails,
                statusName: data.status_name,
            }))
            
            pm.NProgress.done();

            return results;
        },
        isFetchPayments () {
            return this.$store.state.projectMilestones.isFetchPayments;
        }
    },
    methods: {
        // getDate(date) {
        //     // this.updateColor({
        //     //     hex: color
        //     // });
        //     console.log("date : ",date);
        // },
        getUrlVars () {
            var vars = [], hash;
            var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');

            for (var i = 0; i < hashes.length; i++) {
                hash = hashes[i].split('=');
                vars.push(hash[0]);
                vars[hash[0]] = hash[1];
            }
            return vars;
        },
        getUrlVar (name) {
            return this.getUrlVars()[name];
        },

        updateManualOrder(){
            var self = this;
            var updatePurchaseDate = self.manualOrderUpdateData.purchaseDate;
            var updateAdjustHours = self.manualOrderUpdateData.hour;
            var updateAdjustRate = self.manualOrderUpdateData.rate;
            var updateAdjustRefID = self.manualOrderUpdateData.refID;
            var updateAssign_status = this.update_assign_status;
            var updateOrderId = self.manualOrderUpdateData.orderId;

            if(!updatePurchaseDate || !updateAdjustHours || !updateAdjustRate) {
                pm.Toastr.error(__('Purchase date, Hours and Rate fields are required', 'pm-pro'));
                return;
            }

            var args = {
                data : {
                    'purchaseDate': updatePurchaseDate,
                    'adjustHours': updateAdjustHours,
                    'adjustRate': updateAdjustRate,
                    'assign_status': updateAssign_status,
                    'adjustRefID': updateAdjustRefID,
                    'orderId': updateOrderId,
                }
            }

            this.apiCallForManualOrderUpdate(args);

        },
        getOrderStatus(){
            this.getStatus();
        },
        editOrder(id){
            var args = {
                data : {
                    'id': id,
                }
            }

            this.getManualOrder(args);
        },
        deleteOrder(id){
            var args = {
                data : {
                    'id': id,
                }
            }

            this.deleteManualOrder(args);
        },
        crateManualOrder(){
            if(!this.purchaseDate || !this.adjustHours || !this.adjustRate) {
                pm.Toastr.error(__('Purchase date, Hours and Rate fields are required', 'pm-pro'));
                return;
            }
            var purchaseDate = this.purchaseDate;
            var adjustHours = this.adjustHours;
            var adjustRate = this.adjustRate;
            var assign_status = this.assign_status;
            if(assign_status){
                assign_status = assign_status.key;

            }
            var adjustRefID = this.adjustRefID;
            var orderType = 'manual_adjustment';
            var args = {
                data : {
                    'purchaseDate': purchaseDate,
                    'adjustHours': adjustHours,
                    'adjustRate': adjustRate,
                    'assign_status': assign_status,
                    'adjustRefID': adjustRefID,
                    'orderType': orderType,
                }
            }

            this.createManualOrder(args);
        },
        cratePaymentOrder () {
            var unpaidHours = this.unpaidHours;
            var hourRate = this.hourRate;

            if(unpaidHours  === undefined){
                unpaidHours = 0;
            }
            if(hourRate === undefined){
                hourRate = 0;
            }

            var orderType = 'invoice_by_woo';

            var args = {
                data : {
                    'unpaidHours': unpaidHours,
                    'hourRate': hourRate,
                    'orderType': orderType,
                }
            }

            this.createOrder(args);
        },
        can_view_client() {
            return pmUserCanAccess(PM_Vars.manager_cap_slug);
        },
        getDateLogsReports() {
            var self = this;
            if (!self.startDate || !self.endDate) {
                pm.Toastr.error(__('Dates fields are required', 'pm-pro'));
                return;
            }
            var query = {};
            if (self.startDate){
                query.start_date = this.startDate;
            }

            if (self.endDate){
                query.end_date = this.endDate;
            }

            self.$router.push({
                name: 'payments', 
                // params: { 
                //     project_id: self.project_id,
                //     start_date: this.start_date,
                //     end_date: this.end_date,
                // }
                query: query
            });

            this.getSelfPayments();
        },
        openCreateOrderModal() {
            this.showCreateOrderModal = true;
        },
        openManualAdjustmentModal() {
            this.showManualAdjustmentModal = true;
        },
        convertDateFormat(inputDateString) {
            const inputDate = new Date(inputDateString);

            var d = inputDate.getDate();
            var m = inputDate.getMonth() + 1;
            var y = inputDate.getFullYear();
            var dateString =  y + '-' + (m <= 9 ? '0' + m : m) + '-' + (d <= 9 ? '0' + d : d) ;
            
            // Check if the input date is valid
            if (isNaN(inputDate.getTime())) {
                return "Invalid date";
            }

            // dateString = dateString.format("yy-mm-dd");


            // const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            // const formattedDate = inputDate.toLocaleDateString('en-US', options);
            // const dateString = inputDate.toISOString().split('T')[0];

            return dateString;
        },
    }
}

</script>
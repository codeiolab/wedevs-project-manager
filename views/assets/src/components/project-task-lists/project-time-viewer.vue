<template>
    <div class="hours-label-wrapper">
        <div class="hour-label total-available-hour" v-if="loggedTimeData && loggedTimeData.available_times"><strong>Total Available Hours:</strong> {{ loggedTimeData.available_times.available_hours }}H</div>
        <div class="hour-label paid-unpaid-hours" v-if="loggedTimeData">
            <span><strong>Paid Hours:</strong> {{ loggedTimeData.paid_hours }}H</span>
            <span><strong>Unpaid Hours:</strong> {{ loggedTimeData.unpaid_hours }}H</span>
        </div>
    </div>
</template>
<script>
import Mixins from './mixin';

export default {
    mixins: [Mixins],
    created () {  
        this.getHoursData();
        pmBus.$on('pm_after_add_custom_time', this.getHoursData);
        pmBus.$on('pm_after_stop_time', this.getHoursData);
        pmBus.$on('pm_after_invoice_payment', this.getHoursData);
        pmBus.$on('pm_after_update_invoice', this.getHoursData);
    },
    computed: {
        loggedTimeData () {
            return this.$store.state.projectTaskLists.totalLoggedTimes;
        },

        project(){
            return this.$store.state.project;
        }
    },
    watch: {
        '$route' (route) {
            this.getHoursData();
        },
        project (newData, oldData) {
            this.getHoursData();
        }
    },
    methods: {
        getHoursData(){
            var self = this;
            
            var request = {
                url: self.base_url + 'pm/v2/projects/get-project-logged-time/'+self.$route.params.project_id,
                success (res) {
                    self.$store.commit('projectTaskLists/setTotalLoggedTimes', res);
                    console.log(res);
                },
                error (res) {
                    res.responseJSON.message.map( function( value, index ) {
                        pm.Toastr.error(value);
                    });
                }
            }; 
            self.httpRequest(request);
        },
    },
}
</script>
<style scoped>
.hours-label-wrapper{
    margin-top: 20px;
}
.hour-label {
    background: #fff;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 5px;
    width: 380px;
    color: #000;
}

.hour-label span:last-child {
    display: inline-block;
    margin-left: 10px;
    padding-left: 10px!important;
    border-left: 1px solid #d6d6d6;
    padding-right: 10px!important;
}

.hour-label.paid-unpaid-hours {
    padding: 0;
}

.hour-label.paid-unpaid-hours span {
    padding: 10px 0;
    display: inline-block;
}

.hour-label.paid-unpaid-hours span:first-child {
    padding-left: 10px;
}
</style>
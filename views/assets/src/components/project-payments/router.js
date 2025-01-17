weDevsPmRegisterModule('projectPayments', 'project-payments');

import payments_route from './payments.vue'

weDevsPMRegisterChildrenRoute('projects', 
    [
        {
            path: ':project_id/payments/', 
            component: payments_route,
            name: 'payments',

            // children: [
            //     {
            //         path: 'pages/:current_page_number', 
            //         component: payments_route,
            //         name: 'payments_pagination',
            //     },
            // ]
        }
    ]
)
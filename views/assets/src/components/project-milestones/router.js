weDevsPmRegisterModule('projectMilestones', 'project-milestones');

// const milestones_route = resolve => {
//     require.ensure(['./milestones.vue'], () => {
//         resolve(require('./milestones.vue'));
//     });
// }

import milestones_route from './milestones.vue'
import test_route from './payment.vue'

weDevsPMRegisterChildrenRoute('projects', 
    [
        {
            path: ':project_id/milestones/', 
            component: milestones_route,
            name: 'milestones',

            children: [
                {
                    path: 'pages/:current_page_number', 
                    component: milestones_route,
                    name: 'milestone_pagination',
                },
            ]
        }
    ]
)

weDevsPMRegisterChildrenRoute('projects', 
    [
        {
            path: ':project_id/payments/', 
            component: test_route,
            name: 'payments',
        }
    ]
)

// var milestones = {
//     path: '/:project_id/milestones/', 
//     components: { 
//         'milestones': milestones_route 
//     }, 
//     name: 'milestones',

//     children: [
//         {
//             path: 'pages/:current_page_number', 
//             components: { 
//                 'milestones': milestones_route
//             }, 
//             name: 'milestone_pagination',
//         },
//     ]
// }

// export { milestones };

 
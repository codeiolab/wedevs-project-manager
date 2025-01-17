<?php

use WeDevs\PM\Core\Router\Router;

$router = Router::singleton();

$router->get( 'projects/{project_id}/milestones', 'WeDevs/PM/Milestone/Controllers/Milestone_Controller@index' )
    ->permission( ['WeDevs\PM\Core\Permissions\Access_Project'] );

$router->post( 'projects/{project_id}/milestones', 'WeDevs/PM/Milestone/Controllers/Milestone_Controller@store' )
    ->permission( ['WeDevs\PM\Core\Permissions\Create_Milestone'] )
    ->validator( 'WeDevs\PM\Milestone\Validators\Create_Milestone' )
    ->sanitizer( 'WeDevs\PM\Milestone\Sanitizers\Milestone_Sanitizer' );

$router->get( 'projects/{project_id}/milestones/{milestone_id}', 'WeDevs/PM/Milestone/Controllers/Milestone_Controller@show' )
    ->permission( ['WeDevs\PM\Core\Permissions\Access_Project'] );

$router->post( 'projects/{project_id}/milestones/{milestone_id}/update', 'WeDevs/PM/Milestone/Controllers/Milestone_Controller@update' )
    ->permission( ['WeDevs\PM\Core\Permissions\Edit_Milestone'] )
    ->validator( 'WeDevs\PM\Milestone\Validators\Create_Milestone' )
    ->sanitizer( 'WeDevs\PM\Milestone\Sanitizers\Milestone_Sanitizer' );

$router->post( 'projects/{project_id}/milestones/{milestone_id}/delete', 'WeDevs/PM/Milestone/Controllers/Milestone_Controller@destroy' )
    ->permission( ['WeDevs\PM\Core\Permissions\Edit_Milestone'] );

$router->post( 'projects/{project_id}/milestones/privacy/{milestone_id}', 'WeDevs/PM/Milestone/Controllers/Milestone_Controller@privacy' )
	->permission( ['WeDevs\PM\Core\Permissions\Edit_Milestone'] );


$router->get( 'projects/{project_id}/payment-log?', 'WeDevs/PM/Milestone/Controllers/Milestone_Controller@payment_log' )
    ->permission( ['WeDevs\PM\Core\Permissions\Access_Project'] );
$router->post( 'projects/{project_id}/create-order', 'WeDevs/PM/Milestone/Controllers/Milestone_Controller@create_order');
$router->post( 'projects/{project_id}/delete-manual-order', 'WeDevs/PM/Milestone/Controllers/Milestone_Controller@delete_manual_order');
$router->get( 'projects/{project_id}/get-status', 'WeDevs/PM/Milestone/Controllers/Milestone_Controller@get_status');
$router->post( 'projects/{project_id}/get-manual-order', 'WeDevs/PM/Milestone/Controllers/Milestone_Controller@get_manual_order');
$router->post( 'projects/{project_id}/update-manual-order', 'WeDevs/PM/Milestone/Controllers/Milestone_Controller@update_manual_order');

<?php
/**
 * Tapestry Endpoints
 *
 */

require __DIR__ . '/controller/class.tapestry-permissions.php';
require __DIR__ . '/controller/class.tapestry-controller.php';

add_action( 'rest_api_init', function () {
    register_rest_route( 'tapestry-tool/v1', '/tapestries', array(
        'methods' => 'POST',
        'callback' => 'updateTapestry',
        'permission_callback' => 'TapestryPermissions::postTapestry'
    ));
});

function updateTapestry($request) {
    $data = json_decode($request->get_body());
    // TODO: JSON validations should happen here
    $tapestryController = new TapestryController($data->postId);
    return $tapestryController->updateTapestry($data);
}

add_action('rest_api_init', function () {
    register_rest_route('tapestry-tool/v1', '/tapestries/(?P<id>[\d]+)', array(
        'methods' => 'GET',
        'callback' => 'loadTapestry'
    ));
});

function loadTapestry($request) {
    $postId = $request['id'];
    // TODO: pass postId to the TapestryController constructor
    $tapestryController = new TapestryController;
    return $tapestryController->getTapestry($postId);
}

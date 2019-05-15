<?php
/**
 * Tapestry Endpoints
 *
 */
require __DIR__ . '/controller/class.tapestry-controller.php';

add_action('rest_api_init', function () {
    register_rest_route('tapestry-tool/v1', '/tapestries/(?P<id>[\d]+)/settings', array(
        'methods' => 'PUT',
        'callback' => 'updateTapestrySettings'
        // TODO: Add permissions here later, when saving tapestry PR is merged
    ));
});

function updateTapestrySettings($request) {
    $postId = $request['id'];
    $data = json_decode($request->get_body());
    // TODO: JSON validations should happen here
    $tapestryController = new TapestryController;
    return $tapestryController->updateTapestrySettings($data, $postId);
}

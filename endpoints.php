<?php

/**
 * Tapestry Endpoints
 *
 */

require_once __DIR__ . '/utilities/class.tapestry-permissions.php';
require_once __DIR__ . '/classes/class.tapestry.php';
require_once __DIR__ . '/classes/class.tapestry-node.php';
require_once __DIR__ . '/classes/class.tapestry-group.php';
require_once __DIR__ . '/classes/class.tapestry-user-progress.php';
require_once __DIR__ . '/classes/class.tapestry-audio.php';
require_once __DIR__ . '/classes/class.tapestry-form.php';
require_once __DIR__ . '/classes/class.tapestry-h5p.php';
require_once __DIR__ . '/utilities/class.tapestry-user-roles.php';

$REST_API_NAMESPACE = 'tapestry-tool/v1';

$REST_API_GET_METHOD = 'GET';
$REST_API_POST_METHOD = 'POST';
$REST_API_PUT_METHOD = 'PUT';
$REST_API_DELETE_METHOD = 'DELETE';

$REST_API_ENDPOINTS = [
    'POST_TAPESTRY_NODE' => (object) [
        'ROUTE'     => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes',
        'ARGUMENTS' => [
            'methods'               => $REST_API_POST_METHOD,
            'callback'              => 'addTapestryNode'
        ]
    ],
    'POST_TAPESTRY' => (object) [
        'ROUTE'     => '/tapestries',
        'ARGUMENTS' => [
            'methods'               => $REST_API_POST_METHOD,
            'callback'              => 'addTapestry',
        ]
    ],
    'DELETE_TAPESTRY' => (object) [
        'ROUTE'     => '/tapestries',
        'ARGUMENTS' => [
            'methods'   => $REST_API_DELETE_METHOD,
            'callback'  => 'deleteTapestry'
        ]
    ],
    'PUT_TAPESTRY_SETTINGS' => (object) [
        'ROUTE'     => '/tapestries/(?P<tapestryPostId>[\d]+)/settings',
        'ARGUMENTS' => [
            'methods'               => $REST_API_PUT_METHOD,
            'callback'              => 'updateTapestrySettings',
            'permission_callback'   => 'TapestryPermissions::putTapestrySettings'
        ]
    ],
    'GET_TAPESTRY' => (object) [
        'ROUTE'     => '/tapestries/(?P<tapestryPostId>[\d]+)',
        'ARGUMENTS' => [
            'methods'   => $REST_API_GET_METHOD,
            'callback'  => 'getTapestry'
        ]
    ],
    'GET_GF_FORMS'  => (object) [
        'ROUTE'     => '/gf/forms',
        'ARGUMENTS' => [
            'methods'   => $REST_API_GET_METHOD,
            'callback'  => 'getGfForms'
        ]
    ],
    'POST_TAPESTRY_GROUP' => (object) [
        'ROUTE'     => '/tapestries/(?P<tapestryPostId>[\d]+)/groups',
        'ARGUMENTS' => [
            'methods'               => $REST_API_POST_METHOD,
            'callback'              => 'addTapestryGroup',
            'permission_callback'   => 'TapestryPermissions::postTapestryGroup'
        ]
    ],
    'PUT_TAPESTRY_NODE' => (object) [
        'ROUTE'     => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)',
        'ARGUMENTS' => [
            'methods'               => $REST_API_PUT_METHOD,
            'callback'              => 'updateTapestryNode'
        ]
    ],
    'DELETE_TAPESTRY_NODE' => (object) [
        'ROUTE'     => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)',
        'ARGUMENTS' => [
            'methods'               => $REST_API_DELETE_METHOD,
            'callback'              => 'deleteTapestryNode'
        ]
    ],
    'PUT_TAPESTRY_NODE_SIZE' => (object) [
        'ROUTE'     => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)/size',
        'ARGUMENTS' => [
            'methods'               => $REST_API_PUT_METHOD,
            'callback'              => 'updateTapestryNodeSize'
        ]
    ],
    'PUT_TAPESTRY_NODE_PERMISSIONS' => (object) [
        'ROUTE'     => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)/permissions',
        'ARGUMENTS' => [
            'methods'               => $REST_API_PUT_METHOD,
            'callback'              => 'updateTapestryNodePermissions'
        ]
    ],
    'PUT_TAPESTRY_NODE_DESCRIPTION' => (object) [
        'ROUTE'     => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)/description',
        'ARGUMENTS' => [
            'methods'               => $REST_API_PUT_METHOD,
            'callback'              => 'updateTapestryNodeDescription'
        ]
    ],
    'PUT_TAPESTRY_NODE_TITLE' => (object) [
        'ROUTE'     => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)/title',
        'ARGUMENTS' => [
            'methods'               => $REST_API_PUT_METHOD,
            'callback'              => 'updateTapestryNodeTitle'
        ]
    ],
    'PUT_TAPESTRY_NODE_IMAGE_URL' => (object) [
        'ROUTE'     => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)/imageURL',
        'ARGUMENTS' => [
            'methods'               => $REST_API_PUT_METHOD,
            'callback'              => 'updateTapestryNodeImageURL'
        ]
    ],
    'PUT_TAPESTRY_NODE_LOCKED_IMAGE_URL' => (object) [
        'ROUTE'     => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)/lockedImageURL',
        'ARGUMENTS' => [
            'methods'               => $REST_API_PUT_METHOD,
            'callback'              => 'updateTapestryNodeLockedImageURL'
        ]
    ],
    'PUT_TAPESTRY_NODE_TYPE_DATA' => (object) [
        'ROUTE'     => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)/typeData',
        'ARGUMENTS' => [
            'methods'               => $REST_API_PUT_METHOD,
            'callback'              => 'updateTapestryNodeTypeData'
        ]
    ],
    'PUT_TAPESTRY_NODE_COORDINATES' => (object) [
        'ROUTE'     => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)/coordinates',
        'ARGUMENTS' => [
            'methods'               => $REST_API_PUT_METHOD,
            'callback'              => 'updateTapestryNodeCoordinates'
        ]
    ],
    'POST_TAPESTRY_LINK' => (object) [
        'ROUTE'     => '/tapestries/(?P<tapestryPostId>[\d]+)/links',
        'ARGUMENTS' => [
            'methods'               => $REST_API_POST_METHOD,
            'callback'              => 'addTapestryLink'
        ]
    ],
    'DELETE_TAPESTRY_LINK' => (object) [
        'ROUTE'     => '/tapestries/(?P<tapestryPostId>[\d]+)/links',
        'ARGUMENTS' => [
            'methods'               => $REST_API_DELETE_METHOD,
            'callback'              => 'deleteTapestryLink'
        ]
    ],
    'GET_TAPESTRY_USER_PROGRESS' => (object) [
        'ROUTE'     => 'users/progress',
        'ARGUMENTS' => [
            'methods'               => $REST_API_GET_METHOD,
            'callback'              => 'getUserProgressByPostId',
        ]
    ],
    'GET_TAPESTRY_USER_ENTRY'   => (object) [
        'ROUTE'     => 'users/entries',
        'ARGUMENTS' => [
            'methods'               => $REST_API_GET_METHOD,
            'callback'              => 'getUserEntry',
        ]
    ],
    'UPDATE_TAPESTRY_USER_PROGRESS' => (object) [
        'ROUTE'     => 'users/progress',
        'ARGUMENTS' => [
            'methods'               => $REST_API_POST_METHOD,
            'callback'              => 'updateProgressByNodeId',
        ]
    ],
    'UPDATE_TAPESTRY_USER_COMPLETED'  => (object) [
        'ROUTE'     => 'users/completed',
        'ARGUMENTS' => [
            'methods'               => $REST_API_POST_METHOD,
            'callback'              => 'completeByNodeId'
        ]
    ],
    'UPDATE_TAPESTRY_USER_QUIZ_PROGRESS' => (object) [
        'ROUTE'     => 'users/quiz',
        'ARGUMENTS' => [
            'methods'               => $REST_API_POST_METHOD,
            'callback'              => 'completeQuestionById'
        ]
    ],
    'GET_TAPESTRY_USER_H5P_SETTING' => (object) [
        'ROUTE'     => 'users/h5psettings',
        'ARGUMENTS' => [
            'methods'               => $REST_API_GET_METHOD,
            'callback'              => 'getUserU5PSettingsByPostId',
        ]
    ],
    'UPDATE_TAPESTRY_USER_H5P_SETTING' => (object) [
        'ROUTE'     => 'users/h5psettings',
        'ARGUMENTS' => [
            'methods'               => $REST_API_POST_METHOD,
            'callback'              => 'updateUserH5PSettingsByPostId',
        ]
    ],
    'GET_USER_H5P_AUDIO' => (object) [
        'ROUTE'     => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)/audio/(?P<h5pId>[\d]+)',
        'ARGUMENTS' => [
            'methods'               => $REST_API_GET_METHOD,
            'callback'              => 'getUserH5PAudio',
        ]
    ],
    'GET_NODE_IDS_WITH_RECORDED_AUDIOS' => (object) [
        'ROUTE'     => '/tapestries/(?P<tapestryPostId>[\d]+)/recorded-audio-nodes',
        'ARGUMENTS' => [
            'methods'               => $REST_API_GET_METHOD,
            'callback'              => 'getNodesWithRecordedAudios',
        ]
    ],
    'UPDATE_USER_H5P_AUDIO' => (object) [
        'ROUTE'     => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)/audio/(?P<h5pId>[\d]+)',
        'ARGUMENTS' => [
            'methods'               => $REST_API_POST_METHOD,
            'callback'              => 'updateUserH5PAudio',
        ]
    ],
    'GET_ALL_H5P' => (object) [
        'ROUTE' => '/h5p',
        'ARGUMENTS' => [
            'methods' => $REST_API_GET_METHOD,
            'callback' => 'getAllH5P'
        ]
    ],
    'GET_FORM_ENTRY' => (object) [
        'ROUTE' => '/gf/entries',
        'ARGUMENTS' => [
            'methods'   => $REST_API_GET_METHOD,
            'callback'  => 'getGfEntry'
        ]
    ],
    'GET_TAPESTRY_USER_FAVOURITES' => (object) [
        'ROUTE'     => 'users/favourites',
        'ARGUMENTS' => [
            'methods'               => $REST_API_GET_METHOD,
            'callback'              => 'getUserFavourites',
        ]
    ],
    'UPDATE_TAPESTRY_USER_FAVOURITES' => (object) [
        'ROUTE'     => 'users/favourites',
        'ARGUMENTS' => [
            'methods'               => $REST_API_POST_METHOD,
            'callback'              => 'updateUserFavourites',
        ]
    ],
    'LOGIN' => (object) [
        'ROUTE' => '/login',
        'ARGUMENTS' => [
            'methods'   => $REST_API_POST_METHOD,
            'callback'  => 'login'
        ]
    ],
    'LOGOUT' => (object) [
        'ROUTE' => '/logout',
        'ARGUMENTS' => [
            'methods'   => $REST_API_GET_METHOD,
            'callback'  => function() { wp_logout(); }
        ]
    ]
];

/**
 * REGISTER API ENDPOINTS
 */
foreach ($REST_API_ENDPOINTS as $ENDPOINT) {
    add_action(
        'rest_api_init',
        function () use ($ENDPOINT, $REST_API_NAMESPACE) {
            register_rest_route(
                $REST_API_NAMESPACE,
                $ENDPOINT->ROUTE,
                $ENDPOINT->ARGUMENTS
            );
        }
    );
}

function login($request)
{
    $params = json_decode($request->get_body());
    $credentials = array('user_login' => $params->username, 'user_password' => $params->password, 'remember' => true);
    $user = wp_signon($credentials, false);
    if (is_wp_error($user)) {
        return $user;
    } else {
        wp_set_current_user($user->ID, $user->user_login);
        if (is_user_logged_in()) {
            return wp_create_nonce('wp_rest');
        }
        return false;
    }
}

function getAllH5P()
{
    try {
        $controller = new TapestryH5P();
        return $controller->get();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

function getGfForms()
{
    try {
        $tapestryForms = new TapestryForm();
        return $tapestryForms->getAll();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

function getGfEntry($request)
{
    $formId = $request['form_id'];
    $userId = $request['user_id'];
    try {
        $controller = new TapestryForm();
        return $controller->getEntry($formId, $userId);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Get a Tapestry
 * 
 * @param Object $request HTTP request
 * 
 * @return Object $response HTTP response
 */
function getTapestry($request)
{
    $postId = $request['tapestryPostId'];
    try {
        $tapestry = new Tapestry($postId);
        return $tapestry->get();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Add a tapestry
 * 
 * @param   Object  $request    HTTP request
 * 
 * @return  Object  $response   HTTP response
 */
function addTapestry($request)
{
    $tapestryData = json_decode($request->get_body());
    if (!get_page_by_title($tapestryData->title, 'OBJECT', 'tapestry')) {
        $postId = wp_insert_post(array(
            'comment_status'    => 'closed',
            'ping_status'       => 'closed',
            'post_status'       => 'publish',
            'post_title'        => $tapestryData->title,
            'post_type'         => 'tapestry'
        ), true);
        if (is_wp_error($postId)) {
            return $post;
        }
        $tapestry = new Tapestry($postId);
        try {
            $tapestry->set($tapestryData);
            return $tapestry->save();
        } catch (TapestryError $e) {
            return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
        }
    }
}

function deleteTapestry($request)
{
    $params = json_decode($request->get_body());
    $post = get_page_by_title($params->title, 'OBJECT', 'tapestry');
    return wp_delete_post($post->ID);
}

/**
 * Add a tapestry node
 * 
 * @param   Object  $request    HTTP request
 * 
 * @return  Object  HTTP response 
 */
function addTapestryNode($request)
{
    $postId = $request['tapestryPostId'];
    $parentId = $request['parent_id'];
    $node = json_decode($request->get_body());
    // TODO: JSON validations should happen here
    // make sure that we can only accept one node object at a time
    // adding multiple nodes would require multiple requests from the client
    try {
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        $tapestry = new Tapestry($postId);

        if ($tapestry->isEmpty()) {
            if (!(TapestryUserRoles::isEditor())
                && !(TapestryUserRoles::isAdministrator())
                && !(TapestryUserRoles::isAuthorOfThePost($postId))
            ) {
                throw new TapestryError('ADD_NODE_PERMISSION_DENIED');
            }
        }

        return $tapestry->addNode($node, $parentId);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Add a Tapestry Group
 * 
 * @param   Object  $request    HTTP request
 * 
 * @return  Object  $response   HTTP response
 */
function addTapestryGroup($request)
{
    $postId = $request['tapestryPostId'];
    $group = json_decode($request->get_body());
    // TODO: JSON validations should happen here
    // make sure the type of the group body exists and is 'tapestry_group'
    try {
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        $tapestry = new Tapestry($postId);
        return $tapestry->addGroup($group);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Add A Tapestry Link
 * 
 * @param   Object  $request    HTTP request
 * 
 * @return  Object  $response   HTTP response
 */
function addTapestryLink($request)
{
    $postId = $request['tapestryPostId'];
    $link = json_decode($request->get_body());

    try {
        if (!$link->source || !$link->target) {
            throw new TapestryError('INVALID_NEW_LINK');
        }
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        if ((!TapestryHelpers::isChildNodeOfTapestry($link->source, $postId))
            || (!TapestryHelpers::isChildNodeOfTapestry($link->target, $postId))
        ) {
            throw new TapestryError('INVALID_CHILD_NODE');
        }
        if (!TapestryHelpers::currentUserIsAllowed('ADD', $link->source, $postId)) {
            throw new TapestryError('ADD_NODE_PERMISSION_DENIED');
        }
        if (!TapestryHelpers::currentUserIsAllowed('ADD', $link->target, $postId)) {
            throw new TapestryError('ADD_NODE_PERMISSION_DENIED');
        }
        $tapestry = new Tapestry($postId);
        return $tapestry->addLink($link);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Delete A Tapestry Link
 * 
 * @param   Object  $request    HTTP request
 * 
 * @return  Object  $response   HTTP response
 */
function deleteTapestryLink($request)
{
    $postId = $request['tapestryPostId'];
    $linkIndex = json_decode($request->get_body());
    try {
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        $tapestry = new Tapestry($postId);
        return $tapestry->removeLink($linkIndex);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Update Tapestry Settings
 * 
 * @param   Object  $request    HTTP request
 * 
 * @return  Object  $response   HTTP response 
 */
function updateTapestrySettings($request)
{
    $postId = $request['tapestryPostId'];
    $settings = json_decode($request->get_body());
    // TODO: JSON validations should happen here
    try {
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        $tapestry = new Tapestry($postId);
        $tapestry->set((object) ['settings' => $settings]);
        return $tapestry->save();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Update Tapestry Node
 * 
 * @param   Object  $request    HTTP request
 * 
 * @return  Object  $response   HTTP response
 */
function updateTapestryNode($request)
{
    $postId = $request['tapestryPostId'];
    $nodeMetaId = $request['nodeMetaId'];
    $nodeData = json_decode($request->get_body());
    // TODO: JSON validations should happen here
    // make sure the permissions body exists and not null
    try {
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        if (!TapestryHelpers::isValidTapestryNode($nodeMetaId)) {
            throw new TapestryError('INVALID_NODE_META_ID');
        }
        if (!TapestryHelpers::currentUserIsAllowed('EDIT', $nodeMetaId, $postId)) {
            throw new TapestryError('EDIT_NODE_PERMISSION_DENIED');
        }
        if (!TapestryHelpers::isChildNodeOfTapestry($nodeMetaId, $postId)) {
            throw new TapestryError('INVALID_CHILD_NODE');
        }

        $tapestry = new Tapestry($postId);
        $tapestry->validateNode((object) $nodeData, $tapestry->getNodeParent($nodeMetaId));

        $node = $tapestry->getNode($nodeMetaId);
        $node->set((object) $nodeData);
        return $node->save();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Delete Tapestry Node
 *
 * @param   Object  $request    HTTP request
 *
 * @return  Object  $response   HTTP response
 */
function deleteTapestryNode($request)
{
    $postId = $request['tapestryPostId'];
    $nodeMetaId = $request['nodeMetaId'];

    // TODO: JSON validations should happen here
    // make sure the permissions body exists and not null
    try {
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        if (!TapestryHelpers::isValidTapestryNode($nodeMetaId)) {
            throw new TapestryError('INVALID_NODE_META_ID');
        }
        if (!TapestryHelpers::currentUserIsAllowed('EDIT', $nodeMetaId, $postId)) {
            throw new TapestryError('EDIT_NODE_PERMISSION_DENIED');
        }
        if (!TapestryHelpers::isChildNodeOfTapestry($nodeMetaId, $postId)) {
            throw new TapestryError('INVALID_CHILD_NODE');
        }

        $tapestry = new Tapestry($postId);
        return $tapestry->deleteNodeFromTapestry($nodeMetaId);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}


/**
 * Update Tapestry Node Size
 * 
 * @param   Object  $request    HTTP request
 * 
 * @return  Object  $response   HTTP response
 */
function updateTapestryNodeSize($request)
{
    $postId = $request['tapestryPostId'];
    $nodeMetaId = $request['nodeMetaId'];
    $size = json_decode($request->get_body());
    // TODO: JSON validations should happen here
    // make sure the size exists and is a valid string
    try {
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        if (!TapestryHelpers::isValidTapestryNode($nodeMetaId)) {
            throw new TapestryError('INVALID_NODE_META_ID');
        }
        if (!TapestryHelpers::currentUserIsAllowed('EDIT', $nodeMetaId, $postId)) {
            throw new TapestryError('EDIT_NODE_PERMISSION_DENIED');
        }
        if (!TapestryHelpers::isChildNodeOfTapestry($nodeMetaId, $postId)) {
            throw new TapestryError('INVALID_CHILD_NODE');
        }

        $tapestry = new Tapestry($postId);
        $node = $tapestry->getNode($nodeMetaId);

        $node->set((object) ['size' => $size]);
        return $node->save();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Update Tapestry Node Permissions
 * 
 * @param   Object  $request    HTTP request
 * 
 * @return  Object  $response   HTTP response
 */
function updateTapestryNodePermissions($request)
{
    $postId = $request['tapestryPostId'];
    $nodeMetaId = $request['nodeMetaId'];
    $permissions = json_decode($request->get_body());
    // TODO: JSON validations should happen here
    // make sure the permissions body exists and not null
    try {
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        if (!TapestryHelpers::isValidTapestryNode($nodeMetaId)) {
            throw new TapestryError('INVALID_NODE_META_ID');
        }
        if (!TapestryHelpers::currentUserIsAllowed('EDIT', $nodeMetaId, $postId)) {
            throw new TapestryError('EDIT_NODE_PERMISSION_DENIED');
        }
        if (!TapestryHelpers::isChildNodeOfTapestry($nodeMetaId, $postId)) {
            throw new TapestryError('INVALID_CHILD_NODE');
        }

        $tapestry = new Tapestry($postId);
        $node = $tapestry->getNode($nodeMetaId);

        $node->set((object) ['permissions' => $permissions]);
        return $node->save();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Update Tapestry Node Permissions
 * 
 * @param   Object  $request    HTTP request
 * 
 * @return  Object  $response   HTTP response
 */
function updateTapestryNodeDescription($request)
{
    $postId = $request['tapestryPostId'];
    $nodeMetaId = $request['nodeMetaId'];
    $description = json_decode($request->get_body());
    // TODO: JSON validations should happen here
    // make sure the description body exists and is valid string
    try {
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        if (!TapestryHelpers::isValidTapestryNode($nodeMetaId)) {
            throw new TapestryError('INVALID_NODE_META_ID');
        }
        if (!TapestryHelpers::currentUserIsAllowed('EDIT', $nodeMetaId, $postId)) {
            throw new TapestryError('EDIT_NODE_PERMISSION_DENIED');
        }
        if (!TapestryHelpers::isChildNodeOfTapestry($nodeMetaId, $postId)) {
            throw new TapestryError('INVALID_CHILD_NODE');
        }

        $tapestry = new Tapestry($postId);
        $node = $tapestry->getNode($nodeMetaId);

        $node->set((object) ['description' => $description]);
        return $node->save();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Update Tapestry Node Title
 * 
 * @param   Object  $request    HTTP request
 * 
 * @return  Object  $response   HTTP response
 */
function updateTapestryNodeTitle($request)
{
    $postId = $request['tapestryPostId'];
    $nodeMetaId = $request['nodeMetaId'];
    $title = json_decode($request->get_body());
    // TODO: JSON validations should happen here
    // make sure the title exists and not null
    try {
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        if (!TapestryHelpers::isValidTapestryNode($nodeMetaId)) {
            throw new TapestryError('INVALID_NODE_META_ID');
        }
        if (!TapestryHelpers::currentUserIsAllowed('EDIT', $nodeMetaId, $postId)) {
            throw new TapestryError('EDIT_NODE_PERMISSION_DENIED');
        }
        if (!TapestryHelpers::isChildNodeOfTapestry($nodeMetaId, $postId)) {
            throw new TapestryError('INVALID_CHILD_NODE');
        }

        $tapestry = new Tapestry($postId);
        $node = $tapestry->getNode($nodeMetaId);

        $node->set((object) ['title' => $title]);
        return $node->save();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Update Tapestry Node Image Url
 * 
 * @param   Object  $request    HTTP request
 * 
 * @return  Object  $response   HTTP response
 */
function updateTapestryNodeImageURL($request)
{
    $postId = $request['tapestryPostId'];
    $nodeMetaId = $request['nodeMetaId'];
    $imageURL = json_decode($request->get_body());
    // TODO: JSON validations should happen here
    // make sure the image url exists and not null
    try {
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        if (!TapestryHelpers::isValidTapestryNode($nodeMetaId)) {
            throw new TapestryError('INVALID_NODE_META_ID');
        }
        if (!TapestryHelpers::currentUserIsAllowed('EDIT', $nodeMetaId, $postId)) {
            throw new TapestryError('EDIT_NODE_PERMISSION_DENIED');
        }
        if (!TapestryHelpers::isChildNodeOfTapestry($nodeMetaId, $postId)) {
            throw new TapestryError('INVALID_CHILD_NODE');
        }

        $tapestry = new Tapestry($postId);
        $node = $tapestry->getNode($nodeMetaId);

        $node->set((object) ['imageURL' => $imageURL]);
        return $node->save();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}


/**
 * Update Tapestry Node Locked Image Url
 * 
 * @param   Object  $request    HTTP request
 * 
 * @return  Object  $response   HTTP response
 */
function updateTapestryNodeLockedImageURL($request)
{
    $postId = $request['tapestryPostId'];
    $nodeMetaId = $request['nodeMetaId'];
    $imageURL = json_decode($request->get_body());
    // TODO: JSON validations should happen here
    // make sure the image url exists and not null
    try {
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        if (!TapestryHelpers::isValidTapestryNode($nodeMetaId)) {
            throw new TapestryError('INVALID_NODE_META_ID');
        }
        if (!TapestryHelpers::currentUserIsAllowed('EDIT', $nodeMetaId, $postId)) {
            throw new TapestryError('EDIT_NODE_PERMISSION_DENIED');
        }
        if (!TapestryHelpers::isChildNodeOfTapestry($nodeMetaId, $postId)) {
            throw new TapestryError('INVALID_CHILD_NODE');
        }

        $tapestry = new Tapestry($postId);
        $node = $tapestry->getNode($nodeMetaId);
        $node->set((object) ['lockedImageURL' => $imageURL]);
        return $node->save();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/** 
 * Update Tapestry Node Type Data
 * 
 * @param   Object  $request    HTTP request
 * 
 * @return  Object  $response   HTTP response
 */
function updateTapestryNodeTypeData($request)
{
    $postId = $request['tapestryPostId'];
    $nodeMetaId = $request['nodeMetaId'];
    $typeData = json_decode($request->get_body());
    // TODO: JSON validations should happen here
    // make sure the type data exists and not null
    try {
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        if (!TapestryHelpers::isValidTapestryNode($nodeMetaId)) {
            throw new TapestryError('INVALID_NODE_META_ID');
        }
        if (!TapestryHelpers::currentUserIsAllowed('EDIT', $nodeMetaId, $postId)) {
            throw new TapestryError('EDIT_NODE_PERMISSION_DENIED');
        }
        if (!TapestryHelpers::isChildNodeOfTapestry($nodeMetaId, $postId)) {
            throw new TapestryError('INVALID_CHILD_NODE');
        }

        $tapestry = new Tapestry($postId);
        $node = $tapestry->getNode($nodeMetaId);

        $node->set((object) ['typeData' => $typeData]);
        return $node->save();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Update Tapestry Node Coordinates
 * 
 * @param   Object  $request    HTTP request
 * 
 * @return  Object  $response   HTTP response
 */
function updateTapestryNodeCoordinates($request)
{
    $postId = $request['tapestryPostId'];
    $nodeMetaId = $request['nodeMetaId'];
    $coordinates = json_decode($request->get_body());
    // TODO: JSON validations should happen here
    // make sure the coordinates exists and not null
    try {
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        if (!TapestryHelpers::isValidTapestryNode($nodeMetaId)) {
            throw new TapestryError('INVALID_NODE_META_ID');
        }
        if (!TapestryHelpers::currentUserIsAllowed('EDIT', $nodeMetaId, $postId)) {
            throw new TapestryError('EDIT_NODE_PERMISSION_DENIED');
        }
        if (!TapestryHelpers::isChildNodeOfTapestry($nodeMetaId, $postId)) {
            throw new TapestryError('INVALID_CHILD_NODE');
        }

        $tapestry = new Tapestry($postId);
        $node = $tapestry->getNode($nodeMetaId);

        $node->set((object) ['coordinates' => $coordinates]);
        return $node->save();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * 
 */
function getUserEntry($request)
{
    $postId = $request['post_id'];
    $nodeMetaId = $request['node_id'];
    $formId = $request['form_id'];

    try {
        $userProgress = new TapestryUserProgress($postId, $nodeMetaId);
        return $userProgress->getUserEntries($formId);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Update a single node progress for the current user by passing in node id, post id and progress value
 * Example: /wp-json/tapestry-tool/v1/users/progress?post_id=44&node_id=1&progress_value=0.2
 * 
 * @param Object $request HTTP request
 * 
 */
function updateProgressByNodeId($request)
{
    $postId = $request['post_id'];
    $nodeMetaId = $request['node_id'];
    $progressValue = $request['progress_value'];

    try {
        $userProgress = new TapestryUserProgress($postId, $nodeMetaId);
        $userProgress->updateUserProgress($progressValue);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Set node as completed for the current user
 * Example: /wp-json/tapestry-tool/v1/users/completed?post_id=44&node_id=1
 * 
 * @param Object $request HTTP request
 * 
 * @return null
 */
function completeByNodeId($request)
{
    $postId = $request['post_id'];
    $nodeMetaId = $request['node_id'];

    try {
        $userProgress = new TapestryUserProgress($postId, $nodeMetaId);
        $userProgress->complete();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Set quiz as completed for the current user
 * Example: /wp-json/tapestry-tool/v1/users/quiz?post_id=44&node_id=1&question_id=abcd
 * 
 * @param Object $request HTTP request
 * 
 * @return null
 */
function completeQuestionById($request)
{
    $postId = $request['post_id'];
    $nodeMetaId = $request['node_id'];
    $questionId = $request['question_id'];

    try {
        $userProgress = new TapestryUserProgress($postId, $nodeMetaId);
        $userProgress->completeQuestion($questionId);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Get user h5p video setting on a tapestry page by post id. Will need to pass these as query parameters
 * Example: /wp-json/tapestry-tool/v1/users/h5psettings?post_id=42
 * 
 * @param Object $request HTTP request
 * 
 * @return Object $response HTTP response
 */
function getUserU5PSettingsByPostId($request)
{
    $postId = $request['post_id'];

    try {
        $userProgress = new TapestryUserProgress($postId);
        return $userProgress->getH5PSettings();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Update the user's h5p settings by post id
 * Example: /wp-json/tapestry-tool/v1/users/h5psettings?post_id=44&json={"volume":100,"muted":false,"caption":null,"quality":"q1","playbackRate":0.5,"time":11.934346}
 * 
 * @param Object $request HTTP request
 * 
 */
function updateUserH5PSettingsByPostId($request)
{
    $postId = $request['post_id'];
    $h5pSettingsData = $request['json'];

    try {
        $userProgress = new TapestryUserProgress($postId);
        $userProgress->updateH5PSettings($h5pSettingsData);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Get user progress on a tapestry page by post id. 
 * Example: /wp-json/tapestry-tool/v1/users/progress?post_id=44
 * 
 * @param Object $request HTTP request
 * 
 * @return Object $response HTTP response
 */
function getUserProgressByPostId($request)
{
    $postId = $request['post_id'];
    try {
        $userProgress = new TapestryUserProgress($postId);
        return $userProgress->get();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Update h5p audio of a node for a user
 * 
 * @param Object $request HTTP request
 * 
 * @return Object $response HTTP response
 */
function updateUserH5PAudio($request)
{
    $postId = $request['tapestryPostId'];
    $nodeMetaId = $request['nodeMetaId'];
    $h5pId = $request['h5pId'];
    $audio = $request->get_body();

    try {
        if (!TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        if (!TapestryHelpers::isValidTapestryNode($nodeMetaId)) {
            throw new TapestryError('INVALID_NODE_META_ID');
        }
        if (!is_numeric($h5pId)) {
            throw new TapestryError('INVALID_AUDIO');
        }
        if (!is_string($audio) || empty($audio)) {
            throw new TapestryError('INVALID_AUDIO');
        }
        if (wp_get_current_user()->ID == 0) {
            throw new TapestryError('INVALID_USER_ID');
        }

        $TapestryAudio = new TapestryAudio($postId, $nodeMetaId, $h5pId);
        return $TapestryAudio->save($audio);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Get h5p audio of a node for a user
 * 
 * @param Object $request HTTP request
 * 
 * @return Object $response HTTP response
 */
function getUserH5PAudio($request)
{
    $postId = $request['tapestryPostId'];
    $nodeMetaId = $request['nodeMetaId'];
    $h5pId = $request['h5pId'];

    try {
        if (!TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        if (!TapestryHelpers::isValidTapestryNode($nodeMetaId)) {
            throw new TapestryError('INVALID_NODE_META_ID');
        }
        if (!is_numeric($h5pId)) {
            throw new TapestryError('INVALID_AUDIO');
        }
        if (wp_get_current_user()->ID == 0) {
            throw new TapestryError('INVALID_USER_ID');
        }

        $TapestryAudio = new TapestryAudio($postId, $nodeMetaId, $h5pId);
        return $TapestryAudio->get();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Get all node IDs that have H5P audios recorded
 * 
 * @param Object $request HTTP request
 * 
 * @return Object $response HTTP response
 */
function getNodesWithRecordedAudios($request)
{
    $postId = $request['tapestryPostId'];

    try {
        if (!TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        if (wp_get_current_user()->ID == 0) {
            throw new TapestryError('INVALID_USER_ID');
        }

        $TapestryAudio = new TapestryAudio($postId);
        return $TapestryAudio->getNodesWithRecordedAudios();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}
/**
 * Get user favourite nodes on a tapestry page by post id. 
 * Example: /wp-json/tapestry-tool/v1/users/favourites?post_id=44
 * 
 * @param Object $request HTTP request
 * 
 * @return Object $response HTTP response
 */
function getUserFavourites($request)
{
    $postId = $request['post_id'];
    try {
        $userProgress = new TapestryUserProgress($postId);
        return $userProgress->getFavourites();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Update favourite nodes for the current user by passing in post id and favourites array
 * Example: /wp-json/tapestry-tool/v1/users/progress?post_id=44&favourites=[409, 411]
 * 
 * @param Object $request HTTP request
 * 
 */
function updateUserFavourites($request)
{
    $postId = $request['post_id'];
    $favourites = json_decode($request->get_body())->favourites;
    try {
        $userProgress = new TapestryUserProgress($postId);
        return $userProgress->updateFavourites($favourites);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
} 

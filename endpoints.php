<?php

/**
 * Tapestry Endpoints.
 */
require_once __DIR__.'/utilities/class.tapestry-permissions.php';
require_once __DIR__.'/classes/class.tapestry.php';
require_once __DIR__.'/classes/class.tapestry-node.php';
require_once __DIR__.'/classes/class.tapestry-group.php';
require_once __DIR__.'/classes/class.tapestry-user-progress.php';
require_once __DIR__.'/classes/class.tapestry-audio.php';
require_once __DIR__.'/classes/class.tapestry-h5p.php';
require_once __DIR__.'/classes/class.constants.php';
require_once __DIR__.'/utilities/class.tapestry-user.php';

$REST_API_NAMESPACE = 'tapestry-tool/v1';

$REST_API_GET_METHOD = 'GET';
$REST_API_POST_METHOD = 'POST';
$REST_API_PUT_METHOD = 'PUT';
$REST_API_DELETE_METHOD = 'DELETE';

$REST_API_ENDPOINTS = [
    'POST_TAPESTRY_NODE' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes',
        'ARGUMENTS' => [
            'methods' => $REST_API_POST_METHOD,
            'callback' => 'addTapestryNode',
        ],
    ],
    'POST_TAPESTRY_NODE_REVIEW' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)/review',
        'ARGUMENTS' => [
            'methods' => $REST_API_POST_METHOD,
            'callback' => 'addTapestryNodeReview',
        ],
    ],
    'POST_TAPESTRY' => (object) [
        'ROUTE' => '/tapestries',
        'ARGUMENTS' => [
            'methods' => $REST_API_POST_METHOD,
            'callback' => 'addTapestry',
            'permission_callback' => 'TapestryPermissions::postTapestry',
        ],
    ],
    'DELETE_TAPESTRY' => (object) [
        'ROUTE' => '/tapestries',
        'ARGUMENTS' => [
            'methods' => $REST_API_DELETE_METHOD,
            'callback' => 'deleteTapestry',
        ],
    ],
    'GET_TAPESTRY' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)',
        'ARGUMENTS' => [
            'methods' => $REST_API_GET_METHOD,
            'callback' => 'getTapestry',
        ],
    ],
    'PUT_TAPESTRY' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)',
        'ARGUMENTS' => [
            'methods' => $REST_API_PUT_METHOD,
            'callback' => 'putTapestry',
        ],
    ],
    'PUT_TAPESTRY_SETTINGS' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)/settings',
        'ARGUMENTS' => [
            'methods' => $REST_API_PUT_METHOD,
            'callback' => 'updateTapestrySettings',
            'permission_callback' => 'TapestryPermissions::putTapestrySettings',
        ],
    ],
    'POST_TAPESTRY_GROUP' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)/groups',
        'ARGUMENTS' => [
            'methods' => $REST_API_POST_METHOD,
            'callback' => 'addTapestryGroup',
            'permission_callback' => 'TapestryPermissions::postTapestryGroup',
        ],
    ],
    'PUT_TAPESTRY_NODE' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)',
        'ARGUMENTS' => [
            'methods' => $REST_API_PUT_METHOD,
            'callback' => 'updateTapestryNode',
        ],
    ],
    'DELETE_TAPESTRY_NODE' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)',
        'ARGUMENTS' => [
            'methods' => $REST_API_DELETE_METHOD,
            'callback' => 'deleteTapestryNode',
        ],
    ],
    'PUT_TAPESTRY_NODE_SIZE' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)/size',
        'ARGUMENTS' => [
            'methods' => $REST_API_PUT_METHOD,
            'callback' => 'updateTapestryNodeSize',
        ],
    ],
    'PUT_TAPESTRY_NODE_DESCRIPTION' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)/description',
        'ARGUMENTS' => [
            'methods' => $REST_API_PUT_METHOD,
            'callback' => 'updateTapestryNodeDescription',
        ],
    ],
    'PUT_TAPESTRY_NODE_TITLE' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)/title',
        'ARGUMENTS' => [
            'methods' => $REST_API_PUT_METHOD,
            'callback' => 'updateTapestryNodeTitle',
        ],
    ],
    'PUT_TAPESTRY_NODE_IMAGE_URL' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)/imageURL',
        'ARGUMENTS' => [
            'methods' => $REST_API_PUT_METHOD,
            'callback' => 'updateTapestryNodeImageURL',
        ],
    ],
    'PUT_TAPESTRY_NODE_LOCKED_IMAGE_URL' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)/lockedImageURL',
        'ARGUMENTS' => [
            'methods' => $REST_API_PUT_METHOD,
            'callback' => 'updateTapestryNodeLockedImageURL',
        ],
    ],
    'PUT_TAPESTRY_NODE_TYPE_DATA' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)/typeData',
        'ARGUMENTS' => [
            'methods' => $REST_API_PUT_METHOD,
            'callback' => 'updateTapestryNodeTypeData',
        ],
    ],
    'PUT_TAPESTRY_NODE_COORDINATES' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)/coordinates',
        'ARGUMENTS' => [
            'methods' => $REST_API_PUT_METHOD,
            'callback' => 'updateTapestryNodeCoordinates',
        ],
    ],
    'GET_TAPESTRY_NODE_HAS_DRAFT_CHILDREN' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)/nodeHasDraftChildren',
        'ARGUMENTS' => [
            'methods' => $REST_API_GET_METHOD,
            'callback' => 'getTapestryNodeHasDraftChildren',
        ],
    ],
    'POST_TAPESTRY_LINK' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)/links',
        'ARGUMENTS' => [
            'methods' => $REST_API_POST_METHOD,
            'callback' => 'addTapestryLink',
        ],
    ],
    'DELETE_TAPESTRY_LINK' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)/links',
        'ARGUMENTS' => [
            'methods' => $REST_API_DELETE_METHOD,
            'callback' => 'deleteTapestryLink',
        ],
    ],
    'GET_TAPESTRY_USER_PROGRESS' => (object) [
        'ROUTE' => 'users/progress',
        'ARGUMENTS' => [
            'methods' => $REST_API_GET_METHOD,
            'callback' => 'getUserProgressByPostId',
        ],
    ],
    'UPDATE_TAPESTRY_USER_PROGRESS' => (object) [
        'ROUTE' => 'users/progress',
        'ARGUMENTS' => [
            'methods' => $REST_API_POST_METHOD,
            'callback' => 'updateProgressByNodeId',
        ],
    ],
    'UPDATE_TAPESTRY_USER_COMPLETED' => (object) [
        'ROUTE' => 'users/completed',
        'ARGUMENTS' => [
            'methods' => $REST_API_POST_METHOD,
            'callback' => 'completeByNodeId',
        ],
    ],
    'UPDATE_TAPESTRY_USER_ACTIVITY_PROGRESS' => (object) [
        'ROUTE' => 'users/activity',
        'ARGUMENTS' => [
            'methods' => $REST_API_POST_METHOD,
            'callback' => 'completeQuestionById',
        ],
    ],
    'GET_TAPESTRY_USER_H5P_SETTING' => (object) [
        'ROUTE' => 'users/h5psettings/(?P<tapestryPostId>[\d]+)',
        'ARGUMENTS' => [
            'methods' => $REST_API_GET_METHOD,
            'callback' => 'getUserH5PSettingsByPostId',
        ],
    ],
    'UPDATE_TAPESTRY_USER_H5P_SETTING' => (object) [
        'ROUTE' => 'users/h5psettings/(?P<tapestryPostId>[\d]+)',
        'ARGUMENTS' => [
            'methods' => $REST_API_POST_METHOD,
            'callback' => 'updateUserH5PSettingsByPostId',
        ],
    ],
    'GET_USER_AVATAR' => (object) [
        'ROUTE' => '/users/userSettings/avatar',
        'ARGUMENTS' => [
            'methods' => $REST_API_GET_METHOD,
            'callback' => 'getUserAvatar',
        ],
    ],
    'UPDATE_USER_SETTINGS' => (object) [
        'ROUTE' => '/users/userSettings',
        'ARGUMENTS' => [
            'methods' => $REST_API_PUT_METHOD,
            'callback' => 'updateUserSettings',
        ],
    ],
    'POST_USER_AUDIO' => (object) [
        'ROUTE' => 'users/activity/audio/tapestries/(?P<tapestryPostId>[\d]+)/nodes/(?P<nodeMetaId>[\d]+)',
        'ARGUMENTS' => [
            'methods' => $REST_API_POST_METHOD,
            'callback' => 'postUserAudio',
        ],
    ],
    'GET_ALL_H5P' => (object) [
        'ROUTE' => '/h5p',
        'ARGUMENTS' => [
            'methods' => $REST_API_GET_METHOD,
            'callback' => 'getAllH5P',
        ],
    ],
    'GET_TAPESTRY_USER_FAVOURITES' => (object) [
        'ROUTE' => 'users/favourites',
        'ARGUMENTS' => [
            'methods' => $REST_API_GET_METHOD,
            'callback' => 'getUserFavourites',
        ],
    ],
    'UPDATE_TAPESTRY_USER_FAVOURITES' => (object) [
        'ROUTE' => 'users/favourites',
        'ARGUMENTS' => [
            'methods' => $REST_API_POST_METHOD,
            'callback' => 'updateUserFavourites',
        ],
    ],
    'LOGIN' => (object) [
        'ROUTE' => '/login',
        'ARGUMENTS' => [
            'methods' => $REST_API_POST_METHOD,
            'callback' => 'login',
        ],
    ],
    'LOGOUT' => (object) [
        'ROUTE' => '/logout',
        'ARGUMENTS' => [
            'methods' => $REST_API_GET_METHOD,
            'callback' => function () {
                wp_logout();
            },
        ],
    ],
    'GET_TAPESTRY_CONTRIBUTORS' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)/contributors',
        'ARGUMENTS' => [
            'methods' => $REST_API_GET_METHOD,
            'callback' => 'getTapestryContributors',
        ],
    ],
    'GET_ALL_ROLES' => (object) [
        'ROUTE' => '/roles',
        'ARGUMENTS' => [
            'methods' => $REST_API_GET_METHOD,
            'callback' => 'get_all_user_roles',
        ],
    ],
    'GET_TAPESTRY_EXPORT' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)/export',
        'ARGUMENTS' => [
            'methods' => $REST_API_GET_METHOD,
            'callback' => 'exportTapestry',
            'permission_callback' => 'TapestryPermissions::putTapestrySettings',
        ],
    ],
    'OPTIMIZE_THUMBNAILS' => (object) [
        'ROUTE' => '/tapestries/(?P<tapestryPostId>[\d]+)/optimize_thumbnails',
        'ARGUMENTS' => [
            'methods' => $REST_API_POST_METHOD,
            'callback' => 'optimizeTapestryNodeThumbnails',
            'permission_callback' => 'TapestryPermissions::putTapestrySettings',
        ],
    ],
];

/*
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

function exportTapestry($request)
{
    $postId = $request['tapestryPostId'];
    try {
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        $tapestry = new Tapestry($postId);
        return $tapestry->export();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

function get_all_user_roles($request)
{
    global $wp_roles;
    
    $roles = $wp_roles->roles;
    
    return $roles;
}
  

function login($request)
{
    $params = json_decode($request->get_body());
    $credentials = ['user_login' => $params->username, 'user_password' => $params->password, 'remember' => true];
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

/**
 * Get a Tapestry.
 *
 * @param object $request HTTP request
 *
 * @return object $response HTTP response
 */
function getTapestry($request)
{
    $postId = $request['tapestryPostId'];
    $filterUserId = $request['filter_user_id'];
    try {
        $tapestry = new Tapestry($postId);
        $data = $tapestry->get($filterUserId);

        return $data;
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Add a tapestry.
 *
 * @param object $request HTTP request
 *
 * @return object $response   HTTP response
 */
function addTapestry($request)
{
    $tapestryData = json_decode($request->get_body());
    try {
        $title = $tapestryData->title;
        $page = get_page_by_title($tapestryData->title, 'OBJECT', 'tapestry');
        if ($page) {
            $count = 1;
            $title = $tapestryData->title.' ('.$count.')';
            while (get_page_by_title($title, 'OBJECT', 'tapestry')) {
                ++$count;
                $title = $tapestryData->title.' ('.$count.')';
            }
        }
        $user = wp_get_current_user();

        $postId = wp_insert_post([
            'comment_status' => 'closed',
            'post_author' => $user->ID ? $user->ID : 1,
            'ping_status' => 'closed',
            'post_status' => 'publish',
            'post_title' => $title,
            'post_type' => 'tapestry',
        ], true);
        if (is_wp_error($postId)) {
            throw new TapestryError('FAILED_TO_CREATE_POST');
        }

        return importTapestry($postId, $tapestryData);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Get a Tapestry.
 *
 * @param object $request HTTP request
 *
 * @return object $response HTTP response
 */
function putTapestry($request)
{
    $postId = $request['tapestryPostId'];
    $tapestryData = json_decode($request->get_body());
    try {
        return importTapestry($postId, $tapestryData);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

function importTapestry($postId, $tapestryData)
{
    $tapestry = new Tapestry($postId);

    if (!$tapestry->isEmpty()) {
        throw new TapestryError('TAPESTRY_NOT_EMPTY');
    }

    $data = new stdClass();
    if (isset($tapestryData->groups)) {
        $data->groups = $tapestryData->groups;
    }
    if (isset($tapestryData->settings)) {
        $data->settings = $tapestryData->settings;
    }
    $tapestry->set($data);

    if (isset($tapestryData->nodes) && isset($tapestryData->links)) {
        $idMap = new stdClass();

        // Construct ID map and add nodes to new Tapestry
        foreach ($tapestryData->nodes as $node) {
            $oldNodeId = $node->id;
            $newNode = $tapestry->addNode($node);
            $newNodeId = $newNode->id;
            $idMap->$oldNodeId = $newNodeId;
        }

        // Now update any node data that relies on IDs
        foreach ($tapestryData->nodes as $oldNode) {
            $oldNodeId = $oldNode->id;
            $newNodeId = $idMap->$oldNodeId;

            $tapestryNode = $tapestry->getNode($newNodeId);
            $node = $tapestryNode->get();

            foreach ($node->conditions as $condition) {
                if ($condition->nodeId) {
                    $oldDependency = $condition->nodeId;
                    $condition->nodeId = $idMap->$oldDependency;
                }
            }


            $tapestryNode->set($node);
            $tapestryNode->save();
        }

        foreach ($tapestryData->links as $link) {
            $oldSource = $link->source;
            $oldTarget = $link->target;

            $link->source = $idMap->$oldSource;
            $link->target = $idMap->$oldTarget;

            $tapestry->addLink($link);
        }
    }

    return $tapestry->save();
}


function deleteTapestry($request)
{
    $params = json_decode($request->get_body());
    $post = get_page_by_title($params->title, 'OBJECT', 'tapestry');

    return wp_delete_post($post->ID);
}

/**
 * Add a tapestry node.
 *
 * @param object $request HTTP request
 *
 * @return object HTTP response
 */
function addTapestryNode($request)
{
    $postId = $request['tapestryPostId'];
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
            $user = new TapestryUser();
            if (!$user->canEdit($postId)) {
                throw new TapestryError('ADD_NODE_PERMISSION_DENIED');
            }
        }

        return $tapestry->addNode($node);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Add a review to a Tapestry node. A review is either a comment or a change of
 * node status.
 *
 * Request Body
 * --
 * Accepts a review object in the request body, of shape:
 * ```
 * {
 *  type: COMMENT | STATUS_CHANGE
 *  timestamp: string // current datetime in ISO8601 format
 *  ...data // varies by type of review
 * }
 * ```
 *
 * Access
 * --
 * - If the current user has Tapestry edit privileges, any review type is allowed.
 * - If the current user is the author of the node, only comments are allowed.
 * - Otherwise, the user is not allowed to review this node.
 *
 * @param object $request HTTP request
 *
 * @return object HTTP response
 */
function addTapestryNodeReview($request)
{
    $postId = $request['tapestryPostId'];
    $nodeMetaId = $request['nodeMetaId'];
    $review = json_decode($request->get_body());
    try {
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        if (!TapestryHelpers::isValidTapestryNode($nodeMetaId)) {
            throw new TapestryError('INVALID_NODE_META_ID');
        }
        if (!TapestryHelpers::isChildNodeOfTapestry($nodeMetaId, $postId)) {
            throw new TapestryError('INVALID_CHILD_NODE');
        }
        if (!isset($review->comments) || !is_array($review->comments)) {
            throw new TapestryError('INVALID_REVIEW', 'A review should have an array of comments', 400);
        }

        $tapestry = new Tapestry($postId);
        $node = $tapestry->getNode($nodeMetaId);
        return $node->addReview($review->comments);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Add a Tapestry Group.
 *
 * @param object $request HTTP request
 *
 * @return object $response   HTTP response
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
 * Add A Tapestry Link.
 *
 * @param object $request HTTP request
 *
 * @return object $response   HTTP response
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
        if (TapestryHelpers::nodeIsDraft($link->source, $postId)
            || TapestryHelpers::nodeIsDraft($link->target, $postId)) {
            $tapestry = new Tapestry($postId);
            return $tapestry->addLink($link);
        }
        if (!TapestryHelpers::userIsAllowed('ADD', $link->source, $postId)) {
            throw new TapestryError('ADD_LINK_PERMISSION_DENIED');
        }
        if (!TapestryHelpers::userIsAllowed('ADD', $link->target, $postId)
            && (!isset($link->addedOnNodeCreation) || !$link->addedOnNodeCreation)) {
            throw new TapestryError('ADD_LINK_PERMISSION_DENIED');
        }
        $tapestry = new Tapestry($postId);

        return $tapestry->addLink($link);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Delete A Tapestry Link.
 *
 * @param object $request HTTP request
 *
 * @return object $response   HTTP response
 */
function deleteTapestryLink($request)
{
    $postId = $request['tapestryPostId'];
    $link = json_decode($request->get_body());
    try {
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        if (!TapestryHelpers::nodeIsDraft($link->target, $postId) && !TapestryHelpers::nodeIsDraft($link->source, $postId)) {
            if (!TapestryHelpers::userIsAllowed('ADD', $link->source, $postId)) {
                throw new TapestryError('DELETE_LINK_PERMISSION_DENIED');
            }
            if (!TapestryHelpers::userIsAllowed('ADD', $link->target, $postId)) {
                throw new TapestryError('DELETE_LINK_PERMISSION_DENIED');
            }
        }
        $tapestry = new Tapestry($postId);

        return $tapestry->removeLink($link);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Update Tapestry Settings.
 *
 * @param object $request HTTP request
 *
 * @return object $response   HTTP response
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
 * Update Tapestry Node.
 *
 * @param object $request HTTP request
 *
 * @return object $response   HTTP response
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
        if (!TapestryHelpers::userIsAllowed('EDIT', $nodeMetaId, $postId)) {
            throw new TapestryError('EDIT_NODE_PERMISSION_DENIED');
        }
        if (!TapestryHelpers::isChildNodeOfTapestry($nodeMetaId, $postId)) {
            throw new TapestryError('INVALID_CHILD_NODE');
        }
        if (TapestryHelpers::nodeIsDraft($nodeMetaId, $postId) &&
            !TapestryHelpers::nodeNeighbourIsPublished($nodeMetaId, $postId)) {
            throw new TapestryError('NODE_APPROVAL_DENIED');
        }

        $tapestry = new Tapestry($postId);
        $node = $tapestry->getNode($nodeMetaId);

        $node->set((object) $nodeData);

        return $node->save();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Delete Tapestry Node.
 *
 * @param object $request HTTP request
 *
 * @return object $response   HTTP response
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
        if (!TapestryHelpers::userIsAllowed('EDIT', $nodeMetaId, $postId)) {
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
 * Update Tapestry Node Size.
 *
 * @param object $request HTTP request
 *
 * @return object $response   HTTP response
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
        if (!TapestryHelpers::userIsAllowed('EDIT', $nodeMetaId, $postId)) {
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
 * Update Tapestry Node Description.
 *
 * @param object $request HTTP request
 *
 * @return object $response   HTTP response
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
        if (!TapestryHelpers::userIsAllowed('EDIT', $nodeMetaId, $postId)) {
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
 * Update Tapestry Node Title.
 *
 * @param object $request HTTP request
 *
 * @return object $response   HTTP response
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
        if (!TapestryHelpers::userIsAllowed('EDIT', $nodeMetaId, $postId)) {
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
 * Update Tapestry Node Image Url.
 *
 * @param object $request HTTP request
 *
 * @return object $response   HTTP response
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
        if (!TapestryHelpers::userIsAllowed('EDIT', $nodeMetaId, $postId)) {
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
 * Update Tapestry Node Locked Image Url.
 *
 * @param object $request HTTP request
 *
 * @return object $response   HTTP response
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
        if (!TapestryHelpers::userIsAllowed('EDIT', $nodeMetaId, $postId)) {
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

// takes an old thumbnail and optimizes it
function optimizeTapestryNodeThumbnails($request)
{
    $postId = $request['tapestryPostId'];

    // TODO: JSON validations should happen here
    // make sure the permissions body exists and not null
    try {
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }

        $tapestry = new Tapestry($postId);

        foreach ($tapestry->getNodeIds() as $nodeMetaId) {
            $node = $tapestry->getNode($nodeMetaId);
            $nodeData = $node->get();
            $protocol = is_ssl() ? "https:" : "http:";
    
            if ($nodeData->imageURL) {
                $urlPrepend = substr($nodeData->imageURL, 0, 4) === "http" ? "" : $protocol;
                $attachmentId = TapestryHelpers::attachImageByURL($urlPrepend . $nodeData->imageURL);
                $node->set((object) ['thumbnailFileId' => $attachmentId]);
                $node->save();
            }
            if ($nodeData->lockedImageURL) {
                $urlPrepend = substr($nodeData->lockedImageURL, 0, 4) === "http" ? "" : $protocol;
                $attachmentId = TapestryHelpers::attachImageByURL($urlPrepend . $nodeData->lockedImageURL);
                $node->set((object) ['lockedThumbnailFileId' => $attachmentId]);
                $node->save();
            }
        }
        return true;
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Update Tapestry Node Type Data.
 *
 * @param object $request HTTP request
 *
 * @return object $response   HTTP response
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
        if (!TapestryHelpers::userIsAllowed('EDIT', $nodeMetaId, $postId)) {
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
 * Update Tapestry Node Coordinates.
 *
 * @param object $request HTTP request
 *
 * @return object $response   HTTP response
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
        if (!TapestryHelpers::userIsAllowed('EDIT', $nodeMetaId, $postId) &&
            !TapestryHelpers::userIsAllowed('MOVE', $nodeMetaId, $postId)) {
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
 * Return whether tapestry node has draft neighbours.
 */
function getTapestryNodeHasDraftChildren($request)
{
    $postId = $request['tapestryPostId'];
    $nodeMetaId = $request['nodeMetaId'];

    try {
        if ($postId && !TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        if (!TapestryHelpers::isValidTapestryNode($nodeMetaId)) {
            throw new TapestryError('INVALID_NODE_META_ID');
        }
        if (!TapestryHelpers::userIsAllowed('EDIT', $nodeMetaId, $postId)) {
            throw new TapestryError('EDIT_NODE_PERMISSION_DENIED');
        }
        if (!TapestryHelpers::isChildNodeOfTapestry($nodeMetaId, $postId)) {
            throw new TapestryError('INVALID_CHILD_NODE');
        }

        $tapestry = new Tapestry($postId);
        $links = $tapestry->getLinks();
        $response = array(
            "hasDraft" => false,
        );

        foreach ($links as $link) {
            if ($link->source == $nodeMetaId || $link->target == $nodeMetaId) {
                $neighbour = new TapestryNode($postId, $link->source == $nodeMetaId ? $link->target : $link->source);
                if ($neighbour->getMeta()->status == "draft") {
                    $response["hasDraft"] = true;
                }
            }
        }

        return $response;
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Update a single node progress for the current user by passing in node id, post id and progress value
 * Example: /wp-json/tapestry-tool/v1/users/progress?post_id=44&node_id=1&progress_value=0.2.
 *
 * @param object $request HTTP request
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
 * Example: /wp-json/tapestry-tool/v1/users/completed?post_id=44&node_id=1.
 *
 * @param object $request HTTP request
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
 * Set activity as completed for the current user
 * Example: /wp-json/tapestry-tool/v1/users/activity?post_id=44&node_id=1&question_id=abcd.
 *
 * @param object $request HTTP request
 *
 * @return null
 */
function completeQuestionById($request)
{
    $postId = $request['post_id'];
    $nodeMetaId = $request['node_id'];
    $questionId = $request['question_id'];
    $body = json_decode($request->get_body());
    $answerData = $body->answer;
    $answerType = $body->answerType;

    try {
        $userProgress = new TapestryUserProgress($postId, $nodeMetaId);
        $userProgress->completeQuestion($questionId, $answerType, $answerData);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Get user h5p video setting on a tapestry page by post id. Will need to pass these as query parameters
 * Example: /wp-json/tapestry-tool/v1/users/h5psettings/42.
 *
 * @param object $request HTTP request
 *
 * @return object $response HTTP response
 */
function getUserH5PSettingsByPostId($request)
{
    $postId = $request['tapestryPostId'];
    try {
        $userProgress = new TapestryUserProgress($postId);

        return $userProgress->getH5PSettings();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

function updateUserSettings($request)
{
    $userSettings = $request->get_body();
    try {
        $userProgress = new TapestryUserProgress();
        $userProgress->updateUserSettings($userSettings);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}


function getUserAvatar($request)
{
    try {
        $userProgress = new TapestryUserProgress();
        return $userProgress->getAvatar();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Update the user's h5p settings by post id.
 *
 * Example endpoint: /wp-json/tapestry-tool/v1/users/h5psettings/44
 * Example body:
 * {
 *  "volume": 100,
 *  "muted": false,
 *  "caption": null,
 *  "quality": "q1",
 *  "playbackRate": 0.5,
 *  "time": 11.934346
 * }
 *
 * @param object $request HTTP request
 */
function updateUserH5PSettingsByPostId($request)
{
    $postId = $request['tapestryPostId'];
    $h5pSettingsData = $request->get_body();
    try {
        $userProgress = new TapestryUserProgress($postId);
        $userProgress->updateH5PSettings($h5pSettingsData);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Get user progress on a tapestry page by post id.
 * Example: /wp-json/tapestry-tool/v1/users/progress?post_id=44.
 *
 * @param object $request HTTP request
 *
 * @return object $response HTTP response
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
 * Saves a user's recorded audio file.
 *
 * @param object $request HTTP request
 *
 * @return object $response HTTP response
 */
function postUserAudio($request)
{
    $postId = $request['tapestryPostId'];
    $nodeMetaId = $request['nodeMetaId'];
    $body = json_decode($request->get_body());
    $questionId = $body->questionId;
    $audio = $body->audio;

    try {
        if (!TapestryHelpers::isValidTapestry($postId)) {
            throw new TapestryError('INVALID_POST_ID');
        }
        if (!TapestryHelpers::isValidTapestryNode($nodeMetaId)) {
            throw new TapestryError('INVALID_NODE_META_ID');
        }
        if (!is_string($audio) || empty($audio)) {
            throw new TapestryError('INVALID_AUDIO');
        }
        if (0 == wp_get_current_user()->ID) {
            throw new TapestryError('INVALID_USER_ID');
        }
        $TapestryAudio = new TapestryAudio($postId, $nodeMetaId, $questionId);

        return $TapestryAudio->save($audio);
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

/**
 * Get user favourite nodes on a tapestry page by post id.
 * Example: /wp-json/tapestry-tool/v1/users/favourites?post_id=44.
 *
 * @param object $request HTTP request
 *
 * @return object $response HTTP response
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
 * Example: /wp-json/tapestry-tool/v1/users/progress?post_id=44&favourites=[409, 411].
 *
 * @param object $request HTTP request
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

function getTapestryContributors($request)
{
    $postId = $request['tapestryPostId'];
    $user = new TapestryUser();

    try {
        if (!$user->canEdit($postId)) {
            throw new TapestryError('TAPESTRY_PERMISSION_DENIED');
        }
        $tapestry = new Tapestry($postId);

        return $tapestry->getAllContributors();
    } catch (TapestryError $e) {
        return new WP_Error($e->getCode(), $e->getMessage(), $e->getStatus());
    }
}

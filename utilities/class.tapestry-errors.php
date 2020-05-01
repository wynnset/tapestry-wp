<?php

/**
 * Tapestry Errors
 */
class TapestryError extends Error
{
    protected $code;
    protected $message;
    protected $status;

    const ERRORS = [
        'INVALID_USER_ID' => [
            'MESSAGE'   => 'UserID is invalid or user is not logged in.',
            'STATUS'    => ['status' => 404]
        ],
        'INVALID_AUDIO' => [
            'MESSAGE'   => 'Audio is invalid.',
            'STATUS'    => ['status' => 404]
        ],
        'INVALID_POST_ID' => [
            'MESSAGE'   => 'PostID is invalid',
            'STATUS'    => ['status' => 404]
        ],
        'INVALID_NODE_TYPE' => [
            'MESSAGE'   => 'Node type is invalid',
            'STATUS'    => ['status' => 406]
        ],
        'INVALID_NODE_META_ID' => [
            'MESSAGE'   => 'NodeMetaId is invalid',
            'STATUS'    => ['status' => 404]
        ],
        'INVALID_GROUP_META_ID' => [
            'MESSAGE'   => 'GroupMetaId is invalid',
            'STATUS'    => ['status' => 404]
        ],
        'GROUP_ALREADY_EXISTS' => [
            'MESSAGE'   => 'Group already exists in the database',
            'STATUS'    => ['status' => 400]
        ],
        'NODE_ALREADY_EXISTS' => [
            'MESSAGE'   => 'Node already exists in the database',
            'STATUS'    => ['status' => 400]
        ],
        'INVALID_CHILD_NODE' => [
            'MESSAGE'   => 'Node is not a child of the tapestry',
            'STATUS'    => ['status' => 400]
        ],
        'SETTINGS_MISSING_IN_NEW_TAPESTRY' => [
            'MESSAGE'   => 'Settings are required to create a new Tapestry',
            'STATUS'    => ['status' => 400]
        ],
        'INVALID_NEW_LINK' => [
            'MESSAGE'   => 'New Tapestry link is invalid',
            'STATUS'    => ['status' => 400]
        ],
        'NODES_EXIST_IN_NEW_TAPESTRY' => [
            'MESSAGE'   => 'Nodes should not be passed in when creating a new Tapestry',
            'STATUS'    => ['status' => 400]
        ],
        'GROUPS_EXIST_IN_NEW_TAPESTRY' => [
            'MESSAGE'   => 'Groups should not be passed in when creating a new Tapestry',
            'STATUS'    => ['status' => 400]
        ],
        'LINKS_EXIST_IN_NEW_TAPESTRY' => [
            'MESSAGE'   => 'Links should not be passed in when creating a new Tapestry',
            'STATUS'    => ['status' => 400]
        ],
        'ADD_NODE_PERMISSION_DENIED' => [
            'MESSAGE'   => 'You are not permitted to add child nodes to this node',
            'STATUS'    => ['status' => 403]
        ],
        'EDIT_NODE_PERMISSION_DENIED' => [
            'MESSAGE'   => 'You are not permitted to edit this node',
            'STATUS'    => ['status' => 403]
        ],
        'EDIT_TAPESTRY_PERMISSION_DENIED' => [
            'MESSAGE'   => 'You are not permitted to edit this tapestry',
            'STATUS'    => ['status' => 403]
        ],
        'POST_ID_ALREADY_SET' => [
            'MESSAGE'   => 'PostID should not be passed in when creating a new Tapestry',
            'STATUS'    => ['status' => 500]
        ],
        'FAILED_TO_SAVE_AUDIO' => [
            'MESSAGE'   => 'Failed to save audio to the server.',
            'STATUS'    => ['status' => 500]
        ],
        'CANNOT_DELETE_ROOT' => [
            'MESSAGE'   => 'Root node can only be deleted if there are no other nodes in the tapestry',
            'STATUS'    => ['status' => 400]
        ],
        'INVALID_PROGRESS' => [
            'MESSAGE'   => 'Progress must be a value between 0 and 1',
            'STATUS'    => ['status' => 400]
        ]
    ];

    /**
     * Constructor
     * 
     * @param   String  $code   error code
     * 
     * @return  NULL
     */
    public function __construct($code)
    {
        $ERROR = (object) self::ERRORS[$code];
        $this->code = $code;
        $this->message = $ERROR->MESSAGE;
        $this->status = $ERROR->STATUS;
    }

    /**
     * Get Error Status
     * 
     * @return  $status
     */
    public function getStatus()
    {
        return $this->status;
    }
}

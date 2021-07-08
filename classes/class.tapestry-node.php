<?php

require_once dirname(__FILE__).'/../utilities/class.tapestry-errors.php';
require_once dirname(__FILE__).'/../utilities/class.tapestry-helpers.php';
require_once dirname(__FILE__).'/../interfaces/interface.tapestry-node.php';
require_once dirname(__FILE__).'/../classes/class.tapestry-user-progress.php';
require_once dirname(__FILE__).'/../classes/class.constants.php';

/**
 * Add/update/retrieve Tapestry post and its child nodes.
 */
class TapestryNode implements ITapestryNode
{
    private $tapestryPostId;
    private $nodePostId;
    private $nodeMetaId;
    private $author;
    private $conditions;
    private $type;
    private $size;
    private $title;
    private $status;
    private $reviewStatus;
    private $behaviour;
    private $typeData;
    private $thumbnailFileId;
    private $lockedThumbnailFileId;
    private $imageURL;
    private $lockedImageURL;
    private $mediaType;
    private $mediaFormat;
    private $mediaDuration;
    private $description;
    private $coordinates;
    private $permissions;
    private $hideTitle;
    private $hideProgress;
    private $hideMedia;
    private $backgroundColor;
    private $textColor;
    private $skippable;
    private $fullscreen;
    private $childOrdering;
    private $fitWindow;
    private $reviewComments;
    private $license;
    private $references;
    private $mapCoordinates;
    private $isDyad;
    private $popup;

    /**
     * Constructor.
     *
     * @param Number $tapestryPostId tapestry post ID
     * @param Number $nodeMetaId     node meta ID
     *
     * @return null
     */
    public function __construct($tapestryPostId = 0, $nodeMetaId = 0)
    {
        $this->tapestryPostId = (int) $tapestryPostId;
        $this->nodePostId = 0;
        $this->nodeMetaId = (int) $nodeMetaId;

        $this->author = $this->_getAuthorInfo(wp_get_current_user()->ID);
        $this->conditions = [];
        $this->size = '';
        $this->title = '';
        $this->status = '';
        $this->thumbnailFileId = '';
        $this->lockedThumbnailFileId = '';
        $this->reviewStatus = '';
        $this->imageURL = '';
        $this->lockedImageURL = '';
        $this->mediaType = '';
        $this->presentationStyle = '';
        $this->mediaFormat = '';
        $this->mediaDuration = 0;
        $this->description = '';
        $this->behaviour = 'new-window';
        $this->type = 'tapestry_node';
        $this->typeData = (object) [];
        $this->coordinates = (object) [];
        $this->permissions = TapestryNodePermissions::getDefaultNodePermissions($tapestryPostId);
        $this->hideTitle = false;
        $this->hideProgress = false;
        $this->hideMedia = false;
        $this->backgroundColor = '#8396a1';
        $this->textColor = 'white';
        $this->skippable = true;
        $this->fullscreen = false;
        $this->childOrdering = [];
        $this->fitWindow = true;
        $this->reviewComments = [];
        $this->license = '';
        $this->references = '';
        $this->mapCoordinates = (object) [
            'lat' => '',
            'lng' => '',
        ];
        $this->isDyad = false;
        $this->popup = null;

        if (TapestryHelpers::isValidTapestryNode($this->nodeMetaId)) {
            $node = $this->_loadFromDatabase();
            $this->set($node);
            $this->author = $this->_getAuthorInfo(get_post_field('post_author', $this->nodePostId));
        }
    }

    /**
     * Save the Tapestry node.
     *
     * @return object $node
     */
    public function save()
    {
        return $this->_saveToDatabase();
    }

    /**
     * Set Node.
     *
     * @param object $node node
     *
     * @return null
     */
    public function set($node)
    {
        if (isset($node->type) && is_string($node->type)) {
            $this->type = $node->type;
        }
        if (isset($node->author) && is_object($node->author)) {
            $this->author = $node->author;
        }
        if (isset($node->size) && is_string($node->size)) {
            $this->size = $node->size;
        }
        if (isset($node->title) && is_string($node->title)) {
            $this->title = $node->title;
        }
        if (isset($node->status) && is_string($node->status)) {
            $this->status = $node->status;
        }
        if (isset($node->reviewStatus) && is_string($node->reviewStatus)) {
            $this->reviewStatus = $node->reviewStatus;
        }
        if (isset($node->behaviour) && is_string($node->behaviour)) {
            $this->behaviour = $node->behaviour;
        }
        if (isset($node->typeData) && is_object($node->typeData)) {
            $this->typeData = $node->typeData;
        }
        if (isset($node->imageURL) && is_string($node->imageURL)) {
            $this->imageURL = $node->imageURL;
        }
        if (isset($node->thumbnailFileId) && (is_numeric($node->thumbnailFileId) || is_string($node->thumbnailFileId))) {
            if (is_string($node->thumbnailFileId) && '' == $node->thumbnailFileId) {
                $this->imageURL = '';
                $this->thumbnailFileId = '';
            } else {
                $this->thumbnailFileId = $node->thumbnailFileId;
                set_post_thumbnail($this->nodePostId, $this->thumbnailFileId);
                $post_thumbnail_url = get_the_post_thumbnail_url($this->nodePostId, 'tapestry_thumb');
                if ($post_thumbnail_url) {
                    $this->imageURL = $post_thumbnail_url;
                }
            }
        }
        if (isset($node->lockedImageURL) && is_string($node->lockedImageURL)) {
            $this->lockedImageURL = $node->lockedImageURL;
        }
        if (isset($node->lockedThumbnailFileId) && (is_numeric($node->lockedThumbnailFileId) || is_string($node->lockedThumbnailFileId))) {
            if (is_string($node->lockedThumbnailFileId) && '' == $node->lockedThumbnailFileId) {
                $this->lockedImageURL = '';
                $this->lockedThumbnailFileId = '';
            } else {
                $this->lockedThumbnailFileId = $node->lockedThumbnailFileId;
                $image_url = wp_get_attachment_image_url($this->lockedThumbnailFileId, 'tapestry_thumb');
                if ($image_url) {
                    $this->lockedImageURL = $image_url;
                }
            }
        }

        if (isset($node->mediaType) && is_string($node->mediaType)) {
            $this->mediaType = $node->mediaType;
        }
        if (isset($node->presentationStyle) && is_string($node->presentationStyle)) {
            $this->presentationStyle = $node->presentationStyle;
        }
        if (isset($node->mediaFormat) && is_string($node->mediaFormat)) {
            $this->mediaFormat = $node->mediaFormat;
        }
        if (isset($node->mediaDuration) && is_numeric($node->mediaDuration)) {
            $this->mediaDuration = $node->mediaDuration;
        }
        if (isset($node->description) && is_string($node->description)) {
            $this->description = $node->description;
        }
        if (isset($node->coordinates) && is_object($node->coordinates)) {
            $this->coordinates = $node->coordinates;
        }
        if (isset($node->permissions) && is_object($node->permissions)) {
            $this->permissions = $node->permissions;
        }
        if (isset($node->hideTitle) && is_bool($node->hideTitle)) {
            $this->hideTitle = $node->hideTitle;
        }
        if (isset($node->isDyad) && is_bool($node->isDyad)) {
            $this->isDyad = $node->isDyad;
        }
        if (isset($node->hideProgress) && is_bool($node->hideProgress)) {
            $this->hideProgress = $node->hideProgress;
        }
        if (isset($node->hideMedia) && is_bool($node->hideMedia)) {
            $this->hideMedia = $node->hideMedia;
        }
        if (isset($node->backgroundColor) && is_string($node->backgroundColor)) {
            $this->backgroundColor = $node->backgroundColor;
        }
        if (isset($node->textColor) && is_string($node->textColor)) {
            $this->textColor = $node->textColor;
        }
        if (isset($node->skippable) && is_bool($node->skippable)) {
            $this->skippable = $node->skippable;
        }
        if (isset($node->fullscreen) && is_bool($node->fullscreen)) {
            $this->fullscreen = $node->fullscreen;
        }
        if (isset($node->conditions) && is_array($node->conditions)) {
            $this->conditions = $node->conditions;
        }
        if (isset($node->childOrdering) && is_array($node->childOrdering)) {
            $this->childOrdering = $node->childOrdering;
        }
        if (isset($node->fitWindow) && is_bool($node->fitWindow)) {
            $this->fitWindow = $node->fitWindow;
        }
        if (isset($node->reviewComments) && is_array($node->reviewComments)) {
            $this->reviewComments = $node->reviewComments;
        }
        if (isset($node->license) && is_object($node->license)) {
            $this->license = $node->license;
        }
        if (isset($node->references) && is_string($node->references)) {
            $this->references = $node->references;
        }
        if (isset($node->mapCoordinates) && is_object($node->mapCoordinates)) {
            $this->mapCoordinates = $node->mapCoordinates;
        }
        if (property_exists($node, 'popup')) {
            $this->popup = $node->popup;
        }
    }

    /**
     * Get the node.
     *
     * @return $node node
     */
    public function get()
    {
        if (!$this->nodeMetaId) {
            throw new TapestryError('INVALID_NODE_META_ID');
        }

        return $this->_formNode();
    }

    /**
     * Delete a node.
     *
     * @return null
     */
    public function deleteNode()
    {
        if (!$this->nodeMetaId) {
            throw new TapestryError('INVALID_NODE_META_ID');
        }

        $this->_deleteNodeFromDatabase();
    }

    /**
     * Update node conditions by removing conditions by nodeId.
     *
     * @param string $nodeId nodeId
     *
     * @return null
     */
    public function removeConditionsById($nodeId)
    {
        $listModified = false;
        foreach ($this->conditions as $conditionId => $condition) {
            if ($condition->nodeId == $nodeId) {
                array_splice($this->conditions, $conditionId, 1);
                $listModified = true;
            }
        }
        if ($listModified) {
            $this->_saveToDatabase();
        }
    }

    public function getLockedState($userId = 0)
    {
        $conditions = $this->conditions;
        $userProgress = new TapestryUserProgress($this->tapestryPostId, $this->nodeMetaId);

        foreach ($conditions as $condition) {
            $condition->fulfilled = false;
        }

        foreach ($conditions as $condition) {
            switch ($condition->type) {
                case ConditionTypes::NODE_COMPLETED:
                    if ($userId && $userProgress->isCompleted($condition->nodeId, $userId)) {
                        $condition->fulfilled = true;
                    }
                    break;
                case ConditionTypes::DATE_NOT_PASSED:
                    if (new DateTime() <= new DateTime($condition->date.' '.$condition->time, new DateTimeZone($condition->timezone))) {
                        $condition->fulfilled = true;
                    }
                    break;
                case ConditionTypes::DATE_PASSED:
                    if (new DateTime() >= new DateTime($condition->date.' '.$condition->time, new DateTimeZone($condition->timezone))) {
                        $condition->fulfilled = true;
                    }
                    break;
                default:
                    break;
            }
        }

        return $conditions;
    }

    public function isLocked($userId = 0)
    {
        $conditions = $this->getLockedState($userId);

        $numFulfilled = 0;
        foreach ($conditions as $condition) {
            if ($condition->fulfilled) {
                ++$numFulfilled;
            }
        }

        return $numFulfilled !== count($conditions);
    }

    public function getMeta()
    {
        $node = $this->get();
        $node->typeData = (object) [
            'progress' => [
                0 => [
                    'group' => 'viewed',
                    'value' => 0,
                ],
                1 => [
                    'group' => 'unviewed',
                    'value' => 1,
                ],
            ],
        ];

        return $node;
    }

    public function isAvailableToUser($userId = 0)
    {
        $nodeMeta = $this->getMeta();

        return $this->isAuthor($userId) || NodeStatus::DRAFT != $nodeMeta->status;
    }

    public function isAuthor($userId = 0)
    {
        if (!$userId) {
            $userId = wp_get_current_user()->ID;
        }
        $nodeMeta = $this->getMeta();

        return $nodeMeta->author->id == $userId;
    }

    public function addReview($comments)
    {
        if (NodeStatus::PUBLISH === $this->status) {
            throw new TapestryError('REVIEW_PERMISSION_DENIED', "You're not allowed to review published nodes", 403);
        }

        $canEditTapestry = current_user_can('edit_post', $this->tapestryPostId);
        if (!$canEditTapestry && !$this->isAuthor()) {
            throw new TapestryError('REVIEW_PERMISSION_DENIED', "You're not allowed to review this node", 403);
        }

        // Validate _all_ comments before adding them in
        foreach ($comments as $comment) {
            $this->_validateComment($comment);
        }

        foreach ($comments as $comment) {
            if (CommentTypes::STATUS_CHANGE === $comment->type) {
                $this->reviewStatus = $comment->to;
                if (NodeStatus::ACCEPT === $comment->to) {
                    $this->status = NodeStatus::PUBLISH;
                }
            }
            array_push($this->reviewComments, $comment);
        }

        $this->save();

        return (object) [
            'status' => $this->status,
            'reviewStatus' => $this->reviewStatus,
            'reviewComments' => $this->reviewComments,
        ];
    }

    private function _validateComment($review)
    {
        $canEditTapestry = current_user_can('edit_post', $this->tapestryPostId);

        if (!isset($review->type)) {
            throw new TapestryError('INVALID_REVIEW', "Missing property 'type'", 400);
        }

        if (!isset($review->timestamp)) {
            throw new TapestryError('INVALID_REVIEW', "Missing property 'timestamp'", 400);
        }

        switch ($review->type) {
            case CommentTypes::COMMENT:
                if (!isset($review->comment) || !is_string($review->comment) || 0 === strlen($review->comment)) {
                    throw new TapestryError('INVALID_REVIEW_COMMENT', 'A review comment must be a non-empty string.', 400);
                }
                break;
            case CommentTypes::STATUS_CHANGE:
                $validStatuses = [NodeStatus::SUBMIT, NodeStatus::REJECT, NodeStatus::ACCEPT];

                if (!in_array($review->to, $validStatuses)) {
                    $message = sprintf(
                        'Invalid node status %s. A node status can only be changed to %s, %s, or %s.',
                        $review->to,
                        NodeStatus::SUBMIT,
                        NodeStatus::REJECT,
                        NodeStatus::ACCEPT
                    );
                    throw new TapestryError('INVALID_REVIEW_STATUS', $message, 400);
                }

                if (!$canEditTapestry && NodeStatus::SUBMIT !== $review->to) {
                    throw new TapestryError('REVIEW_PERMISSION_DENIED', "You're not allowed to accept or reject this node.", 403);
                }
                break;
            default:
                $message = sprintf(
                    'Unknown review type %s. A review type can only be one of %s or %s.',
                    $review->type,
                    CommentTypes::COMMENT,
                    CommentTypes::STATUS_CHANGE
                );
                throw new TapestryError('INVALID_REVIEW', $message, 400);
        }
    }

    private function _saveToDatabase()
    {
        $node = $this->_formNode();

        $nodePostId = 0;

        if ($this->nodeMetaId) {
            $nodeMetadata = get_metadata_by_mid('post', $this->nodeMetaId);
            $nodePostId = $nodeMetadata->meta_value->post_id;
        }

        $nodePostId = TapestryHelpers::updatePost($node, 'tapestry_node', $nodePostId, $node->author->id);

        $nodeMetadata = $this->_makeMetadata($node, $nodePostId);

        if ($this->nodeMetaId) {
            update_metadata_by_mid('post', $this->nodeMetaId, $nodeMetadata);
        } else {
            $this->nodeMetaId = add_post_meta($this->tapestryPostId, 'tapestry_node', $nodeMetadata);
            $node->id = $this->nodeMetaId;
        }

        // TODO: add a check permission to see if the user is allowed to edit
        // the "original" node data
        update_post_meta($nodePostId, 'tapestry_node_data', $node);

        $this->_resetAuthor();

        return $node;
    }

    private function _resetAuthor()
    {
        wp_update_post([
            'ID' => $this->nodePostId,
            'post_author' => $this->author->id,
        ]);
    }

    private function _loadFromDatabase()
    {
        $nodeMetadata = get_metadata_by_mid('post', $this->nodeMetaId);

        if (empty($nodeMetadata)) {
            return (object) [];
        }

        $nodePostId = $nodeMetadata->meta_value->post_id;
        $this->nodePostId = $nodePostId;

        $nodeData = get_post_meta($nodePostId, 'tapestry_node_data', true);

        return $this->_formNodeData($nodeData, $nodeMetadata);
    }

    private function _deleteNodeFromDatabase()
    {
        $nodeMetadata = get_metadata_by_mid('post', $this->nodeMetaId);
        if (empty($nodeMetadata)) {
            return;
        }
        $nodePostId = $nodeMetadata->meta_value->post_id;
        delete_post_meta($nodePostId, 'tapestry_node_data');
        delete_metadata_by_mid('post', $this->nodeMetaId);
    }

    private function _formNode()
    {
        if ('video' == $this->mediaType && 'h5p' == $this->mediaFormat) {
            $this->mediaType = 'h5p';
        }
        if ('accordion' == $this->mediaType) {
            $this->mediaType = 'multi-content';
            $this->presentationStyle = 'accordion';
        }

        return (object) [
            'id' => $this->nodeMetaId,
            'postId' => $this->nodePostId,
            'author' => $this->author,
            'type' => $this->type,
            'size' => $this->size,
            'title' => $this->title,
            'status' => $this->status,
            'thumbnailFileId' => $this->thumbnailFileId,
            'lockedThumbnailFileId' => $this->lockedThumbnailFileId,
            'reviewStatus' => $this->reviewStatus,
            'imageURL' => $this->imageURL,
            'lockedImageURL' => $this->lockedImageURL,
            'mediaType' => $this->mediaType,
            'presentationStyle' => $this->presentationStyle,
            'mediaFormat' => $this->mediaFormat,
            'mediaDuration' => $this->mediaDuration,
            'description' => $this->description,
            'behaviour' => $this->behaviour,
            'typeData' => $this->typeData,
            'coordinates' => $this->coordinates,
            'permissions' => $this->permissions,
            'hideTitle' => $this->hideTitle,
            'hideProgress' => $this->hideProgress,
            'hideMedia' => $this->hideMedia,
            'backgroundColor' => $this->backgroundColor,
            'textColor' => $this->textColor,
            'skippable' => $this->skippable,
            'fullscreen' => $this->fullscreen,
            'conditions' => $this->conditions,
            'childOrdering' => $this->childOrdering,
            'fitWindow' => $this->fitWindow,
            'reviewComments' => $this->reviewComments,
            'license' => $this->license,
            'references' => $this->references,
            'mapCoordinates' => $this->mapCoordinates,
            'isDyad' => $this->isDyad,
            'popup' => $this->popup,
        ];
    }

    private function _makeMetadata($node, $nodePostId)
    {
        return (object) [
            'post_id' => $nodePostId,
            'title' => $node->title,
            'permissions' => $node->permissions,
            'coordinates' => $node->coordinates,
        ];
    }

    private function _formNodeData($nodeData, $nodeMetadata)
    {
        // Update node data here to match its own version
        // This enables the same node to have multiple versions
        $nodeData->id = (int) $nodeMetadata->meta_id;
        if (isset($nodeMetadata->meta_value->title)) {
            $nodeData->title = $nodeMetadata->meta_value->title;
        }
        if (isset($nodeMetadata->meta_value->coordinates->x)) {
            $nodeData->fx = $nodeMetadata->meta_value->coordinates->x;
        }
        if (isset($nodeMetadata->meta_value->coordinates->y)) {
            $nodeData->fy = $nodeMetadata->meta_value->coordinates->y;
        }
        if (isset($nodeMetadata->meta_value->permissions)) {
            $nodeData->permissions = $nodeMetadata->meta_value->permissions;
        }
        if (isset($nodeMetadata->meta_value->typeData)) {
            $nodeData->typeData = $nodeMetadata->meta_value->typeData;
        }
        if (isset($nodeMetadata->meta_value->thumbnailFileId)) {
            $nodeData->thumbnailFileId = $nodeMetadata->meta_value->thumbnailFileId;
        }
        if (isset($nodeMetadata->meta_value->lockedThumbnailFileId)) {
            $nodeData->lockedThumbnailFileId = $nodeMetadata->meta_value->lockedThumbnailFileId;
        }
        if (isset($nodeMetadata->meta_value->imageURL)) {
            $nodeData->imageURL = $nodeMetadata->meta_value->imageURL;
        }
        if (isset($nodeMetadata->meta_value->lockedImageURL)) {
            $nodeData->lockedImageURL = $nodeMetadata->meta_value->lockedImageURL;
        }

        return $nodeData;
    }

    private function _getAuthorInfo($id)
    {
        if (!$id) {
            $id = 1;
        }
        $user = get_user_by('id', $id);
        if ($user) {
            return (object) [
                'id' => $id,
                'name' => $user->display_name,
                'email' => $user->user_email,
                'original_author_name' => '',
                'original_author_email' => '',
            ];
        }
    }
}

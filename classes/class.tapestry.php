<?php

require_once dirname(__FILE__).'/../utilities/class.tapestry-errors.php';
require_once dirname(__FILE__).'/../utilities/class.tapestry-helpers.php';
require_once dirname(__FILE__).'/../utilities/class.tapestry-user.php';
require_once dirname(__FILE__).'/../utilities/class.tapestry-node-permissions.php';
require_once dirname(__FILE__).'/../classes/class.tapestry-h5p.php';
require_once dirname(__FILE__).'/../classes/class.constants.php';
require_once dirname(__FILE__).'/../interfaces/interface.tapestry.php';
require_once dirname(__FILE__).'/class.constants.php';

/**
 * Add/update/retrieve a Tapestry.
 */
class Tapestry implements ITapestry
{
    private $postId;
    private $author;
    private $groups;
    private $links;
    private $settings;
    private $rootId;
    private $nodes;

    private $updateTapestryPost = true;

    /**
     * Constructor.
     *
     * @param Number $postId post ID
     *
     * @return null
     */
    public function __construct($postId = 0)
    {
        $this->postId = (int) $postId;
        $this->author = (int) $this->_getAuthor();

        $this->nodes = [];
        $this->links = [];
        $this->groups = [];
        $this->rootId = 0;
        $this->settings = $this->_getDefaultSettings();

        if (TapestryHelpers::isValidTapestry($this->postId)) {
            $tapestry = $this->_loadFromDatabase();
            $this->set($tapestry);
        }
    }

    /**
     * Save the Tapestry.
     *
     * @return object $tapestry
     */
    public function save()
    {
        $this->updateTapestryPost = true;

        return $this->_saveToDatabase();
    }

    /**
     * Save the Tapestry automatically on publish.
     *
     * @return object $tapestry
     */
    public function saveOnPublish()
    {
        $this->updateTapestryPost = false;

        return $this->_saveToDatabase();
    }

    /**
     * Set Tapestry.
     *
     * @param object $tapestry tapestry
     *
     * @return null
     */
    public function set($tapestry)
    {
        if (isset($tapestry->rootId) && is_numeric($tapestry->rootId)) {
            $this->rootId = $tapestry->rootId;
        }
        if (isset($tapestry->nodes) && is_array($tapestry->nodes)) {
            $this->nodes = $tapestry->nodes;
        }
        if (isset($tapestry->groups) && is_array($tapestry->groups)) {
            $this->groups = $tapestry->groups;
        }
        if (isset($tapestry->links) && is_array($tapestry->links)) {
            $this->links = $tapestry->links;
        }
        if (isset($tapestry->settings) && is_object($tapestry->settings)) {
            $this->settings = $tapestry->settings;
            if (!isset($this->settings->analyticsEnabled)) {
                $this->settings->analyticsEnabled = false;
            }
            if (!isset($this->settings->draftNodesEnabled)) {
                $this->settings->draftNodesEnabled = true;
                $this->settings->submitNodesEnabled = true;
            }
        }
    }

    /**
     * Retrieve a Tapestry post.
     *
     * @return object $tapestry
     */
    public function get($filterUserId)
    {
        if (!$this->postId) {
            throw new TapestryError('INVALID_POST_ID');
        }

        return $this->_getTapestry($filterUserId);
    }

    /**
     * Get node IDs.
     *
     * @return array $nodes  node ids
     */
    public function getNodeIds()
    {
        if (!$this->postId) {
            throw new TapestryError('INVALID_POST_ID');
        }

        return $this->nodes;
    }

    /**
     * Get links.
     *
     * @return array $links
     */
    public function getLinks()
    {
        if (!$this->postId) {
            throw new TapestryError('INVALID_POST_ID');
        }

        return $this->links;
    }

    /**
     * Add a new node.
     *
     * @param object $node Tapestry node
     *
     * @return object $node   Tapestry node
     */
    public function addNode($node)
    {
        $tapestryNode = new TapestryNode($this->postId);

        // Checks if user is logged in to prevent logged out user-0 from getting permissions
        // Only add user permissions if it is not a review node
        if (is_user_logged_in() && 0 === count($node->reviewComments)) {
            $userId = wp_get_current_user()->ID;
            $node->permissions->{'user-'.$userId} = ['read', 'add', 'edit'];
        }

        $tapestryNode->set($node);
        $node = $tapestryNode->save($node);

        array_push($this->nodes, $node->id);

        if (empty($this->rootId)) {
            $this->rootId = $node->id;
        }

        $nodeArray = [$node];
        $nodeArray = $this->_addH5PMeta($nodeArray);
        $node = array_values($nodeArray)[0];
        $node = $tapestryNode->save($node);

        $this->_saveToDatabase();

        return $node;
    }

    /**
     * Delete a node.
     *
     * @param object $nodeId Tapestry node id
     *
     * @return object $Array   Tapestry nodes
     */
    public function deleteNodeFromTapestry($nodeId)
    {
        // Remove the rootId field
        if ($nodeId == $this->rootId) {
            foreach ($this->nodes as $node) {
                if ($node !== $this->rootId && !TapestryHelpers::nodeIsDraft($node, $this->postId)) {
                    throw new TapestryError('CANNOT_DELETE_ROOT');
                }
            }
            $this->rootId = 0;
        }

        // Delete the element from nodes array
        foreach ($this->nodes as $elementId => $element) {
            if ($element == $nodeId) {
                array_splice($this->nodes, $elementId, 1);
                // Delete node from database
                $tapestryNode = new TapestryNode($this->postId, $nodeId);
                $tapestryNode->deleteNode();
            }
        }

        // Delete associated links with this node
        foreach ($this->links as $index => $link) {
            if ($link->source == $nodeId || $link->target == $nodeId) {
                $this->removeLink([
                    'source' => $link->source,
                    'target' => $link->target,
                    ]);
            }
        }

        // Delete condition from nodes that rely on this node
        foreach ($this->nodes as $index => $id) {
            if ($id != $nodeId) {
                // Delete condition from node and update database
                $elementNode = new TapestryNode($this->postId, $id);
                $elementNode->removeConditionsById($nodeId);
            }
        }

        $tapestry = $this->_formTapestry();
        update_post_meta($this->postId, 'tapestry', $tapestry);

        return $this->nodes;
    }

    /**
     * Add a new link.
     *
     * @param object $link Tapestry link
     *
     * @return object $link   Tapestry link
     */
    public function addLink($link)
    {
        array_push($this->links, $link);
        $this->_saveToDatabase();

        return $link;
    }

    /**
     * Delete a link from links array.
     *
     * @param int $link an array containing the node IDs that this connects
     *
     * @return array $links     Tapestry links
     */
    public function removeLink($linkToDelete)
    {
        foreach ($this->links as $linkIndex => $link) {
            if ($link->source == $linkToDelete->source && $link->target == $linkToDelete->target) {
                array_splice($this->links, $linkIndex, 1);
                break;
            }
        }
        $this->_saveToDatabase();

        return $this->links;
    }

    /**
     * Add a new group.
     *
     * @param object $group Tapestry group
     *
     * @return object $group   Tapestry group
     */
    public function addGroup($group)
    {
        $tapestryGroup = new TapestryGroup($this->postId);
        $tapestryGroup->set($group);
        $group = $tapestryGroup->save();

        array_push($this->groups, $group->id);
        $this->_saveToDatabase();

        return $group;
    }

    /**
     * Get the node controller with associated node meta ID.
     *
     * @param Number $nodeMetaId node meta ID
     *
     * @return object $node       node controller
     */
    public function getNode($nodeMetaId)
    {
        return new TapestryNode($this->postId, $nodeMetaId);
    }

    /**
     * Get the group controller with associated group meta ID.
     *
     * @param Number $groupMetaId group meta ID
     *
     * @return object $group          group controller
     */
    public function getGroup($groupMetaId)
    {
        return new TapestryNode($this->postId, $groupMetaId);
    }

    /**
     * Returns true if the tapestry is empty.
     *
     * @return bool true if there is no root node, false otherwise
     */
    public function isEmpty()
    {
        return empty($this->rootId);
    }

    public function setUnlocked($nodeIds, $userId = 0)
    {
        $nodes = $this->_setAccessibleStatus($nodeIds, $userId);

        return array_map(
            function ($nodeData) {
                $node = new TapestryNode($this->postId, $nodeData->id);
                $user = new TapestryUser();
                $data = $user->canEdit($this->postId) || $nodeData->accessible ? $node->get() : $node->getMeta();
                $data->accessible = $nodeData->accessible;
                $data->conditions = $nodeData->conditions;
                $data->unlocked = $nodeData->unlocked;

                return $data;
            },
            $nodes
        );
    }

    public function getAllContributors()
    {
        $authors = [];
        foreach ($this->nodes as $node) {
            $node = new TapestryNode($this->postId, $node);
            if ($node->isAvailableToUser()) {
                array_push($authors, $node->get()->author);
            }
        }

        return array_unique($authors, SORT_REGULAR);
    }

    /**
     * Retrieve a Tapestry post for export.
     *
     * @return object $tapestry
     */
    public function export()
    {
        $nodes = [];
        foreach ($this->nodes as $node) {
            $temp = (new TapestryNode($this->postId, $node))->get();
            if (NodeStatus::DRAFT == $temp->status) {
                continue;
            }
            $nodes[] = $temp;
        }
        $groups = [];
        foreach ($this->groups as $group) {
            $groups[] = (new TapestryGroup($this->postId, $$group))->get();
        }
        $parsedUrl = parse_url($this->settings->permalink);
        unset($this->settings->permalink);
        unset($this->settings->tapestrySlug);
        unset($this->settings->title);
        unset($this->settings->status);

        return (object) [
            'nodes' => $nodes,
            'groups' => $groups,
            'links' => $this->links,
            'settings' => $this->settings,
            'site-url' => $parsedUrl['scheme'].'://'.$parsedUrl['host'],
        ];
    }

    private function _setAccessibleStatus($nodes, $userId)
    {
        $newNodes = array_map(
            function ($nodeId) use ($userId) {
                $node = new TapestryNode($this->postId, $nodeId);
                $data = new stdClass();
                $data->id = $nodeId;
                $data->accessible = false;
                $data->unlocked = !$node->isLocked($userId);
                $data->conditions = $node->getLockedState($userId);

                return $data;
            },
            $nodes
        );
        if (count($newNodes)) {
            $root = null;
            foreach ($newNodes as $newNode) {
                if ($this->rootId === $newNode->id) {
                    $root = $newNode;
                    break;
                }
            }
            $this->_recursivelySetAccessible($root, [], $newNodes);
        }

        return $newNodes;
    }

    private function _recursivelySetAccessible($node, $visited, $nodeList)
    {
        if (!isset($node)) {
            return;
        }
        if (!in_array($node, $visited)) {
            array_push($visited, $node);
        }
        $node->accessible = $node->unlocked;
        if ($node->accessible) {
            $neighbourIds = $this->_getNeighbours($node);

            $neighbours = [];

            foreach ($neighbourIds as $nodeId) {
                foreach ($nodeList as $otherNode) {
                    if ($otherNode->id === $nodeId) {
                        array_push($neighbours, $otherNode);
                    }
                }
            }

            foreach ($neighbours as $neighbour) {
                if (!in_array($neighbour, $visited)) {
                    array_push($visited, $neighbour);
                    $this->_recursivelySetAccessible($neighbour, $visited, $nodeList);
                }
            }
        }
    }

    private function _getNeighbours($node)
    {
        $neighbourIds = [];

        foreach ($this->links as $link) {
            if ($link->source === $node->id || $link->target === $node->id) {
                array_push(
                    $neighbourIds,
                    $link->source === $node->id ? $link->target : $link->source
                );
            }
        }

        return $neighbourIds;
    }

    private function _loadFromDatabase()
    {
        $tapestry = get_post_meta($this->postId, 'tapestry', true);
        if (empty($tapestry)) {
            return $this->_getDefaultTapestry();
        }

        return $tapestry;
    }

    private function _getDefaultTapestry()
    {
        $tapestry = new stdClass();
        $tapestry->nodes = [];
        $tapestry->links = [];
        $tapestry->groups = [];
        $tapestry->rootId = 0;
        $tapestry->settings = $this->_getDefaultSettings();

        return $tapestry;
    }

    private function _getDefaultSettings()
    {
        $post = get_post($this->postId);
        $settings = new stdClass();
        $settings->tapestrySlug = $post->post_name;
        $settings->title = $post->post_title;
        $settings->status = $post->post_status;
        $settings->backgroundUrl = '';
        $settings->autoLayout = false;

        $settings->showAccess = true;
        $settings->showRejected = false;
        $settings->showAcceptedHighlight = true;
        $settings->defaultPermissions = TapestryNodePermissions::getDefaultNodePermissions($this->postId);
        $settings->superuserOverridePermissions = true;
        $settings->analyticsEnabled = false;
        $settings->draftNodesEnabled = true;
        $settings->submitNodesEnabled = true;
        $settings->permalink = get_permalink($this->postId);

        return $settings;
    }

    private function _getAuthor()
    {
        if ($this->postId) {
            return get_post_field('post_author', $this->postId);
        } else {
            return wp_get_current_user()->ID;
        }
    }

    private function _formTapestry()
    {
        return (object) [
            'nodes' => $this->nodes,
            'groups' => $this->groups,
            'links' => $this->links,
            'settings' => $this->settings,
            'rootId' => $this->rootId,
        ];
    }

    private function _saveToDatabase()
    {
        $tapestry = $this->_formTapestry();

        update_post_meta($this->postId, 'tapestry', $tapestry);

        return $tapestry;
    }

    private function _resetAuthor()
    {
        wp_update_post([
            'ID' => $this->postId,
            'post_author' => $this->author,
        ]);
    }

    private function _getTapestry($filterUserId)
    {
        $tapestry = $this->_filterTapestry($this->_formTapestry(), $filterUserId);

        $tapestry->nodes = $this->setUnlocked($tapestry->nodes);

        $tapestry->groups = array_map(
            function ($groupMetaId) {
                $tapestryGroup = new TapestryGroup($this->postId, $groupMetaId);

                return $tapestryGroup->get();
            },
            $tapestry->groups
        );

        $tapestry->nodes = $this->_addH5PMeta($tapestry->nodes);

        return $tapestry;
    }

    private function _filterTapestry($tapestry, $filterUserId)
    {
        $user = new TapestryUser();

        if (!isset($tapestry->settings->superuserOverridePermissions)) {
            $tapestry->settings->superuserOverridePermissions = true;
        }
        $tapestry->nodes = $this->_filterNodesMetaIdsByStatus($tapestry->nodes);

        if ($tapestry->settings->superuserOverridePermissions && $user->canEdit($this->postId)) {
            $tapestry->links = $this->_filterLinksByNodeMetaIds($tapestry->links, $tapestry->nodes);
        } else {
            $tapestry->nodes = array_intersect(
                $tapestry->nodes,
                $this->_filterNodeMetaIdsByPermissions(
                    $tapestry->rootId,
                    $tapestry->settings->superuserOverridePermissions,
                    $filterUserId
                )
            );
            $tapestry->links = $this->_filterLinksByNodeMetaIds($tapestry->links, $tapestry->nodes);
            $tapestry->groups = TapestryHelpers::getGroupIdsOfUser(wp_get_current_user()->ID, $this->postId);
        }

        return $tapestry;
    }

    private function _filterLinksByNodeMetaIds($links, $nodeMetaIds)
    {
        $newLinks = [];

        foreach ($links as $link) {
            if ((in_array($link->source, $nodeMetaIds))
                && (in_array($link->target, $nodeMetaIds))
            ) {
                array_push($newLinks, $link);
            }
        }

        return $newLinks;
    }

    private function _filterNodeMetaIdsByPermissions($rootId, $superuser_override, $secondaryUserId)
    {
        if (0 == $rootId) {
            return [];
        }

        $currentUserId = wp_get_current_user()->ID;
        $checked = [];
        $nodesPermitted = [];
        $this->_traverseNodes($rootId, $checked, $nodesPermitted, $superuser_override, $currentUserId, $secondaryUserId);

        return $nodesPermitted;
    }

    private function _addH5PMeta($nodes)
    {
        $controller = new TapestryH5P();
        $allH5Ps = $controller->get();
        foreach ($nodes as $i => $node) {
            if ('h5p' == $node->mediaType && $node->typeData->mediaURL) {
                $H5PURLParts = explode('&id=', $node->typeData->mediaURL);
                if (count($H5PURLParts) >= 2) {
                    $H5PId = $H5PURLParts[1];
                    $H5PIndex = array_search($H5PId, array_column($allH5Ps, 'id'));
                    if ($H5PIndex || 0 == $H5PIndex) {
                        $nodes[$i]->typeData->h5pMeta = $allH5Ps[$H5PIndex];
                    }
                }
            }
        }

        return $nodes;
    }

    private function _filterNodesMetaIdsByStatus($nodeMetaIds)
    {
        if (!isset($this->settings->showRejected)) {
            $this->settings->showRejected = false;
        }
        $currentUser = new TapestryUser();
        $currentUserId = wp_get_current_user()->ID;
        $nodesPermitted = [];
        foreach ($nodeMetaIds as $nodeId) {
            $node = new TapestryNode($this->postId, $nodeId);
            $nodeMeta = $node->getMeta();
            // draft nodes should only be visible to node authors
            // the exception is that the node is submitted in which case it should also be viewable to reviewers
            if (NodeStatus::DRAFT == $nodeMeta->status) {
                if ($nodeMeta->author->id == $currentUserId) {
                    array_push($nodesPermitted, $nodeId);
                } elseif ((NodeStatus::SUBMIT == $nodeMeta->reviewStatus || (NodeStatus::REJECT == $nodeMeta->reviewStatus && $this->settings->showRejected)) && $currentUser->canEdit($this->postId)) {
                    array_push($nodesPermitted, $nodeId);
                }
            } else {
                array_push($nodesPermitted, $nodeId);
            }
        }

        return $nodesPermitted;
    }

    private function _traverseNodes($node, &$checked, &$nodesPermitted, $superuser_override, $currentUserId, $secondaryUserId)
    {
        $checked[] = $node;

        if ($this->_userIsAllowed($node, $superuser_override, $currentUserId)
            || $this->_userIsAllowed($node, $superuser_override, $secondaryUserId)) {
            $nodesPermitted[] = $node;

            foreach ($this->links as $link) {
                if ($link->target == $node && $link->source && !in_array($link->source, $checked)) {
                    $this->_traverseNodes($link->source, $checked, $nodesPermitted, $superuser_override, $currentUserId, $secondaryUserId);
                } elseif ($link->source == $node && $link->target && !in_array($link->target, $checked)) {
                    $this->_traverseNodes($link->target, $checked, $nodesPermitted, $superuser_override, $currentUserId, $secondaryUserId);
                }
            }
        }
    }

    private function _userIsAllowed($node, $superuser_override, $userId)
    {
        return TapestryHelpers::userIsAllowed('READ', $node, $this->postId, $superuser_override, $userId)
        || TapestryHelpers::userIsAllowed('ADD', $node, $this->postId, $superuser_override, $userId)
        || TapestryHelpers::userIsAllowed('EDIT', $node, $this->postId, $superuser_override, $userId);
    }
}

<?php
require_once dirname(__FILE__) . "/../utilities/class.tapestry-errors.php";
require_once dirname(__FILE__) . "/../utilities/class.tapestry-helpers.php";
require_once dirname(__FILE__) . "/../utilities/class.tapestry-user-roles.php";
require_once dirname(__FILE__) . "/../utilities/class.tapestry-node-permissions.php";
require_once dirname(__FILE__) . "/../interfaces/interface.tapestry.php";
require_once dirname(__FILE__) . "/../classes/class.constants.php";

/**
 * Add/update/retrieve a Tapestry
 * 
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
     * Constructor
     * 
     * @param   Number  $postId post ID
     * 
     * @return  NULL
     */
    public function __construct($postId = 0)
    {
        $this->postId = (int) $postId;
        $this->author = (int) $this->_getAuthor();

        if (TapestryHelpers::isValidTapestry($this->postId)) {
            $tapestry = $this->_loadFromDatabase();
            $this->nodes = $tapestry->nodes;
            $this->links = $tapestry->links;
            $this->groups = $tapestry->groups;
            $this->rootId = $tapestry->rootId;
            $this->settings = $tapestry->settings;
        } else {
            $this->nodes = [];
            $this->links = [];
            $this->groups = [];
            $this->rootId = 0;
            $this->settings = (object) [];
        }
    }

    /**
     * Save the Tapestry
     * 
     * @return  Object  $tapestry
     */
    public function save()
    {
        $this->updateTapestryPost = true;
        return $this->_saveToDatabase();
    }

    /**
     * Save the Tapestry automatically on publish
     * 
     * @return  Object  $tapestry
     */
    public function saveOnPublish()
    {
        $this->updateTapestryPost = false;
        return $this->_saveToDatabase();
    }

    /**
     * Set Tapestry
     * 
     * @param   Object  $tapestry  tapestry
     *
     * @return  NULL
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
        }
    }

    /**
     * Retrieve a Tapestry post
     * 
     * @return  Object  $tapestry
     */
    public function get()
    {
        if (!$this->postId) {
            throw new TapestryError('INVALID_POST_ID');
        }
        return $this->_getTapestry();
    }

    /**
     * Get node IDs
     * 
     * @return  Array  $nodes  node ids
     */
    public function getNodeIds()
    {
        if (!$this->postId) {
            throw new TapestryError('INVALID_POST_ID');
        }
        return $this->nodes;
    }

    /**
     * Retrieves ids of all copilot-only nodes. Assumes the
     * currently logged in user is a copilot.
     */
    public function getCopilotNodeIds()
    {
        $result = array();
        foreach ($this->nodes as $nodeId) {
            $node = $this->getNode($nodeId);
            if ($node->isCopilotOnly()) {
                array_push($result, $nodeId);
            }
        }
        return $result;
    }

    /**
     * Retrieves ids of all teen nodes. It does this by checking
     * whether the node id exists in the copilot nodes array.
     */
    public function getTeenNodeIds()
    {
        $result = array();
        $copilotNodes = $this->getCopilotNodeIds();
        foreach ($this->nodes as $nodeId) {
            if (!in_array($nodeId, $copilotNodes)) {
                array_push($result, $nodeId);
            }
        }
        return $result;
    }

    /**
     * Add a new node
     * 
     * @param   Object  $node   Tapestry node
     * 
     * @return  Object  $node   Tapestry node
     */
    public function addNode($node, $parentId = null)
    {
        $parent = null;

        if (isset($parentId)) {
            $parent = $this->getNode($parentId)->get();
        }

        if (!$this->validateNode($node, $parent)) {
            throw new TapestryError("INVALID_NODE_TYPE");
        }

        $tapestryNode = new TapestryNode($this->postId);
        $tapestryNode->set($node);
        $node = $tapestryNode->save($node);

        array_push($this->nodes, $node->id);

        if (empty($this->rootId)) {
            $this->rootId = $this->nodes[0];
        }

        $this->_saveToDatabase();
        return $node;
    }

    /**
     * Delete a node
     *
     * @param   Object  $nodeId  Tapestry node id
     *
     * @return  Object  $Array   Tapestry nodes
     */
    public function deleteNodeFromTapestry($nodeId)
    {
        // Remove the rootId field
        if ($nodeId == $this->rootId) {
            if (count($this->nodes) > 1) {
                throw new TapestryError('CANNOT_DELETE_ROOT');
            } else {
                $this->rootId = 0;
            }
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
                $this->removeLink($index);
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
     * Add a new link
     * 
     * @param  Object   $link   Tapestry link
     * 
     * @return  Object  $link   Tapestry link
     */
    public function addLink($link)
    {
        $parent = $this->getNode($link->source)->get();
        $child = $this->getNode($link->target)->get();

        $isValid = $this->validateNode($child, $parent);

        if (!$isValid) {
            throw new TapestryError('INVALID_NODE_TYPE');
        }

        array_push($this->links, $link);
        $this->_saveToDatabase();
        return $link;
    }

    /**
     * Delete a link from links array
     * 
     * @param  Integer $linkIndex Link Index
     * 
     * @return Array   $links     Tapestry links
     */
    public function removeLink($linkIndex)
    {
        array_splice($this->links, $linkIndex, 1);
        $this->_saveToDatabase();
        return $this->links;
    }

    /**
     * Add a new group
     * 
     * @param   Object  $group   Tapestry group
     * 
     * @return  Object  $group   Tapestry group
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
     * Get the node controller with associated node meta ID
     * 
     * @param   Number  $nodeMetaId node meta ID
     *
     * @return  Object  $node       node controller
     */
    public function getNode($nodeMetaId)
    {
        return new TapestryNode($this->postId, $nodeMetaId);
    }

    /**
     * Get the group controller with associated group meta ID
     * 
     * @param   Number  $groupMetaId    group meta ID
     *
     * @return  Object  $group          group controller
     */
    public function getGroup($groupMetaId)
    {
        return new TapestryNode($this->postId, $groupMetaId);
    }

    /**
     * Returns true if the tapestry is empty
     *
     * @return Boolean true if there is no root node, false otherwise
     */
    public function isEmpty()
    {
        return empty($this->rootId);
    }

    public function validateNode($node, $parent = null)
    {
        $tydeType = $node->tydeType;

        if (!isset($tydeType) || !is_string($tydeType)) {
            return true; // for backwards compatibility
        }

        if (!isset($parent)) {
            return $tydeType == TydeTypes::MODULE || $tydeType == TydeTypes::REGULAR;
        }

        $parentType = $parent->tydeType;
        if (!isset($parentType) || $parentType == "") {
            return true;
        }

        if ($parentType == TydeTypes::MODULE) {
            return $tydeType == TydeTypes::STAGE;
        } else if ($parentType == TydeTypes::STAGE) {
            return $tydeType == TydeTypes::QUESTION_SET;
        } else if ($parentType == TydeTypes::REGULAR) {
            return $tydeType == TydeTypes::MODULE || $tydeType == TydeTypes::REGULAR;
        } else {
            // otherwise parent is a question set, so only valid if parent
            // is an accordion
            return $parent->mediaType == "accordion";
        }
    }

    public function getNodeParent($nodeId)
    {
        $parent = null;

        foreach ($this->links as $link) {
            if ($link->target == $nodeId) {
                $node = new TapestryNode($this->postId, $link->source);
                $parent = $node->get();
                break;
            }
        }

        return $parent;
    }
    
    public function setUnlocked($nodeIds, $userId = 0)
    {
        $nodes = array_map(
            function ($nodeMetaId) use ($userId) {
                $tapestryNode = new TapestryNode($this->postId, $nodeMetaId);
                $nodeData = !TapestryUserRoles::canEdit($this->postId) && $tapestryNode->isLocked($userId) ? $tapestryNode->getMeta() : $tapestryNode->get();
                $nodeData->unlocked = !$tapestryNode->isLocked($userId);
                $nodeData->conditions = $tapestryNode->getLockedState($userId);
                return $nodeData;
            },
            $nodeIds
        );
        return $this->_setAccessibleStatus($nodes);
    }

    private function _setAccessibleStatus($nodes)
    {
        $newNodes = array_map(
            function ($node) {
                $node->accessible = false;
                return $node;
            },
            $nodes
        );
        if (count($newNodes)) {
            $this->_recursivelySetAccessible($newNodes[0], array(), $newNodes);
        }
        return $newNodes;
    }

    private function _recursivelySetAccessible($node, $visited, $nodeList)
    {
        if (!in_array($node, $visited)) {
            array_push($visited, $node);
        }
        $node->accessible = $node->unlocked;
        if ($node->accessible) {
            $neighbourIds = $this->_getNeighbours($node);

            $neighbours = array_map(
                function($nodeId) use ($nodeList) {
                    foreach ($nodeList as $otherNode) {
                        if ($otherNode->id === $nodeId) {
                            return $otherNode;
                        }
                    }
                },
                $neighbourIds
            );

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
        $neighbourIds = array();

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
            return (object) [
                'nodes' => [],
                'links' => [],
                'groups' => [],
                'rootId' => 0,
                'settings' => (object) []
            ];
        }
        return $tapestry;
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
            'nodes'     => $this->nodes,
            'groups'    => $this->groups,
            'links'     => $this->links,
            'settings'  => $this->settings,
            'rootId'    => $this->rootId
        ];
    }

    private function _saveToDatabase()
    {
        $tapestry = $this->_formTapestry();

        if ($this->updateTapestryPost) {
            $this->postId = TapestryHelpers::updatePost($tapestry, 'tapestry', $this->postId);
            $this->_resetAuthor();
        }

        update_post_meta($this->postId, 'tapestry', $tapestry);

        return $tapestry;
    }
    
    private function _resetAuthor()
    {
        wp_update_post(array(
            'ID'            => $this->postId,
            'post_author'   => $this->author
        ));
    }

    private function _getTapestry()
    {
        $tapestry = $this->_filterTapestry($this->_formTapestry());

        $tapestry->nodes = $this->setUnlocked($tapestry->nodes);
        $tapestry->nodes = array_map(
            function ($node) {
                $tapestryNode = new TapestryNode($this->postId, $node->id);
                if (TapestryUserRoles::isRole('copilot')) {
                    if ($tapestryNode->isCopilotOnly()) {
                        $node->userType = 'copilot';
                    } else {
                        $node->userType = 'teen';
                    }
                }
                return $node;
            },
            $tapestry->nodes
        );

        $tapestry->groups = array_map(
            function ($groupMetaId) {
                $tapestryGroup = new TapestryGroup($this->postId, $groupMetaId);
                return $tapestryGroup->get();
            },
            $tapestry->groups
        );

        return $tapestry;
    }

    private function _filterTapestry($tapestry)
    {
        if ((!TapestryUserRoles::isEditor())
            && (!TapestryUserRoles::isAdministrator())
            && (!TapestryUserRoles::isAuthorOfThePost($this->postId))
        ) {
            $tapestry->nodes = $this->_filterNodeMetaIdsByPermissions($tapestry->nodes, $tapestry->rootId);
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

    private function _filterNodeMetaIdsByPermissions($nodeMetaIds, $rootId)
    {
        $options = TapestryNodePermissions::getNodePermissions();
        $userId = wp_get_current_user()->ID;
        $groupIds = TapestryHelpers::getGroupIdsOfUser($userId, $this->postId);

        $nodesPermitted = [];
        foreach ($nodeMetaIds as $nodeMetaId) {
            if ($this->_pathIsAllowed($nodeMetaId, $rootId)) {
                array_push($nodesPermitted, $nodeMetaId);
            }
        }

        return $nodesPermitted;
    }

    private function _pathIsAllowed($from, $to, $checked = [])
    {
        if (in_array($from, $checked)) {
            return false;
        }

        if (TapestryHelpers::currentUserIsAllowed('READ', $from, $this->postId))
        {
            if ($from == $to) {
                return true;
            }

            $checked[] = $from;

            foreach ($this->links as $link) {
                if (($link->target == $from && $this->_pathIsAllowed($link->source, $to, $checked)) || 
                    ($link->source == $from && $this->_pathIsAllowed($link->target, $to, $checked))) {
                    return true;
                }
            }   
        }

        return false;
    }
}

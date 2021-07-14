<?php

require_once dirname(__FILE__).'/../../utilities/class.tapestry-errors.php';
class CircleOfSupport
{
    private $userId;
    private $versions;
    private $current;

    public const META_KEY = 'tyde_circle_of_support';
    public const MAX_COMMUNITIES = 10;

    public function __construct($userId = 0)
    {
        if (!$userId) {
            $userId = wp_get_current_user()->ID;
        }

        $this->userId = $userId;
        $this->versions = $this->_getVersions();
        $this->current = $this->get();
    }

    public function addConnection($connection)
    {
        $id = uniqid();
        $connection->id = $id;
        $this->current['connections']->$id = $connection;

        return $connection;
    }

    public function addCommunity($community)
    {
        if (count(get_object_vars($this->current['communities'])) >= CircleOfSupport::MAX_COMMUNITIES) {
            throw new TapestryError('CANNOT_ADD_COMMUNITY', sprintf('Cannot add more communities (limit: %d). Please delete a community before adding another.', CircleOfSupport::MAX_COMMUNITIES), 400);
        }

        $id = uniqid();
        $community->id = $id;
        $community->connections = [];
        $this->current['communities']->$id = $community;

        return $community;
    }

    public function addConnectionToCommunity($connection, $communityId)
    {
        // Check if community exists
        if (!isset($this->current['communities']->$communityId)) {
            return;
        }

        // Check if connection exists
        $connectionId = $connection->id;
        if (!isset($this->current['connections']->$connectionId)) {
            return;
        }

        $community = $this->current['communities']->$communityId;
        array_push($community->connections, $connectionId);

        return $community;
    }

    public function removeConnectionFromCommunity($connectionId, $communityId)
    {
        // Check if community exists
        if (!isset($this->current['communities']->$communityId)) {
            return;
        }

        // Check if connection exists
        if (!isset($this->current['connections']->$connectionId)) {
            return;
        }

        $community = $this->current['communities']->$communityId;
        $index = array_search($connectionId, $community->connections);
        if (!is_numeric($index)) {
            return;
        }

        array_splice($community->connections, $index, 1);

        return $community;
    }

    public function updateConnection($id, $connection)
    {
        $this->current['connections']->$id = $connection;

        return $connection;
    }

    public function updateCommunity($id, $community)
    {
        $this->current['communities']->$id = $community;

        return $community;
    }

    public function get()
    {
        if ($this->_isEmpty()) {
            return $this->_getDefaultCos();
        }

        return $this->versions[count($this->versions) - 1];
    }

    public function getByDate($date)
    {
        foreach ($this->versions as $version) {
            if ($this->_isSameDay($version->timestamp, $date)) {
                return $version;
            }
        }

        return null;
    }

    public function save($circleOfSupport = null)
    {
        if (!$circleOfSupport) {
            $circleOfSupport = $this->current;
        }

        $circleOfSupport['id'] = $this->userId;
        $now = date_format(new DateTime(), DateTime::ISO8601);

        if ($this->_isEmpty()) {
            array_push($this->versions, $circleOfSupport);
        } else {
            if ($this->_isSameDay($this->current->timestamp, $now)) {
                $this->versions[count($this->versions) - 1] = $circleOfSupport;
            } else {
                array_push($this->versions, $circleOfSupport);
            }
        }

        $this->current = $this->get();
        update_user_meta($this->userId, CircleOfSupport::META_KEY, $this->versions);

        return $this->current;
    }

    /**
     * Deletes the entire CoS from the db. Currently not called anywhere other than
     * for development/testing.
     */
    public function delete()
    {
        return delete_user_meta($this->userId, CircleOfSupport::META_KEY);
    }

    public function addConnectionToCircle($circleIndex, $connectionId)
    {
        // Check if circle exists
        if (!isset($this->current['circles'][$circleIndex])) {
            return;
        }

        // Check if connection exists
        if (!isset($this->current['connections']->$connectionId)) {
            return;
        }

        $circle = $this->current['circles'][$circleIndex];
        array_push($circle, $connectionId);
        $this->current['circles'][$circleIndex] = $circle;

        return $circle;
    }

    public function removeConnectionFromCircle($circleIndex, $connectionId)
    {
        // Check if circle exists
        if (!isset($this->current['circles'][$circleIndex])) {
            throw new TapestryError('CIRCLE_DOESNT_EXIST', sprintf('Cannot find circle with index %d', $circleIndex), 404);
        }

        // Check if connection exists
        if (!isset($this->current['connections']->$connectionId)) {
            throw new TapestryError('CONNECTION_DOESNT_EXIST', sprintf('Cannot find connection with id %s', $connectionId), 404);
        }

        $circle = $this->current['circles'][$circleIndex];
        $index = array_search($connectionId, $circle);

        // This circle doesn't contain the connection
        if (!is_numeric($index)) {
            throw new TapestryError('CONNECTION_DOESNT_EXIST', sprintf("Circle %d doesn't contain connection %s", $circleIndex, $connectionId), 404);
        }

        array_splice($circle, $index, 1);
        $this->current['circles'][$circleIndex] = $circle;

        return $circle;
    }

    private function _getDefaultCos()
    {
        return [
            'id' => $this->userId,
            'connections' => new stdClass(),
            'communities' => new stdClass(),
            'circles' => [[], [], []],
            'members' => new stdClass(),
            'timestamp' => date_format(new DateTime(), DateTime::ISO8601),
        ];
    }

    private function _getVersions()
    {
        $versions = get_user_meta($this->userId, CircleOfSupport::META_KEY, true);
        if (!is_array($versions)) {
            return [];
        }

        return $versions;
    }

    private function _isEmpty()
    {
        return 0 === count($this->versions);
    }

    private function _isSameDay($date1, $date2)
    {
        return date_format(new DateTime($date1), 'Ymd') == date_format(new DateTime($date2), 'Ymd');
    }
}

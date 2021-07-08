<?php

// TODO Change exceptions to using an ERROR class
require_once dirname(__FILE__).'/../interfaces/interface.tapestry-user-progress.php';
require_once dirname(__FILE__).'/../classes/class.tapestry-node.php';
require_once dirname(__FILE__).'/../classes/class.tapestry.php';
require_once dirname(__FILE__).'/../utilities/class.tapestry-user.php';

/**
 * Add/update/retrieve User progress.
 */
class TapestryUserProgress implements ITapestryUserProgress
{
    private $_userId = null;
    private $postId;
    private $nodeMetaId;

    /**
     * Constructor.
     *
     * @param Number $postId     post ID
     * @param Number $nodeMetaId node meta ID
     *
     * @return null
     */
    public function __construct($postId = null, $nodeMetaId = null)
    {
        $this->_userId = apply_filters('determine_current_user', false);
        $this->postId = $postId;
        $this->nodeMetaId = $nodeMetaId;
    }

    /**
     * Get User's progress for a tapestry post.
     *
     * @return string progress   of each node in json format
     */
    public function get()
    {
        $this->_isValidTapestryPost();
        $this->_checkPostId();

        $tapestry = new Tapestry($this->postId);
        $nodeIds = $tapestry->getNodeIds();

        return $this->_getUserProgress($nodeIds, $this->_userId);
    }

    /**
     * Update User's video progress for a tapestry post.
     *
     * @param float $progressValue how much the video was viewed, value should be between >= 0 and <= 1
     *
     * @return null
     */
    public function updateUserProgress($progressValue)
    {
        $this->_checkPostId();

        if (null !== $progressValue) {
            $progressValue = floatval($progressValue);
        }

        // Value should be between 0 and 1
        if ($progressValue < 0 || $progressValue > 1) {
            throw new TapestryError('INVALID_PROGRESS');
        }

        $this->_updateUserProgress($progressValue);
    }

    /**
     * Set 'completed' status of a Tapestry Node for this User to true.
     *
     * @return null
     */
    public function complete()
    {
        $this->_checkPostId();
        $this->_complete();
    }

    /**
     * Set the question with the given id to be marked as 'completed'.
     *
     * @param int    $questionId the question to mark
     * @param string $answerData the user answer
     *
     * @return null
     */
    public function completeQuestion($questionId, $answerType, $answerData)
    {
        $this->_checkPostId();
        $this->_completeQuestion($questionId, $answerType, $answerData);
    }

    /**
     * Update User's h5p video setting for a tapestry post.
     *
     * @param string $h5pSettingsData stores volume,
     *                                playbackRate, quality of h5p video
     *
     * @return null
     */
    public function updateH5PSettings($h5pSettingsData)
    {
        $this->_checkPostId();
        $this->_updateUserH5PSettings($h5pSettingsData);
    }

    /**
     * Get User's h5p video setting for a tapestry post.
     *
     * @return string h5p $setting
     */
    public function getH5PSettings()
    {
        $this->_isValidTapestryPost();
        $this->_checkPostId();

        return $this->_getUserH5PSettings();
    }

    public function isCompleted($nodeId, $userId)
    {
        $nodeMetadata = get_metadata_by_mid('post', $nodeId)->meta_value;
        $completed_value = get_user_meta($userId, 'tapestry_'.$this->postId.'_node_completed_'.$nodeId, true);
        if (null !== $completed_value) {
            return '1' === $completed_value;
        } else {
            return isset($nodeMetadata->completed) && $nodeMetadata->completed ? true : false;
        }
    }

    private function _updateUserProgress($progressValue)
    {
        update_user_meta($this->_userId, 'tapestry_'.$this->postId.'_progress_node_'.$this->nodeMetaId, $progressValue);
    }

    private function _complete()
    {
        update_user_meta($this->_userId, 'tapestry_'.$this->postId.'_node_completed_'.$this->nodeMetaId, true);
    }

    private function _completeQuestion($questionId, $answerType, $answerData)
    {
        $userAnswer = get_user_meta($this->_userId, 'tapestry_'.$this->postId.'_'.$this->nodeMetaId.'_question_'.$questionId.'_answers', true);
        if ('' === $userAnswer) {
            $userAnswer = [];
        }
        $userAnswer[$answerType] = $answerData;
        update_user_meta($this->_userId, 'tapestry_'.$this->postId.'_'.$this->nodeMetaId.'_question_'.$questionId.'_answers', $userAnswer);
    }

    private function _getUserProgress($nodeIdArr, $userId)
    {
        $progress = new stdClass();
        $tapestry = new Tapestry($this->postId);

        $roles = new TapestryUser();

        $nodes = $tapestry->setUnlocked($nodeIdArr, $userId);

        // Build json object for frontend e.g. {0: 0.1, 1: 0.2} where 0 and 1 are the node IDs
        foreach ($nodes as $node) {
            $nodeId = $node->id;
            $isDyad = $node->isDyad;

            $progress_value = get_user_meta($userId, 'tapestry_'.$this->postId.'_progress_node_'.$nodeId, true);

            $user_meta = get_userdata($userId); 
            $user_roles = $user_meta->roles;

            if ($isDyad && in_array('dyad', $user_roles, true)) {
                // overwrite $progress_value to that of $teenId
                // if the user is a parent account
                $teenId = get_user_meta($userId, 'linked_dyad_user_id');
                $progress_value = get_user_meta($teenId[0], 'tapestry_'.$this->postId.'_progress_node_'.$nodeId, true);
            }

            $progress->$nodeId = new stdClass();
            if (null !== $progress_value) {
                $progress->$nodeId->progress = (float) $progress_value;
            } else {
                $progress->$nodeId->progress = 0;
            }

            $progress->$nodeId->accessible = $node->accessible;
            $progress->$nodeId->conditions = $node->conditions;
            $progress->$nodeId->unlocked = $node->unlocked;

            if ($node->accessible) {
                $progress->$nodeId->content = [
                    'typeData' => $node->typeData,
                ];
            }

            $isQuestionNode = property_exists($node->typeData, 'activity');
            if ($isQuestionNode) {
                $questionIdArray = array_map(
                    function ($question) {
                        return $question->id;
                    },
                    $node->typeData->activity->questions
                );

                $progress->$nodeId->content['userAnswers'] = new stdClass();
                $progress->$nodeId->content['userAnswers']->activity = new stdClass();
                foreach ($questionIdArray as $questionId) {
                    $answer = get_user_meta($userId, 'tapestry_'.$this->postId.'_'.$nodeId.'_question_'.$questionId.'_answers', true);
                    $progress->$nodeId->content['userAnswers']->activity->{$questionId}->answers = $answer;
                }
            }

            $completed_value = $this->isCompleted($nodeId, $userId);
            $progress->$nodeId->completed = $completed_value;
        }

        return $progress;
    }

    private function _updateUserH5PSettings($h5pSettingsData)
    {
        update_user_meta($this->_userId, 'tapestry_h5p_setting_'.$this->postId, $h5pSettingsData);
    }

    private function _getUserH5PSettings()
    {
        $settings = get_user_meta($this->_userId, 'tapestry_h5p_setting_'.$this->postId, true);

        return $settings ? json_decode($settings) : (object) [];
    }

    /**
     * Get User's video progress for a tapestry post.
     *
     * @return array $favourites array of nodeIds
     */
    public function getFavourites()
    {
        $this->_isValidTapestryPost();
        $this->_checkPostId();

        $favourites = get_user_meta($this->_userId, 'tapestry_favourites_'.$this->postId, true);
        if ($favourites) {
            return json_decode($favourites);
        }

        return [];
    }

    /**
     * Update User's favourite nodes for a tapestry post.
     *
     * @param array $favourites update the favourite nodes
     *
     * @return null
     */
    public function updateFavourites($favourites)
    {
        $this->_checkPostId();
        update_user_meta($this->_userId, 'tapestry_favourites_'.$this->postId, $favourites);
    }

    /* Helpers */

    private function _checkPostId()
    {
        if (!isset($this->postId)) {
            throw new Exception('postId is invalid');
        }
    }

    private function _isValidTapestryPost()
    {
        // post ID exists in db
        if (!get_permalink($this->postId)) {
            throw new Exception('post id does not exist');
        }

        // Post type is correct
        if ('tapestry' != get_post_type($this->postId)) {
            throw new Exception('post type is invalid');
        }
    }

    private function _isJson($string)
    {
        $test_json = json_decode($string);
        if (null !== $test_json) {
            return true;
        } else {
            return false;
        }
    }
}

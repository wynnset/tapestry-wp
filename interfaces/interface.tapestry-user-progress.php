<?php

/**
 * Tapestry Interface.
 */
interface ITapestryUserProgress
{
    /**
     * Get User's video progress for a tapestry post.
     *
     * @return string progress of each node in json format
     */
    public function get();

    /**
     * Update User's video progress for a tapestry post.
     *
     * @param float $progressValue how much the video was viewed, value should be between >= 0 and <= 1
     *
     * @return null
     */
    public function updateUserProgress($progressValue);

    /**
     * Set 'skippable' status of a Tapestry Node for this User to true.
     *
     * @return null
     */
    public function complete();

    /**
     * Set the question with the given id to be marked as 'completed'.
     *
     * @param int $questionId the question to mark
     * @param string $answerData the user answer
     * @param string $answerType the user answer type
     *
     * @return null
     */
    public function completeQuestion($questionId, $answerData, $answerType);

    /**
     * Update User's h5p video setting for a tapestry post.
     *
     * @param string $h5pSettingsData stores volume,
     *                                playbackRate, quality of h5p video
     *
     * @return null
     */
    public function updateH5PSettings($h5pSettingsData);

    /**
     * Get User's h5p video setting for a tapestry post.
     *
     * @return string h5p $setting
     */
    public function getH5PSettings();

    /**

     * Update the user's user settings.
     *
     * @param string $userSettings stores avatar
     *
     *
     * @return null
     */
    public function updateUserSettings($userSettings);

    /**
     * Get the user's avatar.
     *
     * @return object avatar $avatar
     */
    public function getAvatar();

    /**
     * Get User's favourite nodes from a tapestry post.
     *
     * @return array $nodes  node ids which are favourites
     */
    public function getFavourites();

    /**
     * Update User's favourite nodes for a tapestry post.
     *
     * @param array $favourites update the favourite nodes
     *
     * @return null
     */
    public function updateFavourites($favourites);
}

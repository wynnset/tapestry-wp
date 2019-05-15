<?php
/**
 * Add/update/retrieve Tapestry post and its child nodes
 * 
 */
class TapestryController {
    /**
     * Update Tapestry settings
     * 
     * @param type @settings The updated Tapestry settings
     * @param type @postId The postId of the Tapestry
     * @return type updated Tapestry settings
     */
    public function updateTapestrySettings($settings, $postId = null) {
        // TODO: use isValidPostID() utlility function
        $tapestry = get_post_meta($postId, 'tapestry', true);
        $tapestry->settings = $settings;

        // TODO: uncomment the line below when saving tapestry is merged
        // $this->updatePost($tapestry, 'tapestry', $postId);
        update_post_meta($postId, 'tapestry', $tapestry);

        return $tapestry->settings;
    }
}
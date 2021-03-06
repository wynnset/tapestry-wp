<?php

require_once dirname(__FILE__).'/../utilities/class.tapestry-errors.php';
require_once dirname(__FILE__).'/../interfaces/interface.tapestry-audio.php';

/**
 * Update/retrieve H5P audio of a node for a user.
 */
class TapestryAudio implements ITapestryAudio
{
    /**
     * Constructor.
     *
     * @param Number $tapestryPostId tapestry post ID
     * @param Number $nodeMetaId     node meta ID
     *
     * @return null
     */
    public function __construct($tapestryPostId = 0, $nodeMetaId = 0, $questionId = 0, $userId = 0)
    {
        $this->tapestryPostId = (int) $tapestryPostId;
        $this->nodeMetaId = (int) $nodeMetaId;
        $this->questionId = $questionId;
        $this->userId = $userId || wp_get_current_user()->ID;
    }

    /**
     * Save the audio.
     *
     * @param string $audio base64 data string
     *
     * @return string $audioName
     */
    public function save($audio)
    {
        $upload_dir = wp_upload_dir();
        $tapestry_upload_dir = $upload_dir['basedir'].'/tapestry';
        if (!file_exists($tapestry_upload_dir)) {
            wp_mkdir_p($tapestry_upload_dir);
        }
        $tapestry_user_upload_dir = $tapestry_upload_dir.'/'.$this->userId;
        $tapestry_user_upload_url = 'tapestry/'.$this->userId;
        if (!file_exists($tapestry_user_upload_dir)) {
            wp_mkdir_p($tapestry_user_upload_dir);
        }

        $filename = $this->_getFileName();

        $decodedAudio = base64_decode($audio);

        if (file_put_contents($tapestry_user_upload_dir.'/'.$filename, $decodedAudio)) {
            return $tapestry_user_upload_url.'/'.$filename;
        } else {
            throw new TapestryError('FAILED_TO_SAVE_AUDIO');
        }
    }

    public function audioExists()
    {
        $filename = $this->_getFileName();
        $upload_dir = wp_upload_dir();

        return file_exists($upload_dir['basedir'].'/tapestry/'.$this->userId.'/'.$filename);
    }

    private function _getFileName()
    {
        return md5('tapestryPostId-'.$this->tapestryPostId.'-'
            .'nodeMetaId-'.$this->nodeMetaId.'-'
            .'questionId-'.$this->questionId.'-'
            .'userId-'.$this->userId)
            .'.ogg';
    }
}

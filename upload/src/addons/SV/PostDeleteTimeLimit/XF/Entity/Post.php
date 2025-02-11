<?php

namespace SV\PostDeleteTimeLimit\XF\Entity;

class Post extends XFCP_Post
{
    public function canDelete($type = 'soft', &$error = null)
    {
        $canDelete = parent::canDelete($type, $error);
        if (!$canDelete)
        {
            return false;
        }

        $visitor = \XF::visitor();
        $nodeId = $this->Thread->node_id;

        if ($visitor->hasNodePermission($nodeId, 'deleteAnyPost'))
        {
            return true;
        }

        $deleteLimit = $visitor->hasNodePermission($nodeId, 'deleteOwnPostTimeLimit');

        if ($deleteLimit != -1 && (!$deleteLimit || $this->post_date < \XF::$time - 60 * $deleteLimit))
        {
            $error = \XF::phraseDeferred('message_edit_time_limit_expired', ['minutes' => $deleteLimit]);

            return false;
        }

		$ownLastOnly = $visitor->hasNodePermission($nodeId, 'deleteOwnLastPostOnly');
		
        if (  $ownLastOnly && !$this->isLastPost() )
        {
			
            $error = \XF::phraseDeferred('permission.message_delete_another_post');

            return false;
        }
        
        return true;
    }
}

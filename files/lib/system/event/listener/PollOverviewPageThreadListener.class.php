<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Checks for posts with a poll and sets the pollID of the first post with a poll.
 * 
 * @author		Markus Bartz <roul@codingcorner.info>
 * @copyright	2010 Markus Bartz
 * @license		GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl.html>
 * @package		com.woltlab.community.roul.wbb.findpolls
 * @subpackage	system.event.listener
 * @category	Find Polls in Threads
 * @version		$Id$
 */
class PollOverviewPageThreadListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (isset($_REQUEST['threadID']) && intval($_REQUEST['threadID']) != 0) {
			$threadID = intval($_REQUEST['threadID']);
			
			require_once(WBB_DIR.'lib/data/thread/Thread.class.php');
			$thread = new Thread($threadID);
			if (!$thread->threadID) {
				throw new IllegalLinkException();
			}
			$thread->enter();
			
			$sql = "SELECT	pollID
				FROM	wbb".WBB_N."_post
				WHERE	threadID = ".$threadID."
					AND	isDeleted = 0
					AND	isDisabled = 0
					AND	pollID > 0
				ORDER BY postID ASC";
			$row = WCF::getDB()->getFirstRow($sql);
			$eventObj->pollID = $row['pollID'];
		}
	}
}
?>
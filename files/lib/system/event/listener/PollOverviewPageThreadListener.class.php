<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
// wbb imports
require_once(WBB_DIR.'lib/data/thread/Thread.class.php');

/**
 * Checks for posts with a poll and sets the pollID of the first post with a poll.
 * 
 * @author 	Markus Bartz <roul@codingcorner.info>
 * @copyright	2010 Coding Corner
 * @license		GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl.html>
 * @package		com.woltlab.community.roul.wbb.findpolls
 * @subpackage	system.event.listener
 * @category 	Find Polls in Threads
 * @version		$Id$
 */
class PollOverviewPageThreadListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (isset($_REQUEST['threadID'])) {
			$threadID = intval($_REQUEST['threadID']);
			$thread = new Thread($threadID);
			
			$thread->enter();
		}
	}
}
?>
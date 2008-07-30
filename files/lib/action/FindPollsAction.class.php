<?php
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/message/search/SearchEngine.class.php');
require_once(WCF_DIR.'lib/system/exception/IllegalLinkException.class.php');

/**
 * Searches for polls in a Thread.
 *
 * @author		Markus Bartz
 * @copyright	2008 Blackstorm
 * @package		com.woltlab.community.roul.wbb.findpolls
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * SVN-ID:		$Id$
 */
class FindPollsAction extends AbstractAction {
	public $threadID = 0;
	public $engine;

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_REQUEST['threadID'])) $this->threadID = intval($_REQUEST['threadID']);
		else throw new IllegalLinkException();
		
		// Thread() has a big query with joins and stuff, so for better performance we test better with an own little query.
		$sql = "SELECT 		threadID
			FROM 		wbb".WBB_N."_thread
			WHERE		threadID = ".$this->threadID;
		$row = WCF::getDB()->getFirstRow($sql);
		if ($row['threadID'] != $this->threadID) throw new IllegalLinkException();
		
		// i think this is a bit dirty, but PostSearch::readFormParameters() reads only from $_POST, so it is ok
		$_POST['threadID'] = $this->threadID;
		$_POST['findPolls'] = 1;
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();

		$this->engine = new SearchEngine();
		SearchEngine::loadSearchTypeObjects();
		$conditions['post'] = SearchEngine::$searchTypeObjects['post']->getConditions();

		// build search hash
		$searchHash = StringUtil::getHash(serialize(array('', array('post'), false, $conditions, 'time DESC')));

		// check search hash
		$sql = "SELECT	searchID
			FROM	wcf".WCF_N."_search
			WHERE	searchHash = '".$searchHash."'
				AND userID = ".WCF::getUser()->userID."
				AND searchType = 'messages'
				AND searchDate > ".(TIME_NOW - 1800)."
				LIMIT	1";
		$row = WCF::getDB()->getFirstRow($sql);
		if (isset($row['searchID'])) {
			header('Location: index.php?form=Search&searchID='.$row['searchID'].SID_ARG_2ND_NOT_ENCODED);
			exit;
		}

		// do search
		$result = $this->engine->search('', array('post'), $conditions, 'time DESC');

		// result is empty
		if (count($result) == 0) {
			throw new IllegalLinkException();
		}

		// get additional data
		$additionalData = array(SearchEngine::$searchTypeObjects['post']->getAdditionalData());

		// save result in database
		$searchData = array('query' => '', 'result' => $result, 'additionalData' => $additionalData);
		$searchData = serialize($searchData);

		$sql = "INSERT INTO	wcf".WCF_N."_search
					(userID, searchData, searchDate, searchType, searchHash)
			VALUES		(".WCF::getUser()->userID.",
					'".escapeString($searchData)."',
					".TIME_NOW.",
					'messages',
					'".$searchHash."')";
		WCF::getDB()->sendQuery($sql);
		$searchID = WCF::getDB()->getInsertID();

		$this->executed();
		header('Location: index.php?form=Search&searchID='.$searchID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>
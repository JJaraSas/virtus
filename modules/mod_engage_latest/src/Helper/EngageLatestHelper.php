<?php
/**
 * @package   AkeebaEngage
 * @copyright Copyright (c)2020-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/**
 * @package     Joomla\Module\EngageLatest\Site\Helper
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace Joomla\Module\EngageLatest\Site\Helper;

use Akeeba\Component\Engage\Administrator\Model\CommentsModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

defined('_JEXEC') or die;

class EngageLatestHelper
{
	public function getLatestComments(int $numComments = 10, int $maxWords = 30): array
	{
		if (!$this->hasEngage())
		{
			return [];
		}

		$app = Factory::getApplication();
		/** @var MVCFactoryInterface $mvcFactory */
		$mvcFactory = $app->bootComponent('com_engage')->getMVCFactory();
		/** @var CommentsModel $model */
		$model      = $mvcFactory->createModel('Comments', 'Site', ['ignore_request' => true]);

		$model->setState('filter.frontend', 1);
		$model->setState('list.ordering', 'c.created');
		$model->setState('list.direction', 'DESC');
		$model->setState('list.start', 0);
		$model->setState('list.limit', $numComments);

		// Comment bodies are converted to plaintext and limited on the number of displayed words
		return array_map(function (object $comment) use ($maxWords) {
			$comment->body  = strip_tags($comment->body);
			$words = str_word_count($comment->body, 2);

			if (count($words) <= $maxWords)
			{
				return $comment;
			}

			$wordPos = array_keys($words);
			$lastPos = $wordPos[$maxWords] - 1;
			$comment->body = substr($comment->body, 0, $lastPos) . ' [...]';

			return $comment;
		}, $model->getItems());
	}

	/**
	 * Is Akeeba Engage installed and enabled?
	 *
	 * @return  bool
	 * @since   3.0.9
	 */
	public function hasEngage(): bool
	{
		return ComponentHelper::isInstalled('com_engage') && ComponentHelper::isEnabled('com_engage');
	}
}
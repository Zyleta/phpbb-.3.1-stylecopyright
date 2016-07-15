<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace zyleta\stylecopyright\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\template\template 	*/
	protected $template;
	/** @var \phpbb\user */
	protected $user;
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	
	/**
	* Constructor
	*
	* @param \phpbb\template\template				$template
	* @param \phpbb\user 							$user
	* @param \phpbb\db\driver\driver_interface 		$db
	*
	*/
	public function __construct(\phpbb\template\template $template, \phpbb\user $user)
	{
		global $db;
		$this->template = $template;
		$this->user = $user;
		$this->db = $db;
	}
	
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'		=> 'load_language_on_setup',
			'core.page_footer'		=> 'page_footer',
		);
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'zyleta/stylecopyright',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	protected function get_copyright()
	{
		$sql = 'SELECT *
			FROM ' . STYLES_TABLE . '
			WHERE style_id = ' . (int) $this->user->data['user_style'];
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return $row;
	}

	public function page_footer($event)
	{
		$this->template->assign_vars(array(
			'L_STYLE_COPYRIGHT'			=> $this->get_copyright()['style_copyright'],
			'L_STYLE_NAME'				=> $this->get_copyright()['style_name'],
			'L_PARENT_STYLE'			=> $this->get_copyright()['style_parent_tree'],
		));
	}
}

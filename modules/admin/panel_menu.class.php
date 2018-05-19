<?php

if (!defined("MCR")) {
	exit("Hacking Attempt!");
}

class submodule
{
	private $core, $db, $cfg, $user, $l10n;

	public function __construct(core $core)
	{
		$this->core = $core;
		$this->db = $core->db;
		$this->cfg = $core->cfg;
		$this->user = $core->user;
		$this->l10n = $core->l10n;

		$bc = [
			$this->l10n->gettext('mod_name') => ADMIN_URL
		];

		$this->core->bc = $this->core->gen_bc($bc);

		$this->core->header .= $this->core->sp(MCR_THEME_MOD."admin/panel_menu/header.html");
	}

	private function get_items_array()
	{
		$query = $this->db->query("SELECT `m`.id, `m`.gid, `m`.title, `m`.`text`, `m`.`url`, `m`.`target`, `m`.`access`, `i`.img
									FROM `mcr_menu_adm` AS `m`
									LEFT JOIN `mcr_menu_adm_icons` AS `i`
										ON `i`.id=`m`.icon
									ORDER BY `priority` ASC");

		if (!$query || $this->db->num_rows($query) <= 0) {
			return [];
		}

		$items = [];

		while ($ar = $this->db->fetch_assoc($query)) {
			$gid = intval($ar['gid']);

			$array = [
				"id" => $ar['id'], "gid" => $ar['gid'], "title" => $ar['title'], "text" => $ar['text'], "url" => $ar['url'], "target" => $ar['target'], "access" => $ar['access'], "icon" => $ar['img']
			];

			if (!isset($items[$gid])) {
				$items[$gid] = [];
				array_push($items[$gid], $array);
			} else {
				array_push($items[$gid], $array);
			}
		}

		return $items;
	}

	private function item_array($items)
	{
		ob_start();

		foreach ($items as $key => $ar) {

			if (!$this->core->is_access($ar['access'])) {
				continue;
			}

			$data = [
				"ID" => intval($ar['id']), "GID" => intval($ar['gid']), "TITLE" => $this->db->HSC($ar['title']), "TEXT" => $this->db->HSC($ar['text']), "URL" => $this->db->HSC($ar['url']), "TARGET" => $this->db->HSC($ar['target']), "ICON" => $this->db->HSC($ar['icon']),
			];

			echo $this->core->sp(MCR_THEME_MOD."admin/panel_menu/menu-items/item-id.html", $data);
		}

		return ob_get_clean();
	}

	private function item_list($items = [])
	{

		if (empty($items)) {
			return $this->core->sp(MCR_THEME_MOD."admin/panel_menu/menu-items/item-none.html");
		}

		$data = [
			"ITEMS" => $this->item_array($items),
		];

		return $this->core->sp(MCR_THEME_MOD."admin/panel_menu/menu-items/item-list.html", $data);
	}

	private function group_array()
	{

		$items = $this->get_items_array();

		$query = $this->db->query("SELECT id, title, `text`, `access`
									FROM `mcr_menu_adm_groups`
									ORDER BY `priority` ASC");

		if (!$query || $this->db->num_rows($query) <= 0) {
			return $this->core->sp(MCR_THEME_MOD."admin/panel_menu/menu-groups/group-none.html");
		}

		ob_start();

		$counter = 0;
		while ($ar = $this->db->fetch_assoc($query)) {
			$counter++;

			$id = intval($ar['id']);

			if (!$this->core->is_access($ar['access'])) {
				continue;
			}

			$list = (isset($items[$id])) ? $items[$id] : [];

			//$sid = 'ajx_spl_adm_grp_'.$id;

			//$status = (isset($_SESSION[$sid]) && $_SESSION[$sid]) ? "closed" : "opened";

			$data = [
				"ID" => $id, "TITLE" => $this->db->HSC($ar['title']), "TEXT" => $this->db->HSC($ar['text']), "ITEMS" => $this->item_list($list), "ACTIVE" => ($counter == 1) ? "show active" : "",
				//"STATUS"	=> $status,
			];

			echo $this->core->sp(MCR_THEME_MOD."admin/panel_menu/menu-groups/group-id.html", $data);
		}

		//echo $this->core->sp(MCR_THEME_MOD."admin/panel_menu/menu-groups/group-id.html", $data);

		return ob_get_clean();
	}

	private function group_tabs_array()
	{
		$query = $this->db->query("SELECT id, title, `text`, `access`
									FROM `mcr_menu_adm_groups`
									ORDER BY `priority` ASC");

		if (!$query || $this->db->num_rows($query) <= 0) {
			return $this->core->sp(MCR_THEME_MOD."admin/panel_menu/menu-groups/group-none.html");
		}

		ob_start();

		$counter = 0;
		while ($ar = $this->db->fetch_assoc($query)) {
			$counter++;

			$id = intval($ar['id']);

			if (!$this->core->is_access($ar['access'])) {
				continue;
			}

			$data = [
				"ID" => $id, "TITLE" => $this->db->HSC($ar['title']), "ACTIVE" => ($counter == 1) ? "active" : "",
			];

			echo $this->core->sp(MCR_THEME_MOD."admin/panel_menu/menu-groups/group-tab.html", $data);
		}

		//echo $this->core->sp(MCR_THEME_MOD."admin/panel_menu/menu-groups/group-id.html", $data);

		return ob_get_clean();
	}

	private function group_list()
	{

		$data = [
			"GROUPS" => $this->group_array(), "TABS" => $this->group_tabs_array(),
		];

		return $this->core->sp(MCR_THEME_MOD."admin/panel_menu/menu-groups/group-list.html", $data);
	}

	public function content()
	{

		return $this->group_list();
	}
}
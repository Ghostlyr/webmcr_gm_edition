<?php

if(!defined("MCR")){ exit("Hacking Attempt!"); }

class module{
	private $install, $cfg, $lng, $methods;

	public function __construct($install){
		$this->install		= $install;
		$this->cfg			= $install->cfg;
		$this->lng			= $install->lng;

		$this->methods = array('MD5', 'SHA1', 'SHA256', 'SHA512', 'Double MD5 [ md5(md5(PASS)) ]', 'Salted MD5 [ md5(PASS+SALT) ]',
								'Salted MD5 [ md5(SALT+PASS) ]', 'Salted Double MD5 [ md5(md5(SALT)+PASS) ]', 'Salted Double MD5 [ md5(md5(PASS)+SALT) ]',
								'Salted Double MD5 [ md5(PASS+md5(SALT)) ]', 'Salted Double MD5 [ md5(SALT+md5(PASS)) ]', 'Salted SHA1 [ sha1(PASS+SALT) ]',
								'Salted SHA1 [ sha1(SALT+PASS) ]', 'Triple salted MD5 [ md5(md5(SALT)+md5(PASS)) ]', 'Salted SHA256 [ sha256(PASS+SALT) ]',
								'Salted SHA512 [ sha512(PASS+SALT) ]');

		$this->install->title = $this->lng['mod_name'].' — '.$this->lng['step_2'];
	}

	private function encrypt_methods($selected=0){

		ob_start();

		foreach($this->methods as $key => $title){
			$select = ($key==$selected) ? 'selected' : '';
			echo '<option value="'.$key.'" '.$select.'>'.$title.'</option>';
		}

		return ob_get_clean();
	}

	public function content(){
		if(!isset($_SESSION['step_1'])){ $this->install->notify('', '', 'install/?do=step_1'); }
		if(isset($_SESSION['step_2'])){ $this->install->notify('', '', 'install/?do=step_3'); }

		$_SESSION['f_login'] = (isset($_POST['login'])) ? $this->install->HSC(@$_POST['login']) : 'admin';

		$_SESSION['f_adm_pass'] = @$_POST['password'];

		$_SESSION['f_repass'] = $this->install->HSC(@$_POST['repassword']);

		$_SESSION['f_email'] = (isset($_POST['email'])) ? $this->install->HSC(@$_POST['email']) : 'admin@'.$_SERVER['SERVER_NAME'];

		$method = intval(@$_POST['method']);

		if($_SERVER['REQUEST_METHOD']=='POST'){

			if(!preg_match("/^[\w\-]{3,}$/i", @$_POST['login'])){
				$this->install->notify($this->lng['e_login_format'], $this->lng['e_msg'], 'install/?do=step_2');
			}

			if(mb_strlen(@$_POST['password'], "UTF-8")<6){
				$this->install->notify($this->lng['e_pass_len'], $this->lng['e_msg'], 'install/?do=step_2');
			}

			if(@$_POST['password'] !== @$_POST['repassword']){
				$this->install->notify($this->lng['e_pass_match'], $this->lng['e_msg'], 'install/?do=step_2');
			}

			if(!filter_var(@$_POST['email'], FILTER_VALIDATE_EMAIL)){
				$this->install->notify($this->lng['e_email_format'], $this->lng['e_msg'], 'install/?do=step_2');
			}

			if(!isset($this->methods[$method])){ $this->install->notify($this->lng['e_method'], $this->lng['e_msg'], 'install/?do=step_2'); }

			$this->cfg['main']['crypt'] = $method;

			if(!$this->install->savecfg($this->cfg['main'], 'main.php', 'main')){
				$this->install->notify($this->lng['e_settings'], $this->lng['e_msg'], 'install/?do=step_2');
			}

			require_once(DIR_ROOT.'engine/db/'.$this->cfg['db']['backend'].'.class.php');

			$db = new db($this->cfg['db']['host'], $this->cfg['db']['user'], $this->cfg['db']['pass'], $this->cfg['db']['base'], $this->cfg['db']['port']);

			$error = $db->error();

			if(!empty($error)){
				$this->install->notify($this->lng['e_connection'].' | '.$db->error(), $this->lng['e_msg'], 'install/?do=step_2');
			}

			$login		= $db->safesql(@$_POST['login']);
			$email		= $db->safesql(@$_POST['email']);

			$salt		= $db->safesql($this->install->random());
			$password	= $this->install->gen_password(@$_POST['password'], $salt, $method);
			$ip			= $this->install->ip();

			$ctables	= $this->cfg['db']['tables'];

			$ic_f		= $ctables['iconomy']['fields'];
			$us_f		= $ctables['users']['fields'];

			$query = $db->query("INSERT INTO `{$ctables['users']['name']}`
										(`{$us_f['group']}`, `{$us_f['login']}`, `{$us_f['email']}`, `{$us_f['pass']}`, `{$us_f['uuid']}`, `{$us_f['salt']}`, `{$us_f['ip_last']}`, `{$us_f['date_reg']}`)
									VALUES
										('3', '$login', '$email', '$password', UNHEX(REPLACE(UUID(), '-', '')), '$salt', '$ip', NOW())");

			if(!$query){ $this->install->notify($this->lng['e_add_admin'], $this->lng['e_msg'], 'install/?do=step_2'); }

			$query = $db->query("INSERT INTO `{$ctables['iconomy']['name']}`
										(`{$ic_f['login']}`, `{$ic_f['money']}`, `{$ic_f['rm']}`, `{$ic_f['bank']}`)
									VALUES
										('$login', 0, 0, 0)");

			if(!$query){ $this->install->notify($this->lng['e_add_economy'], $this->lng['e_msg'], 'install/?do=step_2'); }

			$db->query("INSERT INTO `mcr_news` (`cid`, `title`, `text_html`, `vote`, `discus`, `attach`, `hidden`, `uid`, `date`, `img`, `data`) VALUE (
			  1, 'О проекте', '<h2><strong>MagicMCR&nbsp;</strong></h2>
<p>powered by WebMCR.&nbsp;</p>
<h3>О проекте&nbsp;</h3>
<p>Публичный проект JM Organization для проекта Grand-Mine. Проект носит кодовое название webmcr_gm_edition. Разрабатывается разработчиком Magicfar4 aka Magicmen. Данный проект представляет из себя cms для сайтов проектов игры Minecrfat. Проект основывается уже на готовой cms от разработчиков.&nbsp;</p>
{READMORE}
<h3>Установка&nbsp;</h3>
<p>Залить все скачанные файлы на сайт и следовать инструкциям установщика.&nbsp;</p>
<h3>Контакты&nbsp;</h3>
<p>Сайт официального разработчика: <a href=\"http://webmcr.com\">http://webmcr.com</a>&nbsp;</p>
<p>Официальный Wiki: <a href=\"http://wiki.webmcr.com/\">http://wiki.webmcr.com/&nbsp;</a></p>
<p>Mind 42 - <a href=\"http://mind42.com/mindmap/a2e9fdc9-a645-42db-80e0-c338f8a27c2c%20\">http://mind42.com/mindmap/a2e9fdc9-a645-42db-80e0-c338f8a27c2c&nbsp;</a></p>
<p>Сайт организации, которая адaптировала движок для проекта:&nbsp; <a href=\"http://www.jm-org.net/\">http://www.jm-org.net/</a>&nbsp;</p>', 0, 1, 0, 0, 1, NOW(), '', '{\"time_last\":1511640157,\"uid_last\":1,\"planed_news\":true,\"close_comments\":false,\"time_when_close_comments\":false}');");

			$_SESSION['step_2'] = true;

			$this->install->notify('', '', 'install/?do=step_3');

		}

		$data = array(
			'METHODS' => $this->encrypt_methods($method),
		);

		return $this->install->sp('step_2.html', $data);
	}

}

?>
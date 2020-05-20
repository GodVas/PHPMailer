<?php
declare(strict_types=1);
namespace Mailer;

use pocketmine\plugin\PluginBase;

class Mailer extends PluginBase{

	private static $instance = null;

	public function onLoad(){
		self::$instance = $this;
	}

	public static function getInstance() : Mailer{
		return self::$instance;
	}

	public function onEnable(){
		$this->saveResource("config.yml");
		if($this->getConfig()->getNested("mail-account", "example@naver.com") === "example@naver.com"){
			$this->getLogger()->error("콘피그에서 이메일과 비밀번호를 설정해주세요.");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		$this->getLogger()->info("Mailer has been enabled");
	}

	/**
	 * @param string $to 보낼 사람
	 * @param string $subject 제목
	 * @param string $desc 내용
	 */
	public function mail(string $to, string $subject, string $desc){
		try{
			$mail = new PHPMailer();
			$mail->isSMTP();
			$mail->SMTPDebug = 0;
			$mail->Host = (explode("@", $to)[1] === "naver.com") ? "smtp.naver.com" : "smtp.gmail.com"; // GMail or NAVER Mail
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = "ssl";
			$mail->Port = 465;
			$mail->CharSet = "utf-8";
			$mail->setFrom($this->getConfig()->getNested("mail-account"));
			$mail->addAddress($to);
			$mail->Subject = $subject;
			$mail->Body = $desc;
			$mail->SMTPOptions = [
					"ssl" => [
							"verify_peer" => false,
							"verify_peer_name" => false,
							"allow_self_signed" => true
					]
			];
			$mail->Username = $this->getConfig()->getNested("mail-account");
			$mail->Password = $this->getConfig()->getNested("mail-password");
			$mail->send();
		}catch(Exception $e){
			$this->getServer()->getLogger()->logException($e);
		}
	}
}
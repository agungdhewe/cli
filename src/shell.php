<?php namespace AgungDhewe\Cli;

class shell {

	private static string $ANSW_COLOR = color::FG_BOLD_MAGENTA;
	private static string $INVMSG_COLOR = color::FG_BOLD_RED;


	public static function setDefaultAnswerColor(string $color) : void {
		self::$ANSW_COLOR = $color;
	}


	public static function ask(string $question, ?array $options=null, ?string $default=null, ?string $pattern=null, ?string $invalidmessage='') : string {
		if (php_sapi_name() !== 'cli') {
			throw new \Exception('Cli::ask only available in cli mode');
		}

		if ($pattern!=null && $pattern!='') {
			if (!self::validatePattern($pattern)) {
				throw new \Exception("internal error: pattern regex salah!");
			}
		}

		$line = [$question];
		
		if ($options!==null) {
			$line[] = "(" . join("/", $options) . ")";
		}
		
		if ($default!=null && $default!='') {
			$line[] = "[" . self::$ANSW_COLOR . $default . color::RESET . "]";
		}		

		$answ = $default;
		$incorect= true;
		while ($incorect) {
			echo join(" ", $line) . " ";
			$fin = fopen ("php://stdin","r");
			$answ = trim(fgets($fin));
			$answ = empty($answ) ? $default : $answ;

			if ($options==null) {
				// jika options null, jawaban bebas, cek di pattern kalau didefinisikan
				if ($pattern==null || $pattern=='') {
					// jawaban bebas, tidak perlu cek pattern
					$incorect = false;
					return $answ;
				} else {
					// perlu cek pattern dulu, bener apa kagak ?
					if (self::validateInput($answ, $pattern)) {
						// pattern sesuai
						$incorect = false;
						return $answ;
					} else {
						if (!empty($invalidmessage)) {
							echo self::$INVMSG_COLOR . $invalidmessage . color::RESET;
							echo "\n\n";
						}
					}
				}
			} else {
				// jika ada option, jawaban cek ke dalam option yang disediakan
				if (in_array(strtolower($answ), array_map('strtolower', $options))) {
					// jawaban sesuai dengan opsi yang disediakan
					$incorect = false;
					return $answ;
				}
			}
		}

		throw new \Exception("internal error at shell::ask()");
	}

	private static function validateInput($text, $pattern) {
		return preg_match($pattern, $text);
	}

	private static function validatePattern($pattern) {
		try {
			preg_match($pattern, "");
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}
}
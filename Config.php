<?php

class Config {
	public static function getConfigurationData() {
		return parse_ini_file('config.ini', true);
	}
}

?>
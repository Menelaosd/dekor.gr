<?php
class ModelExtensionModuleSecretRecipe extends Model {
	private function ensureTable() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_secret_recipe` (
			`product_id` INT(11) NOT NULL,
			`recipe_value` MEDIUMTEXT NOT NULL,
			`date_added` DATETIME NOT NULL,
			`date_modified` DATETIME NOT NULL,
			PRIMARY KEY (`product_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	}

	private function ensureRitualCodexTable() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_secret_ritual_codex` (
			`product_id` INT(11) NOT NULL,
			`codex_value` MEDIUMTEXT NOT NULL,
			`date_added` DATETIME NOT NULL,
			`date_modified` DATETIME NOT NULL,
			PRIMARY KEY (`product_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	}

	private function encryptionKey() {
		$material = DB_PASSWORD . '|' . DB_DATABASE . '|' . DIR_SYSTEM;

		return hash('sha256', $material, true);
	}

	private function encryptValue($product_id, $value) {
		if (!function_exists('openssl_encrypt')) {
			throw new Exception('Secret Recipe requires the PHP OpenSSL extension.');
		}

		$key = $this->encryptionKey();
		$aad = 'product:' . (int)$product_id;

		if (in_array('aes-256-gcm', openssl_get_cipher_methods(), true)) {
			$iv = random_bytes(12);
			$tag = '';
			$ciphertext = openssl_encrypt($value, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, $aad, 16);

			if ($ciphertext === false) {
				throw new Exception('Secret Recipe encryption failed.');
			}

			return base64_encode('SR1' . $iv . $tag . $ciphertext);
		}

		$iv = random_bytes(16);
		$ciphertext = openssl_encrypt($value, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

		if ($ciphertext === false) {
			throw new Exception('Secret Recipe encryption failed.');
		}

		$mac = hash_hmac('sha256', $aad . $iv . $ciphertext, $key, true);

		return base64_encode('SR0' . $iv . $mac . $ciphertext);
	}

	private function decryptValue($product_id, $encoded) {
		$payload = base64_decode($encoded, true);

		if ($payload === false || strlen($payload) < 4) {
			return '';
		}

		$key = $this->encryptionKey();
		$aad = 'product:' . (int)$product_id;
		$version = substr($payload, 0, 3);

		if ($version === 'SR1' && strlen($payload) >= 31) {
			$iv = substr($payload, 3, 12);
			$tag = substr($payload, 15, 16);
			$ciphertext = substr($payload, 31);
			$value = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, $aad);

			return $value === false ? '' : $value;
		}

		if ($version === 'SR0' && strlen($payload) >= 51) {
			$iv = substr($payload, 3, 16);
			$mac = substr($payload, 19, 32);
			$ciphertext = substr($payload, 51);
			$expected = hash_hmac('sha256', $aad . $iv . $ciphertext, $key, true);

			if (!hash_equals($expected, $mac)) {
				return '';
			}

			$value = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

			return $value === false ? '' : $value;
		}

		return '';
	}

	public function getRecipe($product_id) {
		$this->ensureTable();

		$query = $this->db->query("SELECT `recipe_value` FROM `" . DB_PREFIX . "product_secret_recipe` WHERE `product_id` = '" . (int)$product_id . "'");

		if (!$query->num_rows) {
			return '';
		}

		return $this->decryptValue($product_id, $query->row['recipe_value']);
	}

	public function saveRecipe($product_id, $value) {
		$this->ensureTable();
		$value = trim((string)$value);

		if ($value === '') {
			$this->deleteRecipe($product_id);
			return;
		}

		$encrypted = $this->encryptValue($product_id, $value);

		$this->db->query("INSERT INTO `" . DB_PREFIX . "product_secret_recipe` SET `product_id` = '" . (int)$product_id . "', `recipe_value` = '" . $this->db->escape($encrypted) . "', `date_added` = NOW(), `date_modified` = NOW() ON DUPLICATE KEY UPDATE `recipe_value` = VALUES(`recipe_value`), `date_modified` = NOW()");
	}

	public function deleteRecipe($product_id) {
		$this->ensureTable();
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_secret_recipe` WHERE `product_id` = '" . (int)$product_id . "'");
	}

	public function getRitualCodex($product_id) {
		$this->ensureRitualCodexTable();

		$query = $this->db->query("SELECT `codex_value` FROM `" . DB_PREFIX . "product_secret_ritual_codex` WHERE `product_id` = '" . (int)$product_id . "'");

		if (!$query->num_rows) {
			return '';
		}

		return $this->decryptValue($product_id, $query->row['codex_value']);
	}

	public function saveRitualCodex($product_id, $value) {
		$this->ensureRitualCodexTable();
		$value = trim((string)$value);

		if ($value === '') {
			$this->deleteRitualCodex($product_id);
			return;
		}

		$encrypted = $this->encryptValue($product_id, $value);

		$this->db->query("INSERT INTO `" . DB_PREFIX . "product_secret_ritual_codex` SET `product_id` = '" . (int)$product_id . "', `codex_value` = '" . $this->db->escape($encrypted) . "', `date_added` = NOW(), `date_modified` = NOW() ON DUPLICATE KEY UPDATE `codex_value` = VALUES(`codex_value`), `date_modified` = NOW()");
	}

	public function deleteRitualCodex($product_id) {
		$this->ensureRitualCodexTable();
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_secret_ritual_codex` WHERE `product_id` = '" . (int)$product_id . "'");
	}
}

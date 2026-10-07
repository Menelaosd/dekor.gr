<?php
/**
 * dekor live search — index + matching.
 *
 * Greek and Greeklish queries are reduced to the same latin "skeleton"
 * (accents, case, final sigma, digraphs, common Greeklish spellings), so
 * "keri", "κερι", "ΚΕΡΙ" and "κερί" all hit "Κερί". Product codes match with
 * or without dots/spaces. The index is built once per store/language and kept
 * in the file cache.
 */
class ModelExtensionModuleDkSearch extends Model {
	const CACHE_TTL = 1800;

	// ---------------------------------------------------------------- normalise

	public function fold($s) {
		$s = mb_strtolower(html_entity_decode((string)$s, ENT_QUOTES, 'UTF-8'), 'UTF-8');

		return strtr($s, array(
			'ά' => 'α', 'έ' => 'ε', 'ή' => 'η', 'ί' => 'ι', 'ό' => 'ο', 'ύ' => 'υ', 'ώ' => 'ω',
			'ϊ' => 'ι', 'ϋ' => 'υ', 'ΐ' => 'ι', 'ΰ' => 'υ', 'ς' => 'σ',
			'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
			'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o',
			'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c', 'ñ' => 'n', 'ø' => 'o', '&' => ' ',
		));
	}

	/** Greek or Greeklish text -> comparable latin skeleton */
	public function skeleton($s) {
		$s = $this->fold($s);

		// Greek digraphs first, then single letters
		$s = strtr($s, array(
			'ου' => 'u', 'αι' => 'e', 'ει' => 'i', 'οι' => 'i', 'υι' => 'i', 'αυ' => 'av', 'ευ' => 'ev',
			'μπ' => 'b', 'ντ' => 'd', 'γκ' => 'g', 'γγ' => 'g', 'τσ' => 'ts', 'τζ' => 'tz',
		));
		$s = strtr($s, array(
			'α' => 'a', 'β' => 'v', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'i', 'θ' => 'th',
			'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => 'ks', 'ο' => 'o', 'π' => 'p',
			'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'i', 'φ' => 'f', 'χ' => 'h', 'ψ' => 'ps', 'ω' => 'o',
		));

		// Greeklish spellings -> same skeleton (also applied to the Greek-derived text, harmless there)
		$s = strtr($s, array(
			'ph' => 'f', 'ch' => 'h', 'kh' => 'h', 'gh' => 'g', 'dh' => 'd',
			'ou' => 'u', 'ai' => 'e', 'ei' => 'i', 'oi' => 'i',
			'mp' => 'b', 'mb' => 'b', 'nt' => 'd', 'gk' => 'g', 'gg' => 'g', 'ck' => 'k',
		));
		$s = strtr($s, array('w' => 'o', 'y' => 'i', 'x' => 'h', 'c' => 'k', 'q' => 'k', 'b' => 'v', '8' => '8'));

		// collapse doubled letters (λλ/ll, σσ/ss…) and keep letters/digits only
		$s = preg_replace('~([a-z])\1+~', '$1', $s);
		$s = preg_replace('~[^a-z0-9]+~', ' ', $s);

		return trim($s);
	}

	public function words($s) {
		$s = $this->skeleton($s);

		return $s === '' ? array() : array_values(array_unique(explode(' ', $s)));
	}

	/** light Greek stemmer on the skeleton: strip one common ending (≥5 letters, stem ≥3) */
	public function stem($t) {
		if (strlen($t) < 5 || ctype_digit($t)) {
			return $t;
		}

		foreach (array('ies', 'ika', 'iki', 'ikes', 'os', 'es', 'us', 'is', 'on', 'as', 'ia', 'io', 'a', 'e', 'i', 'o', 'u') as $end) {
			$l = strlen($end);

			if (strlen($t) - $l >= 3 && substr($t, -$l) === $end) {
				return substr($t, 0, -$l);
			}
		}

		return $t;
	}

	/** 2 = word is a close inflection of the token (same stem, similar length), 1 = only shares the stem, 0 = none */
	public function stemHit($st, $t, $words) {
		$best = 0;

		foreach ($words as $w) {
			if (strpos($w, $st) === 0) {
				if (strlen($w) <= strlen($t) + 2) {
					return 2;
				}

				$best = 1;
			}
		}

		return $best;
	}

	public function code($s) {
		return preg_replace('~[^a-z0-9]+~', '', $this->fold($s));
	}

	// ---------------------------------------------------------------- index

	public function getIndex() {
		$store_id = (int)$this->config->get('config_store_id');
		$language_id = (int)$this->config->get('config_language_id');
		$key = 'dk_search.index.' . $store_id . '.' . $language_id;

		$index = $this->cache->get($key);

		if ($index && isset($index['time']) && $index['time'] > time() - self::CACHE_TTL) {
			return $index;
		}

		$index = array('time' => time(), 'products' => array(), 'categories' => array(), 'posts' => array(), 'vocab' => array());

		// product -> category names
		$cat_names = array();
		$query = $this->db->query("SELECT p2c.product_id, cd.name FROM `" . DB_PREFIX . "product_to_category` p2c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (cd.category_id = p2c.category_id AND cd.language_id = '" . $language_id . "')");

		foreach ($query->rows as $row) {
			$cat_names[$row['product_id']][] = $row['name'];
		}

		$query = $this->db->query("SELECT p.product_id, p.model, p.sku, p.ean, p.jan, p.mpn, p.quantity, p.viewed, pd.name, pd.tag, pd.meta_keyword, pd.small_description, pd.description, m.name AS manufacturer
			FROM `" . DB_PREFIX . "product` p
			LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id AND pd.language_id = '" . $language_id . "')
			LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.product_id = p2s.product_id)
			LEFT JOIN `" . DB_PREFIX . "manufacturer` m ON (m.manufacturer_id = p.manufacturer_id)
			WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . $store_id . "'");

		foreach ($query->rows as $row) {
			$name_words = $this->words($row['name']);

			foreach (preg_split('~\s+~u', mb_strtolower(strip_tags(html_entity_decode($row['name'], ENT_QUOTES, 'UTF-8')), 'UTF-8')) as $w) {
				// multibyte-safe edge punctuation strip (trim() would cut UTF-8 bytes); keeps accents for display
				$w = preg_replace('~^[^\p{L}\p{N}]+|[^\p{L}\p{N}]+$~u', '', $w);
				$k = $this->skeleton($w);

				if (mb_strlen($w) >= 3 && $k !== '' && strpos($k, ' ') === false) {
					// remember a readable Greek form for each skeleton word (for "did you mean" / search page)
					if (!isset($index['vocab'][$k])) {
						$index['vocab'][$k] = array($w, 0);
					}

					$index['vocab'][$k][1]++;
				}
			}

			$codes = array();

			foreach (array('model', 'sku', 'ean', 'jan', 'mpn') as $f) {
				$c = $this->code($row[$f]);

				if (strlen($c) >= 3) {
					$codes[] = $c;
				}
			}

			$desc = mb_substr(strip_tags(html_entity_decode($row['small_description'] . ' ' . $row['description'], ENT_QUOTES, 'UTF-8')), 0, 600);

			$index['products'][(int)$row['product_id']] = array(
				'n' => $name_words,
				'c' => array_values(array_unique($codes)),
				'k' => $this->words(implode(' ', isset($cat_names[$row['product_id']]) ? $cat_names[$row['product_id']] : array()) . ' ' . $row['manufacturer'] . ' ' . $row['tag'] . ' ' . $row['meta_keyword']),
				'd' => $this->words($desc),
				'v' => (int)$row['viewed'],
				'q' => (int)$row['quantity'] > 0 ? 1 : 0,
			);
		}

		// categories
		$query = $this->db->query("SELECT c.category_id, cd.name, (SELECT GROUP_CONCAT(cp.path_id ORDER BY cp.level SEPARATOR '_') FROM `" . DB_PREFIX . "category_path` cp WHERE cp.category_id = c.category_id) AS path
			FROM `" . DB_PREFIX . "category` c
			LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.category_id = cd.category_id AND cd.language_id = '" . $language_id . "')
			LEFT JOIN `" . DB_PREFIX . "category_to_store` c2s ON (c.category_id = c2s.category_id)
			WHERE c.status = '1' AND c2s.store_id = '" . $store_id . "'");

		foreach ($query->rows as $row) {
			$index['categories'][(int)$row['category_id']] = array('name' => html_entity_decode($row['name'], ENT_QUOTES, 'UTF-8'), 'path' => $row['path'], 'n' => $this->words($row['name']));
		}

		// blog posts (extension/blog)
		$tables = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "blog_description'");

		if ($tables->num_rows) {
			$query = $this->db->query("SELECT b.blog_id, b.image, bd.title, bd.tags FROM `" . DB_PREFIX . "blog` b
				LEFT JOIN `" . DB_PREFIX . "blog_description` bd ON (b.blog_id = bd.blog_id AND bd.language_id = '" . $language_id . "')
				LEFT JOIN `" . DB_PREFIX . "blog_to_store` b2s ON (b.blog_id = b2s.blog_id)
				WHERE b.status = '1' AND b2s.store_id = '" . $store_id . "'");

			foreach ($query->rows as $row) {
				$index['posts'][(int)$row['blog_id']] = array('title' => html_entity_decode($row['title'], ENT_QUOTES, 'UTF-8'), 'image' => $row['image'], 'n' => $this->words($row['title'] . ' ' . $row['tags']));
			}
		}

		$this->cache->set($key, $index);

		return $index;
	}

	// ---------------------------------------------------------------- matching

	private function tokenHits($t, $words, $prefix_only = false) {
		$best = 0;

		foreach ($words as $w) {
			if ($w === $t) {
				return 3;
			}

			if (strpos($w, $t) === 0) {
				$best = max($best, 2);
			} elseif (!$prefix_only && strlen($t) >= 4 && strpos($w, $t) !== false) {
				$best = max($best, 1);
			}
		}

		return $best;
	}

	/** @return array [product_id => score], sorted */
	public function searchProducts($index, $tokens, $code) {
		$scores = array();

		foreach ($index['products'] as $id => $p) {
			$score = 0;
			$all_in_name = true;

			// product code: exact / prefix
			$code_hit = 0;

			if ($code !== '' && strlen($code) >= 3) {
				foreach ($p['c'] as $c) {
					if ($c === $code) {
						$code_hit = 2;
						break;
					}

					if (strpos($c, $code) === 0) {
						$code_hit = max($code_hit, 1);
					}
				}
			}

			$ok = true;

			foreach ($tokens as $i => $t) {
				$h = $this->tokenHits($t, $p['n']);

				if ($h) {
					$score += array(1 => 6, 2 => 10, 3 => 14)[$h];

					if ($i == 0 && $p['n'] && strpos($p['n'][0], $t) === 0) {
						$score += 5;
					}

					// earlier in the title ranks higher ("Λαμπάδες … χονδρική" before "Λαμπαδόκουτο … λαμπάδες")
					foreach ($p['n'] as $pos => $w) {
						if (strpos($w, $t) === 0) {
							$score += max(0, 3 - $pos * 0.5);
							break;
						}
					}

					continue;
				}

				// Greek inflection: γάμος / γάμου / γάμο, λαμπάδα / λαμπάδες
				$st = $this->stem($t);

				if ($st !== $t && ($sh = $this->stemHit($st, $t, $p['n']))) {
					// a close form (κερί / κεριά) scores near an exact hit, a long word on the same stem (κερωμένα) low
					$score += $sh == 2 ? 12 : 4;
					continue;
				}

				$all_in_name = false;

				if ($this->tokenHits($t, $p['k'])) {
					$score += 4;
				} elseif ($this->tokenHits($t, $p['d'], true)) {
					$score += 1;
				} else {
					$ok = false;
					break;
				}
			}

			if ($code_hit) {
				$score += $code_hit == 2 ? 120 : 40;
				$ok = true;
			}

			if (!$ok || !$score) {
				continue;
			}

			if ($all_in_name) {
				$score += 10;
			}

			$score += $p['q'] ? 3 : 0;
			$score += min(4, log(1 + $p['v'], 10));

			$scores[$id] = $score;
		}

		arsort($scores);

		return $scores;
	}

	public function searchList($items, $tokens, $limit) {
		$out = array();

		foreach ($items as $id => $item) {
			$score = 0;

			foreach ($tokens as $t) {
				$h = $this->tokenHits($t, $item['n']);

				if (!$h && ($st = $this->stem($t)) !== $t) {
					$h = $this->tokenHits($st, $item['n'], true) ? 1 : 0;
				}

				if (!$h) {
					$score = 0;
					break;
				}

				$score += $h;
			}

			if ($score) {
				$out[$id] = $score - (count($item['n']) * 0.01);
			}
		}

		arsort($out);

		return array_slice($out, 0, $limit, true);
	}

	/** closest vocabulary word for each token (typo tolerance). Returns [tokens, display words] or null */
	public function suggest($index, $tokens) {
		$fixed = array();
		$display = array();
		$changed = false;

		foreach ($tokens as $t) {
			if (isset($index['vocab'][$t])) {
				$fixed[] = $t;
				$display[] = $index['vocab'][$t][0];
				continue;
			}

			$best = null;
			$best_d = 99;
			$best_n = 0;
			$max = strlen($t) >= 7 ? 2 : (strlen($t) >= 4 ? 1 : 0);

			if ($max) {
				foreach ($index['vocab'] as $w => $info) {
					if (abs(strlen($w) - strlen($t)) > $max) {
						continue;
					}

					$d = levenshtein($t, $w);

					if ($d <= $max && ($d < $best_d || ($d == $best_d && $info[1] > $best_n))) {
						$best = $w;
						$best_d = $d;
						$best_n = $info[1];
					}
				}
			}

			if ($best === null) {
				return null;
			}

			$fixed[] = $best;
			$display[] = $index['vocab'][$best][0];
			$changed = true;
		}

		return $changed ? array($fixed, $display) : null;
	}

	/** Greek display form of a (possibly Greeklish) query, for the full search page */
	public function displayQuery($index, $tokens, $raw) {
		// Greek already, or a product code (digits / dots / dashes) -> keep as typed
		if (preg_match('~\p{Greek}~u', $raw) || preg_match('~^[0-9][0-9a-z .\-/]*$~i', $raw)) {
			return $raw;
		}

		$words = array();

		foreach ($tokens as $t) {
			$words[] = $this->displayWord($index, $t);
		}

		return implode(' ', $words);
	}

	/** readable Greek word for a skeleton token: exact, then prefix, then stem prefix, then closest */
	public function displayWord($index, $t) {
		if (isset($index['vocab'][$t])) {
			return $index['vocab'][$t][0];
		}

		foreach (array($t, $this->stem($t)) as $p) {
			$best = null;
			$best_n = -1;

			foreach ($index['vocab'] as $w => $info) {
				if (strpos($w, $p) === 0 && $info[1] > $best_n) {
					$best = $info[0];
					$best_n = $info[1];
				}
			}

			if ($best !== null) {
				return $best;
			}
		}

		$sug = $this->suggest($index, array($t));

		return $sug ? $sug[1][0] : $t;
	}

	// ---------------------------------------------------------------- popular

	public function getPopular($limit = 8) {
		$key = 'dk_search.popular.' . (int)$this->config->get('config_store_id');
		$popular = $this->cache->get($key);

		if (is_array($popular)) {
			return $popular;
		}

		// group spelling variants ("Λαμπαδες", "λαμπάδες"…) by skeleton; show the accented variant
		$groups = array();
		$query = $this->db->query("SELECT keyword, COUNT(*) AS total FROM `" . DB_PREFIX . "customer_search` WHERE store_id = '" . (int)$this->config->get('config_store_id') . "' AND date_added > DATE_SUB(NOW(), INTERVAL 365 DAY) GROUP BY keyword ORDER BY total DESC LIMIT 300");

		foreach ($query->rows as $row) {
			$k = preg_replace('~\s+~u', ' ', trim(html_entity_decode($row['keyword'], ENT_QUOTES, 'UTF-8')));
			$s = $this->skeleton($k);

			// real Greek searches only (drops "{search_term_string}", bot noise like "rztype_0", codes)
			if (mb_strlen($k) < 3 || mb_strlen($k) > 28 || substr_count($k, ' ') > 2 || $s === '' || !preg_match('~\p{Greek}~u', $k) || preg_match('~[{}_<>]~', $k)) {
				continue;
			}

			if (!isset($groups[$s])) {
				$groups[$s] = array('total' => 0, 'label' => $k, 'accented' => false);
			}

			$groups[$s]['total'] += (int)$row['total'];
			$accented = (bool)preg_match('~[άέήίόύώΐΰ]~u', mb_strtolower($k, 'UTF-8'));

			if ($accented && !$groups[$s]['accented']) {
				$groups[$s]['label'] = $k;
				$groups[$s]['accented'] = true;
			}
		}

		uasort($groups, function ($a, $b) { return $b['total'] - $a['total']; });

		$popular = array();
		$index = $this->getIndex();

		foreach (array_slice($groups, 0, $limit) as $g) {
			// accents from the product names when shoppers typed without them ("λαμπαδες" -> "λαμπάδες")
			$words = array();

			foreach (preg_split('~\s+~u', mb_strtolower($g['label'], 'UTF-8')) as $w) {
				$k = $this->skeleton($w);
				$words[] = ($k !== '' && isset($index['vocab'][$k])) ? $index['vocab'][$k][0] : $w;
			}

			$label = implode(' ', $words);
			$popular[] = mb_strtoupper(mb_substr($label, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($label, 1, null, 'UTF-8');
		}

		$this->cache->set($key, $popular);

		return $popular;
	}
}

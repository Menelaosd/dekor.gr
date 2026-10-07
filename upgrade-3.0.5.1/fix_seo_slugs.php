<?php
// Fixes product SEO URLs that contain spaces/commas (meta keywords pasted into the SEO URL field,
// leading spaces, "SEO URL: ..." prefixes) and adds 301s from the old URL to the new one
// through the iSenseLabs SEO 404-redirect table (it fires only when the old URL 404s).
//
//   php upgrade-3.0.5.1/fix_seo_slugs.php            -> dry run, prints the plan
//   php upgrade-3.0.5.1/fix_seo_slugs.php --apply    -> writes it
//
// Idempotent: a second run finds nothing to fix.
if (PHP_SAPI !== 'cli') {
	exit;
}

require dirname(__DIR__) . '/config.php';

$apply = in_array('--apply', $argv);
$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, (int)DB_PORT);
$db->set_charset('utf8');
$p = DB_PREFIX;

function slugify($s) {
	$map = array('ου' => 'ou', 'ού' => 'ou', 'αι' => 'ai', 'ει' => 'ei', 'οι' => 'oi', 'μπ' => 'mp', 'ντ' => 'nt', 'γκ' => 'gk', 'θ' => 'th', 'χ' => 'x', 'ψ' => 'ps', 'ξ' => 'ks',
		'α' => 'a', 'ά' => 'a', 'β' => 'v', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'έ' => 'e', 'ζ' => 'z', 'η' => 'i', 'ή' => 'i', 'ι' => 'i', 'ί' => 'i', 'ϊ' => 'i', 'ΐ' => 'i',
		'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ο' => 'o', 'ό' => 'o', 'π' => 'p', 'ρ' => 'r', 'σ' => 's', 'ς' => 's', 'τ' => 't', 'υ' => 'y', 'ύ' => 'y', 'ϋ' => 'y', 'ΰ' => 'y',
		'φ' => 'f', 'ω' => 'o', 'ώ' => 'o');
	$s = mb_strtolower(html_entity_decode($s, ENT_QUOTES, 'UTF-8'), 'UTF-8');
	$s = strtr($s, $map);
	$s = preg_replace('~[^a-z0-9]+~', '-', $s);
	return trim(preg_replace('~-+~', '-', $s), '-');
}

function taken($db, $p, $keyword, $store_id, $query) {
	$r = $db->query("SELECT 1 FROM `{$p}seo_url` WHERE keyword = '" . $db->real_escape_string($keyword) . "' AND store_id = " . (int)$store_id . " AND query <> '" . $db->real_escape_string($query) . "' LIMIT 1");
	return $r->num_rows > 0;
}

$rows = $db->query("SELECT s.seo_url_id, s.store_id, s.language_id, s.query, s.keyword, pd.name
	FROM `{$p}seo_url` s
	LEFT JOIN `{$p}product_description` pd ON pd.product_id = SUBSTRING(s.query, 12) AND pd.language_id = s.language_id
	WHERE s.query LIKE 'product_id=%' AND s.keyword REGEXP '[ ,]'");

$n = 0;
foreach ($rows->fetch_all(MYSQLI_ASSOC) as $r) {
	$old = $r['keyword'];
	$clean = trim(preg_replace('~^\s*seo\s*url\s*:\s*~i', '', $old));

	// keep what the shop owner meant when the value starts with a real slug ("psalmos-89-... junk")
	if (preg_match('~^[a-z0-9]+(-[a-z0-9]+){2,}~', $clean, $m)) {
		$new = $m[0];
	} else {
		$new = slugify((string)$r['name']);
	}

	if ($new === '') {
		echo "SKIP {$r['query']} store {$r['store_id']}: no name to build a slug from\n";
		continue;
	}

	$base = $new;
	for ($i = 2; taken($db, $p, $new, $r['store_id'], $r['query']); $i++) {
		$new = $base . '-' . $i;
	}

	$n++;
	echo "{$r['query']} store {$r['store_id']}: [" . $old . "] -> [" . $new . "]\n";

	if (!$apply) {
		continue;
	}

	$db->query("UPDATE `{$p}seo_url` SET keyword = '" . $db->real_escape_string($new) . "' WHERE seo_url_id = " . (int)$r['seo_url_id']);

	// the old URL may have been reached with or without a category path in front of it
	$old_trim = trim($old);
	foreach (array_unique(array_filter(array($old, $old_trim, '*/' . $old, '*/' . $old_trim), function ($v) { return trim($v, '*/ ') !== ''; })) as $from) {
		$e = $db->real_escape_string($from);
		$exists = $db->query("SELECT 1 FROM `{$p}seo_404_redirects` WHERE route_from = '$e' AND store_id = " . (int)$r['store_id'] . " LIMIT 1");
		if (!$exists->num_rows) {
			$db->query("INSERT INTO `{$p}seo_404_redirects` SET route_from = '$e', route_to = '" . $db->real_escape_string($new) . "', store_id = " . (int)$r['store_id'] . ", date_added = NOW(), date_modified = NOW()");
		}
	}
}

echo ($apply ? 'Fixed' : 'Would fix') . " $n SEO URL(s)" . ($apply ? '' : ' (dry run, add --apply)') . "\n";

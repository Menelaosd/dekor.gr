<?php
//ERROR REPORTING
error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors" , 1);
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');
ini_set('max_input_time', 30000);
ini_set('max_execution_time', 30000);
header( 'Content-type: text/html; charset=utf-8' );
set_time_limit(0);

// OPENCART FRAMEWORK
$root = $_SERVER['DOCUMENT_ROOT'].'/';    
if (file_exists($root . 'config.php')) {require_once($root . 'config.php');};
if (file_exists($root . 'system/startup.php')) {require_once($root . 'system/startup.php');};
global $loader,$registry,$config;
$registry = new Registry();	
$loader = new Loader($registry);
$registry->set('load', $loader);
$config = new Config();
$registry->set('config', $config);
$cache = new Cache('file');
$registry->set('cache', $cache);
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);

	function transliterate($string){
		$str = mb_strtolower($string);
 
		//Specific language transliteration.
		//This one is for latin 1, latin supplement , extended A, Cyrillic, Greek
 
		$glyph_array = array(
			'afth'	=>	'αυθ',
			'afk'	=>	'αυκ',
			'afks'	=>	'αυξ',
			'afp'	=>	'αυπ',
			'afs'	=>	'αυσ',
			'aft'	=>	'αυτ',
			'aff'	=>	'αυφ',
			'afx'	=>	'αυχ',
			'afps'	=>	'αυψ',
			'efth'	=>	'ευθ',
			'efk'	=>	'ευκ',
			'efks'	=>	'ευξ',
			'efp'	=>	'ευπ',
			'efs'	=>	'ευσ',
			'eft'	=>	'ευτ',
			'eff'	=>	'ευφ',
			'efx'	=>	'ευχ',
			'efps'	=>	'ευψ',
			'ifth'	=>	'ηυθ',
			'ifk'	=>	'ηυκ',
			'ifks'	=>	'ηυξ',
			'ifp'	=>	'ηυπ',
			'ifs'	=>	'ηυσ',
			'ift'	=>	'ηυτ',
			'iff'	=>	'ηυφ',
			'ifx'	=>	'ηυχ',
			'ifps'	=>	'ηυψ',
			'-b'	=>	'-μπ',
			'-d'	=>	'-ντ',
			'-g'	=>	'-γκ',
			' b'	=>	' μπ',
			' d'	=>	' ντ',
			' g'	=>	' γκ',
			'av'	=>	'αυ',
			'ev'	=>	'ευ',
			'iv'	=>	'ηυ',
			'ou'	=>	'ου',
			'a'		=>	'a,à,á,â,ã,ä,å,ā,ă,ą,ḁ,α,ά',
			'ae'	=>	'æ',
			'b'		=>	'б,^μπ',
			'c'		=>	'c,ç,ć,ĉ,ċ,č,ћ,ц',
			'ch'	=>	'ч',
			'd'		=>	'ď,đ,Ð,д,ђ,δ,ð,^ντ',
			'dz'	=>	'џ',
			'e'		=>	'e,è,é,ê,ë,ē,ĕ,ė,ę,ě,э,ε,έ',
			'f'		=>	'ƒ,ф,φ',
			'g'		=>	'ğ,ĝ,ğ,ġ,ģ,г,γ,^γκ',
			'h'		=>	'ĥ,ħ,Ħ,х',
			'i'		=>	'i,ì,í,î,ï,ı,ĩ,ī,ĭ,į,и,й,ъ,ы,ь,η,ή,ι,ί,ϊ,ΐ',
			'ij'	=>	'ĳ',
			'j'		=>	'ĵ,j',
			'ja'	=>	'я',
			'ju'	=>	'яю',
			'k'		=>	'ķ,ĸ,κ',
			'ks'	=>	'ξ',
			'l'		=>	'ĺ,ļ,ľ,ŀ,ł,л,λ',
			'lj'	=>	'љ',
			'm'		=>	'μ,м',
			'n'		=>	'ñ,ņ,ň,ŉ,ŋ,н,ν',
			'nj'	=>	'њ',
			'o'		=>	'ò,ó,ô,õ,ø,ō,ŏ,ő,ο,ό,ω,ώ',
			'oe'	=>	'œ,ö',
			'p'		=>	'п,π',
			'ps'	=>	'ψ',
			'r'		=>	'ŕ,ŗ,ř,р,ρ',
			's'		=>	'ş,ś,ŝ,ş,š,с,σ,ς',
			'ss'	=>	'ß,ſ',
			'sh'	=>	'ш',
			'shch'	=>	'щ',
			't'		=>	'ţ,ť,ŧ,τ,т',
			'th'	=>	'θ',
			'u'		=>	'u,ù,ú,û,ü,ũ,ū,ŭ,ů,ű,ų,у',
			'v'		=>	'в,β',
			'w'		=>	'ŵ',
			'x'		=>	'χ',
			'y'		=>	'ý,þ,ÿ,ŷ,υ,ύ,ϋ,ΰ',
			'z'		=>	'ź,ż,ž,з,ж,ζ'
		);
 
		foreach($glyph_array as $letter => $glyphs) {
			preg_match_all('/(\^[^,]+(,|$))/', $glyphs, $matches);
			if (count($matches[0])) {
				foreach ($matches[0] as $m) {
                    if (strpos($m, ',')) {
                        $glyphs = str_replace($m, '', $glyphs);
                    }
                    elseif(strpos($glyphs, ',')) {
                        $glyphs = str_replace(','.$m, '', $glyphs);
                    }
                    else {
                        $glyphs = '';
                    }
        			$str = preg_replace('/'.$m.'/', $letter, $str);
                }
			}
			$glyphs = explode(',', $glyphs);
			$str = str_replace($glyphs, $letter, $str);
		}
		return $str;
	}

// --- tiny helpers ---
function oa3_slug($txt) {
	$t = transliterate($txt);
	$t = mb_strtolower($t);
	$t = preg_replace('~[^a-z0-9]+~u', '-', $t); // specials & spaces -> dashes
	$t = trim($t, '-');
	return $t ?: 'n-a';
}
function ensure_dir($absPath) {
	$dir = is_dir($absPath) ? $absPath : dirname($absPath);
	if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
}

function openart3_migrate($db, $catalogDir, $commit = false) {
	// store_id → folder name
	$storeNames = [ 0 => 'dekor', 2 => 'apokrifa' ];

	$preview = []; // grouped by store → product_id

	// 1) products with store from product_to_store
	$sql = "
		SELECT p.product_id,
			   p.model,
			   p.image AS main_image,
			   COALESCE(pts.store_id, 0) AS store_id,
			   (SELECT pd.name FROM ".DB_PREFIX."product_description pd WHERE pd.product_id = p.product_id ORDER BY pd.language_id ASC LIMIT 1) AS name
		FROM ".DB_PREFIX."product p
		LEFT JOIN ".DB_PREFIX."product_to_store pts ON pts.product_id = p.product_id
		ORDER BY p.product_id ASC, pts.store_id ASC
	";
	$products = $db->query($sql)->rows;

	foreach ($products as $p) {
		$pid      = (int)$p['product_id'];
		$name     = (string)$p['name'];
		$model    = (string)$p['model'];
		$store_id = (int)$p['store_id'];
		$mainRel  = (string)$p['main_image'];

		$storeName = isset($storeNames[$store_id]) ? $storeNames[$store_id] : ('store'.$store_id);

		// Only MODEL as folder (slugged); DO NOT use product name as a folder
		$slugModel = oa3_slug($model);
		$combo     = oa3_slug($name.$model); // filenames still name+model

		// Target dir (no name folder)
		$targetDir = "catalog/products/$storeName/$slugModel/"; // relative to /image

		// main filename
		$mainExt = pathinfo($mainRel, PATHINFO_EXTENSION) ?: 'jpg';
		$mainNew = $targetDir.$combo.'.'.$mainExt;

		// 3) additional images
		$extras = [];
		$imgs = $db->query("SELECT image, sort_order FROM ".DB_PREFIX."product_image WHERE product_id = ".$pid." ORDER BY sort_order, image");
		$i = 0;
		foreach ($imgs->rows as $row) {
			$old = (string)$row['image'];
			if ($old === '' || $old === $mainRel) continue;
			$ext = pathinfo($old, PATHINFO_EXTENSION) ?: 'jpg';
			$new = $targetDir.$combo.'_'.$i.'.'.$ext;
			$extras[] = ['old' => $old, 'new' => $new, 'sort_order' => (int)$row['sort_order']];
			$i++;
		}

		// Preview item
		if (!isset($preview[$storeName])) $preview[$storeName] = [];
		$preview[$storeName][$pid] = [
			'product_id' => $pid,
			'model'      => $model,
			'name'       => $name,
			'store_id'   => $store_id,
			'target_dir' => $targetDir,
			'main'       => ['old' => $mainRel, 'new' => $mainNew],
			'extras'     => $extras,
		];

		// 4) Move + update DB
		if ($commit) {
			// MAIN
			if ($mainRel) {
				$src = rtrim($catalogDir, '/').'/'.ltrim($mainRel, '/');
				$dst = rtrim($catalogDir, '/').'/'.ltrim($mainNew, '/');
				if (is_file($src)) {
					ensure_dir($dst);
					if ($src !== $dst) { @rename($src, $dst); }
					$db->query("UPDATE ".DB_PREFIX."product SET image = '".$db->escape($mainNew)."' WHERE product_id = ".$pid);
				}
			}
			// EXTRAS
			foreach ($extras as $ex) {
				$src = rtrim($catalogDir, '/').'/'.ltrim($ex['old'], '/');
				$dst = rtrim($catalogDir, '/').'/'.ltrim($ex['new'], '/');
				if (is_file($src)) {
					ensure_dir($dst);
					if ($src !== $dst) { @rename($src, $dst); }
					$db->query(
						"UPDATE ".DB_PREFIX."product_image SET image = '".$db->escape($ex['new'])."' WHERE product_id = ".$pid." AND image = '".$db->escape($ex['old'])."'"
					);
				}
			}	
		}
	}

	return $preview;
}

// ---- RUN (preview) ----
$plan = openart3_migrate($db, DIR_IMAGE, true);

echo 'UPDATE DONE';

// echo '<pre>'; print_r($plan); echo '</pre>';
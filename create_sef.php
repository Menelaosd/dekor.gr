<?php
	//ERROR REPORTING
	error_reporting(E_ALL & ~E_NOTICE);
	ini_set("display_errors" , 1);
	ini_set('upload_max_filesize', '50M');
	ini_set('post_max_size', '50M');
	ini_set('max_input_time', 30000);
	ini_set('max_execution_time', 30000);
	//ini_set('memory_limit','16M');
	header( 'Content-type: text/html; charset=utf-8' );
	set_time_limit(0);
	//OPENCART FRAMEWORK
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
	
	$all_prod_data = array();
	
	$regular_cat_data = array();
	
	$regular_cats = $db->query("
		SELECT
		CD.category_id,
		CD.name
		FROM ".DB_PREFIX."category_description CD
		WHERE CD.language_id = '2'
	")->rows;	
	
	foreach($regular_cats as $reg_cat) {
		$regular_cat_data['category_id='.$reg_cat['category_id']] = rtrim(str_replace('--','-',str_replace('--','-',preg_replace("/[^a-zA-Z0-9]+/","-",transliterate($reg_cat['name'])))),'-');
	};
	
	$information_data = array();
	
	$information = $db->query("
		SELECT
		DISTINCT
		ID.information_id,
		ID.title
		FROM ".DB_PREFIX."information_description ID
		WHERE ID.language_id = '2'
	")->rows;	
	
	foreach($information as $info) {
		$information_data['information_id='.$info['information_id']] = rtrim(str_replace('--','-',str_replace('--','-',preg_replace("/[^a-zA-Z0-9]+/","-",transliterate($info['title'])))),'-');
	};	
	
	$product_data=array();
	$products = $db->query("
		SELECT
		PD.product_id,
		P.model,
		PD.name
		FROM ".DB_PREFIX."product_description PD
		LEFT JOIN ".DB_PREFIX."product P
		ON (P.product_id = PD.product_id)
		WHERE PD.language_id = '2'
	")->rows;	
	
	foreach($products as $product) {
		$attach = '-'.$product['model'];
		$product_data['product_id='.$product['product_id']] = rtrim(str_replace('--','-',str_replace('--','-',preg_replace("/[^a-zA-Z0-9]+/","-",transliterate($product['name'].$attach)))),'-');
	};

	$all_prod_data = array_merge($information_data,$regular_cat_data,$product_data);
	
	echo '<pre>';
	print_r($all_prod_data);
	echo '</pre>';
	
	$final_array = array();
	
	foreach($all_prod_data as $key => $prodata) {
		$final_array[] = array(
			'seo_url_id' => '',
			'store_id' => '0',
			'language_id' => '2',
			'query' => $key,
			'keyword' => $prodata
		);
	};
	
	$db->query("TRUNCATE TABLE ".DB_PREFIX."seo_url");

	$fp = fopen('image/create_sef_.csv',"w");
	foreach ($final_array as $fields) {
		fputcsv($fp,$fields,';',"\0");
	};
	
	foreach($final_array as $seo_url) {
		$db->query("
		INSERT 
		INTO ".DB_PREFIX."seo_url 
		SET
		store_id = '0',
		language_id = '2',
		query = '".$seo_url['query']."',
		keyword = '".$seo_url['keyword']."'
		");		
	};
	fclose($fp);	
	/*
	$db->query("
	LOAD DATA LOCAL INFILE 'image/create_sef_.csv' INTO TABLE ".DB_PREFIX."seo_url
	FIELDS TERMINATED BY ';' 
	ENCLOSED BY '\0'
	LINES TERMINATED BY '\n'
	");	
	*/	
	if(file_exists('image/create_sef_.csv')) {
		unlink('image/create_sef_.csv');
	};	
	
	$double_slugs = $db->query("
		SELECT query,keyword,COUNT(*) 
		FROM ".DB_PREFIX."seo_url GROUP BY keyword HAVING COUNT(*) > 1 ORDER BY COUNT(*) DESC
	")->rows;	


    $key_replace = array(
        1 => 'a',
        2 => 'b',
        3 => 'c',
        4 => 'd',
        5 => 'e',
        6 => 'f',
        7 => 'g',
        8 => 'h',
        9 => 'i',
        10 => 'j',
        11 => 'k',
        12 => 'l',
        13 => 'm',
        14 => 'n',
        15 => 'o',
        16 => 'p',
        17 => 'q',
        18 => 'r',
        19 => 's',
        20 => 't',
        21 => 'u',
        22 => 'v'
    );
	foreach	($double_slugs as $double_slug) {
		$slug_groups = $db->query("
			SELECT seo_url_id,keyword,query
			FROM ".DB_PREFIX."seo_url
			WHERE keyword = '".$double_slug['keyword']."'
		")->rows;	
		foreach($slug_groups as $key => $slug_group) {
            if($key > 0) {
    			$new_keyword = $slug_group['keyword'].'-'.$key_replace[$key];
    			if (strpos($slug_group['keyword'], '|') !== false) {
    				$new_keyword = str_replace('|','-'.$key_replace[$key].'|',$slug_group['keyword']);
    			}
    			$db->query("
    				UPDATE ".DB_PREFIX."seo_url
    				SET keyword = '".$new_keyword."'
    				WHERE seo_url_id = '".$slug_group['seo_url_id']."'
    			");
            };
		};
	};	
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
?>
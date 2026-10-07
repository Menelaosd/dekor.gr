<?php
namespace Template;
final class Twig {
	private $data = array();
	// dekor: registry for the d_twig_manager Twig extension (passed in by the d_twig_manager OCMOD on Template)
	private $dtm_registry;

	public function __construct($registry = null) {
		$this->dtm_registry = $registry;
	}

	public function set($key, $value) {
		$this->data[$key] = $value;
	}
	
	public function render($filename, $code = '') {
		if (!$code) {
			$file = DIR_TEMPLATE . $filename . '.twig';

			if (is_file($file)) {
				$code = file_get_contents($file);
			} else {
				throw new \Exception('Error: Could not load template ' . $file . '!');
				exit();
			}
		}

		// initialize Twig environment
		$config = array(
			'autoescape'  => false,
			'debug'       => false,
			'auto_reload' => true,
			'cache'       => DIR_CACHE . 'template/'
		);

		try {
			$loader = new \Twig\Loader\ArrayLoader(array($filename . '.twig' => $code));

			// dekor: keep {% include %} of other template files working, as with the Twig 1 filesystem loader of 3.0.2.0
			$paths = array();

			if (defined('DIR_CATALOG') && is_dir(DIR_MODIFICATION . 'admin/view/template/')) {
				$paths[] = DIR_MODIFICATION . 'admin/view/template/';
			} elseif (!defined('DIR_CATALOG') && is_dir(DIR_MODIFICATION . 'catalog/view/theme/')) {
				$paths[] = DIR_MODIFICATION . 'catalog/view/theme/';
			}

			$paths[] = DIR_TEMPLATE;

			$loader = new \Twig\Loader\ChainLoader(array($loader, new \Twig\Loader\FilesystemLoader($paths)));

			$twig = new \Twig\Environment($loader, $config);

			// dekor: d_twig_manager functions/filters/globals (Twig 3 port of Twig_Extension_DTwigManager)
			if ($this->dtm_registry) {
				require_once(DIR_SYSTEM . 'library/template/Twig/Extension/DTwigManager3.php');

				$twig->addExtension(new \Twig_Extension_DTwigManager3($this->dtm_registry));
			}

			return $twig->render($filename . '.twig', $this->data);
		} catch (\Exception $e) {
			trigger_error('Error: Could not load template ' . $filename . '! ' . $e->getMessage());
			exit();
		}	
	}	
}

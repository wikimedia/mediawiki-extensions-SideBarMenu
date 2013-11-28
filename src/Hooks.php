<?php

namespace SideBarMenu;
use ParamProcessor\Processor;
use \SideBarMenu\SubPage\SubPageRenderer;

class Hooks {

	public static function init(\Parser &$parser) {
		$parser->setHook('sidebarmenu', 'SideBarMenu\Hooks::renderSideBarMenuFromTag');
		return true;
	}


	public static function renderSideBarMenuFromTag($input, array $args, \Parser $parser, \PPFrame $frame) {
		try {
			$parser->getOutput()->addModules('ext.sidebarmenu.core');
			$input = $parser->recursiveTagParse($input,$frame);

			if(strpos($input,'#subpage ') !== false){
				//subpages handling
				$parser->disableCache();
				SubPageRenderer::renderSubPages($input);
				$input = str_replace("\n\n","\n",$input);
				$input = $parser->recursiveTagParse($input,$frame);
			}

			//default settings
			$config = self::getTagConfig($args);

			$id = uniqid('sidebar-menu-id-');
			$output = '<div id="'.$id.'" class="sidebar-menu-container'.(is_null($config[SBM_CLASS])? '' : ' '.$config[SBM_CLASS]).'" style="display:none;'.(is_null($config[SBM_STYLE])? '' : $config[SBM_STYLE]).'">';

			$menuParser = new MenuParser($config);
			$output .= $menuParser->getMenuTree($input)->toHTML();

			if ($config[SBM_EDIT_LINK]) {
				$output .= \Linker::link($frame->getTitle(), wfMessage('sidebarmenu-edit')->escaped(), array('title' => wfMessage('sidebarmenu-edit')->escaped(), 'class' => 'sidebar-menu-edit-link'), array('action' => 'edit'));
			}
			$output .= '</div>';

			$jsOutput = self::getJSConfig($config,$id);
			return array($jsOutput . $output, 'noparse' => true, 'isHTML' => true);

		} catch (\Exception $x) {
			wfDebug("An error occured during parsing of: '$input' caught exception: $x");
			return wfMessage('sidebarmenu-parser-input-error', '<strong>'.$x->getMessage()."</strong>\n<pre>$input</pre>")->parse();
		}
	}

	public static function registerUnitTests(&$files) {
		$testDir = dirname(__FILE__) . '/test/';
		$testFiles = scandir($testDir);
		foreach ($testFiles as $testFile) {
			$absoluteFile = $testDir . $testFile;
			if (is_file($absoluteFile)) {
				$files[] = $absoluteFile;
			}
		}
		return true;
	}

	private static function minifyJavascript($js) {
		$js = preg_replace("/[\n\r]/", "", $js); //remove newlines
		$js = preg_replace("/[\s]{2,}/", " ", $js); //remove spaces

		return $js;
	}

	private static function getJSConfig($config,$id) {
		//javascript config output
		$jsonConfig = json_encode($config);
		$jsOutput = <<<EOT
			(function(json,id){
				if(window.sidebarmenu === undefined){
					window.sidebarmenu = {};
				}
				window.sidebarmenu[id] = json;
			})($jsonConfig,'$id');
EOT;


		$jsOutput = \Html::inlineScript($jsOutput);
		//minify js to prevent <p> tags to be rendered
		$jsOutput = self::minifyJavascript($jsOutput);
		return $jsOutput;
	}

	private static function getTagConfig($args) {
		global $wgSideBarMenuConfig;
		$config[SBM_EXPANDED] = array_key_exists(SBM_EXPANDED, $args) ? filter_var($args[SBM_EXPANDED], FILTER_VALIDATE_BOOLEAN) : $wgSideBarMenuConfig[SBM_EXPANDED];
		$config[SBM_CONTROLS_SHOW] = array_key_exists(SBM_CONTROLS_SHOW, $args) ? $args[SBM_CONTROLS_SHOW] : (isset($wgSideBarMenuConfig[SBM_CONTROLS_SHOW]) ? $wgSideBarMenuConfig[SBM_CONTROLS_SHOW] : '[' . wfMessage('showtoc')->escaped() . ']');
		$config[SBM_CONTROLS_HIDE] = array_key_exists(SBM_CONTROLS_HIDE, $args) ? $args[SBM_CONTROLS_HIDE] : (isset($wgSideBarMenuConfig[SBM_CONTROLS_HIDE]) ? $wgSideBarMenuConfig[SBM_CONTROLS_HIDE] : '[' . wfMessage('hidetoc')->escaped() . ']');
		$config[SBM_JS_ANIMATE] =  array_key_exists(SBM_JS_ANIMATE, $args) ? filter_var($args[SBM_JS_ANIMATE], FILTER_VALIDATE_BOOLEAN) : $wgSideBarMenuConfig[SBM_JS_ANIMATE];
		$config[SBM_EDIT_LINK] = array_key_exists(SBM_EDIT_LINK, $args) ? filter_var($args[SBM_EDIT_LINK], FILTER_VALIDATE_BOOLEAN) : $wgSideBarMenuConfig[SBM_EDIT_LINK];
		$config[SBM_CLASS] =  array_key_exists(SBM_CLASS, $args) ? $args[SBM_CLASS] : null;
		$config[SBM_STYLE] =  array_key_exists(SBM_STYLE, $args) ? $args[SBM_STYLE] : null;
		$config[SBM_MINIMIZED] = array_key_exists(SBM_MINIMIZED, $args) ? filter_var($args[SBM_MINIMIZED], FILTER_VALIDATE_BOOLEAN) : $wgSideBarMenuConfig[SBM_MINIMIZED];
		return $config;
	}
}

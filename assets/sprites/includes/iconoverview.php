<?php

// PokéSprite
// ----------
// The use of this source code is governed by the MIT license.
// See the COPYRIGHT file for more information.

namespace PkSpr;

require_once 'icontplfactory.php';

/**
 * Builds an HTML overview of all icons.
 */
class IconOverview extends IconTplFactory
{
    /** @var mixed[] A list of our icons. */
    public $icon_list;
    /** @var ?string[] The generated overview markup, by format. */
    public $overview;
    /** @var ?string[] The generated build overview markup, by format. */
    public $build_overview;
    /** @var string Empty cell content. */
    public $empty_cell = '–';
    
    /**
     * Sets the tree structure of the icon information and coordinates.
     *
     * @param mixed[] $tree Pokémon icon tree structure.
     */
    public function set_icon_list($tree)
    {
        $this->icon_list = $tree;
    }
    
    /**
     * Returns overview markup code.
     *
     * @param string $format The format to generate the overview in.
     * @return string Overview markup code.
     */
    public function get_overview($format='html')
    {
        if (empty($this->overview[$format])) {
            $this->generate_overview($format);
        }
        return $this->overview[$format];
    }
    
    /**
     * Returns build overview markup code.
     *
     * @param string $format The format to generate the overview in.
     * @return string Overview markup code.
     */
    public function get_build_overview($format='html')
    {
        if (empty($this->build_overview[$format])) {
            $this->generate_build_overview($format);
        }
        return $this->build_overview[$format];
    }
    
    /**
     * Generates overview markup code.
     *
     * @param string $format The format to generate the overview in.
     */
    public function generate_overview($format='html')
    {
        if ($format == 'html') {
            $this->generate_overview_html();
        }
        else
        if ($format == 'markdown') {
            $this->generate_overview_markdown();
        }
    }
    
    /**
     * Generates build overview markup code.
     *
     * @param string $format The format to generate the overview in.
     */
    public function generate_build_overview($format='html')
    {
        if ($format == 'html') {
            // Actually, this is the only one we do.
            $this->generate_build_overview_html();
        }
    }
    
    /**
     * Generates overview markup code, Markdown version.
     *
     * This overview includes every single icon individually rather than
     * using the generated sprite image.
     *
     * @return string Overview markup code.
     */
    public function generate_overview_markdown()
    {
    	// Initialize containers.
    	$cols = array('dex', 'name', 'icon', 'class');
    	foreach ($cols as $col) {
    		${$col.'_col'} = array();
    	}
        
        $github_base_dir = Settings::get('github_base_dir');
        
        // Iterate over all icons and their variants and add them to the list.
        foreach ($this->icon_list as $icon) {
            $idx_str = $this->get_icon_idx_str($icon);
            $name_str = $this->get_icon_name_str($icon);
            $classes = $this->get_icon_classes($icon);
            
            // Check to ensure we're not linking to a non-existent file.
            if ($icon['is_duplicate']) {
            	$img_file = $icon['original']['file'];
            }
            else {
	            $img_file = $icon['file'];
	        }
            
            $icon_str = vsprintf('<img src="%s%s" alt="%s" title="%s" width="%d" height="%d" />', array(
            	$github_base_dir,
            	$this->remove_dot_base($img_file),
            	htmlspecialchars($name_str),
            	$this->remove_dot_base($img_file),
            	$icon['w'],
            	$icon['h'],
            ));
            $class_str = vsprintf('**`%s`**', array(implode(' ', $classes)));
            
            // Add an entry in every column.
            $dex_col[] = $idx_str;
            $name_col[] = $name_str;
            $icon_col[] = $icon_str;
            $class_col[] = $class_str;
        }
        
        // Retrieve the string contents of every column.
        $dex_col = $this->md_table_col(I18n::l('overview_dex'), $dex_col, 'left');
        $name_col = $this->md_table_col(I18n::l('overview_name'), $name_col);
        $class_col = $this->md_table_col(I18n::l('overview_class'), $class_col);
        
        // Add &nbsp; characters to the icon column to force the icons
        // to display as 40x30.
        $icon_col = $this->md_table_col(
        	'&nbsp;'.I18n::l('overview_icon').'&nbsp;', $icon_col, 'center'
        );
        
        // Integrate the columns.
        $table = $this->md_table(array(
        	$dex_col, $name_col, $icon_col, $class_col, $file_col,
        ));
        
        $this->overview['markdown'] = $table;
    }
    
    /**
     * Creates a Markdown table out of a list of columns.
     *
     * Assumes all columns have the same amount of rows.
     *
     * @param string[] $cols Columns that make up the content of the table.
     * @return string Markdown table consisting of data from the columns.
     */
    private function md_table($cols)
    {
    	$lines = array();
    	
    	// First, find out how many rows we have.
    	// We assume all columns have the same amount of rows.
    	$first = reset($cols);
    	$amount = count($first);
    	
    	// Iterate over every row, adding lines from every column.
    	for ($a = 0; $a < $amount; ++$a) {
    		$line = '';
    		foreach ($cols as $col) {
    			$line .= $col[$a];
    		}
    		$line .= '|';
    		$lines[] = $line;
    	}
    	return implode("\n", $lines)."\n";
    }
    
    /**
     * Removes the ./ base from a path in case it exists.
     *
     * @param string $path Path to a file or directory.
     * @return string Path without ./ prefix.
     */
    private function remove_dot_base($path)
    {
    	if (strpos($path, './') === 0) {
    		$path = substr($path, 2);
    	}
    	return $path;
    }
    
    /**
     * Returns a rendered Markdown table column as an array of strings.
     *
     * Every string in the array will have the same length.
     *
     * @param string $name Header title of the column.
     * @param string[] $rows Column rows.
     * @param string $align Column alignment (left|center|right).
     * @return string[] Column lines.
     */
    private function md_table_col($name, $rows, $align='left')
    {
    	// First, find out what the length of every line should be.
    	$len = strlen((string)$name);
    	foreach ($rows as $row) {
    		$row_len = mb_strlen((string)$row);
    		if ($len < $row_len) {
    			$len = $row_len;
    		}
    	}
    	
    	// Set up the templates.
    	$tpl_row = '| %'.($align == 'right' ? '' : '-').$len.'s ';
    	$tpl_sep = '|%s%\'-'.$len.'s%s';
    	$colon_l = ($align == 'left' || $align == 'center') ? ':' : ' ';
    	$colon_r = ($align == 'right' || $align == 'center') ? ':' : ' ';
    	
    	// Now render the lines.
    	// Fixme: this doesn't work very well with Unicode strings.
    	$col = array();
    	$col[] = sprintf($tpl_row, $name);
    	$col[] = sprintf($tpl_sep, $colon_l, '-', $colon_r);
    	foreach ($rows as $row) {
    		$col[] = sprintf($tpl_row, $row);
    	}
    	return $col;
    }
    
    /**
     * Returns the classes required to get an icon to display in HTML.
     *
     * @return string[] Icon HTML classes.
     */
    private function get_icon_classes($icon)
    {
    	// Base icon selection classes.
        $cls_base = Settings::get('css_base_selector');
        $cls_shiny = 'color-shiny';
        $cls_female = 'gender-female';
        $cls_right = 'dir-right';
        
        // Whether to use slug classes or Pokédex classes in the overview.
        // (For debugging only.)
        $use_idx_classes = Settings::get('html_no_slugs');
        
        // If we're only using shiny icons, omit the "color-shiny" class,
        // as no regular icons means the fallback will always be shiny.
        $only_shiny = (
            Settings::get('include_pkmn_nonshiny') == false &&
            Settings::get('include_pkmn_shiny') == true
        );
        
		$classes = array($cls_base);
		
		if ($icon['type'] == 'pkmn') {
			// For Pokémon sprite icons.
			if ($use_idx_classes) {
				$classes[] = $icon['type'].'-'.$icon['id'];
			}
			else {
				$classes[] = $icon['type'].'-'.$icon['slug'];
			}
			if ($icon['version'] != 'regular' && !$only_shiny) {
				$classes[] = $cls_shiny;
			}
			if ($icon['variation'] != '.') {
				$classes[] = 'form-'.$icon['variation'];
			}
			if ($icon['subvariation'] == 'female') {
				$classes[] = $cls_female;
			}
			if ($icon['subvariation'] == 'right'
			||  $icon['subvariation'] == 'flipped') {
				$classes[] = $cls_right;
			}
		}
		else {
			// For all other icon types.
			$classes[] = $icon['set'].'-'.$icon['slug'];
		}
		
		return $classes;
    }
    
    /**
     * Returns the icon's Pokédex number, or an empty string.
     *
     * @return string Dex number, or empty string.
     */
    private function get_icon_idx_str($icon)
    {
    	$idx = $icon['idx'];
    	$idx_dex = sprintf(I18n::l('dex_prefix').'%03d', $idx);
    	return empty($idx) ? $this->empty_cell_content : $idx_dex;
    }
    
    /**
     * Returns the icon's name string.
     *
     * @return string Name string.
     */
    private function get_icon_name_str($icon)
    {
        $name = $icon['name_display'];
    	return empty($name) ? $this->empty_cell_content : $name;
    }
    
    /**
     * Generates build overview markup code, HTML version.
     *
     * @return string Build overview markup code.
     */
    public function generate_build_overview_html()
    {
        // Allow the template to use the minified version too.
        $js_output_min = str_replace(
            '.js',
            '.min.js',
            Settings::get('js_output')
        );
        
        // Decorate the template with our generated markup.
        $markup = $this->decorate_tpl_with_defaults($this->tpl, array(
            'resources_dir' => Settings::get('resources_dir'),
            'js_output' => Settings::get('js_output'),
            'js_output_min' => str_replace('.js', '.min.js', Settings::get('js_output')),
            'html_rel_home' => Settings::get('html_rel_home'),
            'icons_amount' => htmlspecialchars(count($this->icon_list)),
            'script_date' => Settings::get('script_date'),
            'css_output' => str_replace('.scss', '.css', Settings::get('scss_output')),
            'css_output_min' => str_replace('.scss', '.min.css', Settings::get('scss_output')),
            'title_str_html' => htmlspecialchars(Settings::get('title_str')),
            'script_date_html' => htmlspecialchars(Settings::get('script_date')),
            'website_html' => htmlspecialchars(Settings::get('website')),
        ));
        
        $this->build_overview['html'] = $this->process_output($markup);
    }
    
    /**
     * Generates overview markup code, HTML version.
     *
     * @return string Overview markup code.
     */
    public function generate_overview_html()
    {
        // Keep track of which Pokémon we're at, to use rowspan.
        $curr_pkmn = '';
        $first_icon = true;
        
        // Empty cells will get this content.
        $empty_cell = $this->empty_cell_content;
        
        ob_start();
?>
<table class="table pkspr-overview">
  <tr>
    <th class="n"><p><?= I18n::l('overview_id'); ?><span class="spacer">000</span></p></th>
    <th class="idx"><p><?= I18n::l('overview_dex'); ?></p></th>
    <th class="name"><p><?= I18n::l('overview_name'); ?></p></th>
    <th class="icon"><p><?= I18n::l('overview_icon'); ?></p></th>
    <th class="example"><p><?= I18n::l('overview_html'); ?></p></th>
  </tr>
<?php
        $n = 0;
        // Iterate over all icons and their variants and add them to the list.
        foreach ($this->icon_list as $icon) {
            if ($curr_pkmn === '') {
                $curr_pkmn = $icon['id'];
                $first_icon = true;
                
                // How many more icons does this ID get?
                $pokeid_icons = 0;
                for ($m = $n; $m < count($this->icon_list); ++$m) {
                    if ($this->icon_list[$m]['id'] !== $curr_pkmn) {
                        break;
                    }
                    $pokeid_icons += 1;
                }
            }
            
            $idx_str = $this->get_icon_idx_str($icon);
            $name_str = $this->get_icon_name_str($icon);
            $classes = $this->get_icon_classes($icon);
            
            $classes_str = implode(' ', $classes);
            $classes_str_safe = htmlspecialchars($classes_str);
            $html_id = 'icon_'.$n;
            
            // Check to ensure we're not linking to a non-existent file.
            if ($icon['is_duplicate']) {
            	$img_file = $icon['original']['file'];
            }
            else {
	            $img_file = $icon['file'];
	          }
	        
            $img_str = '<span id="'.$html_id.'" class="'.$classes_str.'"></span>';
            $decorator = '<script>PkSpr.decorate(\''.$html_id.'\');</script>';
            $example_str = '<pre><code>'.htmlspecialchars('<span class="').'<span class="class">'.$classes_str_safe.'</span>'.htmlspecialchars('"></span>').'</code></pre>';
            $file_link = Settings::get('icon_url_img_base').$this->remove_dot_base($img_file);
?>
  <tr>
    <td class="n"><p><a href="<?= $file_link; ?>"><?= $n; ?></a></p></td>
<?php
            if ($first_icon) {
?>
    <td class="idx" rowspan="<?= $pokeid_icons; ?>"><p class="idx"><?= $idx_str; ?></p></td>
    <td class="name" rowspan="<?= $pokeid_icons; ?>"><p><?= $name_str; ?></p></td>
<?php
            }
?>
    <td class="icon"><p><?= $img_str.$decorator; ?></p></td>
    <td class="example"><?= $example_str; ?></td>
  </tr>
<?php
            $n += 1;
            $pokeid_icons -= 1;
            $first_icon = false;
            if ($pokeid_icons <= 0) {
                $curr_pkmn = '';
            }
        }
?>
</table>
<?php
        $icons = ob_get_clean();
        
        // Allow the template to use the minified version too.
        $js_output_min = str_replace(
            '.js',
            '.min.js',
            Settings::get('js_output')
        );
        
        // Decorate the template with our generated markup.
        $markup = $this->decorate_tpl_with_defaults($this->tpl, array(
            'icons' => $this->indent_lines($icons, 4),
            'resources_dir' => Settings::get('resources_dir'),
            'js_output' => Settings::get('js_output'),
            'js_output_min' => $js_output_min,
            'html_rel_home' => Settings::get('html_rel_home'),
            'script_date' => Settings::get('script_date'),
            'css_output' => str_replace('.scss', '.css', Settings::get('scss_output')),
            'css_output_min' => str_replace('.scss', '.min.css', Settings::get('scss_output')),
            'title_str_html' => htmlspecialchars(Settings::get('title_str')),
            'script_date_html' => htmlspecialchars(Settings::get('script_date')),
            'icons_amount' => htmlspecialchars(count($this->icon_list)),
            'website_html' => htmlspecialchars(Settings::get('website')),
        ));
        
        $this->overview['html'] = $this->process_output($markup);
    }
}

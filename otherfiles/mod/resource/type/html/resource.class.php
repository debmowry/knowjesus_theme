<?php // $Id$

class resource_html extends resource_base {


function resource_html($cmid=0) {
    parent::resource_base($cmid);
}

function add_instance($resource) {
    $this->_postprocess($resource);
    return parent::add_instance($resource);
}


function update_instance($resource) {
    $this->_postprocess($resource);
    return parent::update_instance($resource);
}

function _postprocess(&$resource) {
    global $RESOURCE_WINDOW_OPTIONS;
    $alloptions = $RESOURCE_WINDOW_OPTIONS;

    if ($resource->windowpopup) {
        $optionlist = array();
        foreach ($alloptions as $option) {
            $optionlist[] = $option."=".$resource->$option;
            unset($resource->$option);
        }
        $resource->popup = implode(',', $optionlist);
        unset($resource->windowpopup);
        $resource->options = '';

    } else {
        if (empty($resource->blockdisplay)) {
            $resource->options = '';
        } else {
            $resource->options = 'showblocks';
        }
        unset($resource->blockdisplay);
        $resource->popup = '';
    }
}


function display() {
    global $CFG;

    $formatoptions = new object();
    $formatoptions->noclean = true;

    /// Set up some shorthand variables
    $cm = $this->cm;
    $course = $this->course;
    $resource = $this->resource;

    // fix for MDL-9021, thanks Etienne Roz
    // fix for MDL-15387, thanks to John Beedell
    add_to_log($course->id, "resource", "view", "view.php?id={$cm->id}", $resource->id, $cm->id);

    /// Are we displaying the course blocks?
    if ($this->resource->options == 'showblocks') {
                
        require_once($CFG->libdir.'/blocklib.php');
        require_once($CFG->libdir.'/pagelib.php');
        require_once($CFG->dirroot.'/course/lib.php'); //required by some blocks

        $PAGE = page_create_object(PAGE_COURSE_VIEW, $this->course->id);
        $this->PAGE = $PAGE;
        $pageblocks = blocks_setup($PAGE);

        $blocks_preferred_width = bounded_number(180, blocks_preferred_width($pageblocks[BLOCK_POS_LEFT]), 210);

    /// Print the page header

        $edit = optional_param('edit', -1, PARAM_BOOL);

        if (($edit != -1) and $PAGE->user_allowed_editing()) {
            $USER->editing = $edit;
        }

        $morenavlinks = array($this->strresources   => 'index.php?id='.$this->course->id,
                                 $this->resource->name => '');

        $PAGE->print_header($this->course->shortname.': %fullname%', $morenavlinks, "", "", 
                            update_module_button($this->cm->id, $this->course->id, $this->strresource));

        $lt = (empty($THEME->layouttable)) ? array('middle', 'left', 'right') : $THEME->layouttable;
        foreach ($lt as $column) {
            switch ($column) {
                case 'middle':
                    echo '<article class="themecolumn">';
                    echo format_text($this->resource->alltext, FORMAT_HTML, $formatoptions, $this->course->id);
                    echo '</article>';
                break;

                case 'left': // The left column
                    if (blocks_have_content($pageblocks, BLOCK_POS_LEFT) || $editing) {
                        echo '<section class="themecolumn sideblock left-sideblock">';
                        // print_container_start(); // Added 24 Jan 2013 to remove excess div tags
                        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_LEFT);
                        // print_container_end(); // Added 24 Jan 2013 to remove excess div tags
                        echo '</section>';
                    }
                break;
                
                case 'right': // The right column
                    if (blocks_have_content($pageblocks, BLOCK_POS_RIGHT) || $editing) {
                        echo '<section class="themecolumn sideblock right-sideblock">';
                        // print_container_start(); // Added 24 Jan 2013 to remove excess div tags
                        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_RIGHT);
                        // print_container_end(); // Added 24 Jan 2013 to remove excess div tags
                        echo '</section>';
                    }
                break;                
            }
        }
        print_footer($this->course);
        
    } else {

        /// Set up generic stuff first, including checking for access
        parent::display();

        $pagetitle = strip_tags($course->shortname.': '.format_string($resource->name));
        $inpopup = optional_param('inpopup', '', PARAM_BOOL);
        
        if ($resource->popup) {
            if ($inpopup) {                    /// Popup only
                
                print_header();
                print_simple_box(format_text($resource->alltext, FORMAT_HTML, $formatoptions, $course->id),
                        "center clearfix", "", "", "20");
                print_footer($course);
            } else {                           /// Make a page and a pop-up window
                $navigation = build_navigation($this->navlinks, $cm);
                
                print_header($pagetitle, $course->fullname, $navigation,
                        "", "", true, update_module_button($cm->id, $course->id, $this->strresource),
                        navmenu($course, $cm));

                echo "\n<script type=\"text/javascript\">";
                echo "\n//<![CDATA[\n";
                echo "openpopup('/mod/resource/view.php?inpopup=true&id={$cm->id}','resource{$resource->id}','{$resource->popup}');\n";
                echo "\n//]]>\n";
                echo '</script>';

                if (trim(strip_tags($resource->summary))) {
                    print_simple_box(format_text($resource->summary, FORMAT_MOODLE, $formatoptions, $course->id), "center clearfix");
                }

                $link = "<a href=\"$CFG->wwwroot/mod/resource/view.php?inpopup=true&amp;id={$cm->id}\" onclick=\"this.target='resource{$resource->id}'; return openpopup('/mod/resource/view.php?inpopup=true&amp;id={$cm->id}', 'resource{$resource->id}','{$resource->popup}');\">".format_string($resource->name,true)."</a>";

                echo '<div class="popupnotice">';
                print_string('popupresource', 'resource');
                echo '<br />';
                print_string('popupresourcelink', 'resource', $link);
                echo '</div>';

                print_footer($course);
            }
        } else {    /// not a popup at all
            $navigation = build_navigation($this->navlinks, $cm);
            
            print_header($pagetitle, $course->fullname, $navigation,
                    "", "", true, update_module_button($cm->id, $course->id, $this->strresource),
                    navmenu($course, $cm));

            print_simple_box(format_text($resource->alltext, FORMAT_HTML, $formatoptions, $course->id), "center clearfix", "", "", "20");

            $strlastmodified = get_string("lastmodified");
            echo "<div class=\"modified\">$strlastmodified: ".userdate($resource->timemodified)."</div>";

            print_footer($course);
        }

    }

}

function setup_preprocessing(&$defaults){

    if (!isset($defaults['popup'])) {
        // use form defaults

    } else if (!empty($defaults['popup'])) {
        $defaults['windowpopup'] = 1;
        if (array_key_exists('popup', $defaults)) {
            $rawoptions = explode(',', $defaults['popup']);
            foreach ($rawoptions as $rawoption) {
                $option = explode('=', trim($rawoption));
                $defaults[$option[0]] = $option[1];
            }
        }
    } else {
        $defaults['windowpopup'] = 0;
        if (array_key_exists('options', $defaults)) {
            $defaults['blockdisplay'] = ($defaults['options']=='showblocks');
        }
    }
}

function setup_elements(&$mform) {
    global $CFG, $RESOURCE_WINDOW_OPTIONS;

    $mform->addElement('htmleditor', 'alltext', get_string('fulltext', 'resource'), array('cols'=>85, 'rows'=>30));
    $mform->setType('alltext', PARAM_RAW);
    $mform->setHelpButton('alltext', array('reading', 'writing', 'richtext'), false, 'editorhelpbutton');
    $mform->addRule('alltext', get_string('required'), 'required', null, 'client');

    $mform->addElement('header', 'displaysettings', get_string('display', 'resource'));

    $woptions = array(0 => get_string('pagewindow', 'resource'), 1 => get_string('newwindow', 'resource'));
    $mform->addElement('select', 'windowpopup', get_string('display', 'resource'), $woptions);
    $mform->setDefault('windowpopup', !empty($CFG->resource_popup));

    $mform->addElement('checkbox', 'blockdisplay', get_string('showcourseblocks', 'resource'));
    $mform->setDefault('blockdisplay', 0);
    $mform->disabledIf('blockdisplay', 'windowpopup', 'eq', '1');
    $mform->setAdvanced('blockdisplay');

    foreach ($RESOURCE_WINDOW_OPTIONS as $option) {
        if ($option == 'height' or $option == 'width') {
            $mform->addElement('text', $option, get_string('new'.$option, 'resource'), array('size'=>'4'));
            $mform->setDefault($option, $CFG->{'resource_popup'.$option});
            $mform->disabledIf($option, 'windowpopup', 'eq', '0');
        } else {
            $mform->addElement('checkbox', $option, get_string('new'.$option, 'resource'));
            $mform->setDefault($option, $CFG->{'resource_popup'.$option});
            $mform->disabledIf($option, 'windowpopup', 'eq', '0');
        }
        $mform->setAdvanced($option);
    }
}


}

?>

<?PHP // $Id: view.php,v 1.4.2.1 2008/03/25 20:13:54 poltawski Exp $

require_once('../../config.php');
require_once('lib.php');

$id        = required_param('id', PARAM_INT);           // Course Module ID
$chapterid = optional_param('chapterid', 0, PARAM_INT); // Chapter ID
$edit      = optional_param('edit', -1, PARAM_BOOL);     // Edit mode

// =========================================================================
// security checks START - teachers edit; students view
// =========================================================================
if (!$cm = get_coursemodule_from_id('book', $id)) {
    error('Course Module ID was incorrect');
}

if (!$course = get_record('course', 'id', $cm->course)) {
    error('Course is misconfigured');
}

if (!$book = get_record('book', 'id', $cm->instance)) {
    error('Course module is incorrect');
}

require_course_login($course, true, $cm);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
$allowedit = has_capability('moodle/course:manageactivities', $context);

if ($allowedit) {
    if ($edit != -1) {
        $USER->editing = $edit;
    } else {
        if (isset($USER->editing)) {
            $edit = $USER->editing;
        } else {
            $edit = 0;
        }
    }
} else {
    $edit = 0;
}

/// read chapters
$select = $allowedit ? "bookid = $book->id" : "bookid = $book->id AND hidden = 0";
$chapters = get_records_select('book_chapters', $select, 'pagenum', 'id, pagenum, subchapter, title, hidden');

if (!$chapters) {
    if ($allowedit) {
        redirect('edit.php?id='.$cm->id); //no chapters - add new one
        die;
    } else {
        error('Error reading book chapters.');
    }
}
/// check chapterid and read chapter data
if ($chapterid == '0') { // go to first chapter if no given
    foreach($chapters as $ch) {
        if ($allowedit) {
            $chapterid = $ch->id;
            break;
        }
        if (!$ch->hidden) {
            $chapterid = $ch->id;
            break;
        }
    }
}


if (!$chapter = get_record('book_chapters', 'id', $chapterid)) {
    error('Error reading book chapters.');
}

//check all variables
unset($id);
unset($chapterid);

/// chapter is hidden for students
if (!$allowedit and $chapter->hidden) {
    error('Error reading book chapters.');
}

/// chapter not part of this book!
if ($chapter->bookid != $book->id) {
    error('Chapter not part of this book!');
}
// =========================================================================
// security checks  END
// =========================================================================

add_to_log($course->id, 'book', 'view', 'view.php?id='.$cm->id.'&amp;chapterid='.$chapter->id, $book->id, $cm->id);


///read standard strings
$strbooks = get_string('modulenameplural', 'book');
$strbook  = get_string('modulename', 'book');
$strTOC = get_string('TOC', 'book');

/// prepare header
$navigation = build_navigation('', $cm);

$buttons = $allowedit ? '<table cellspacing="0" cellpadding="0"><tr><td>'.update_module_button($cm->id, $course->id, $strbook).'</td>'.
           '<td>&nbsp;</td><td>'.book_edit_button($cm->id, $course->id, $chapter->id).'</td></tr></table>'
           : '&nbsp;';

print_header( "$course->shortname: $book->name ($chapter->title)",
              $course->fullname,
              $navigation,
              '',
              '<style type="text/css">@import url('.$CFG->wwwroot.'/mod/book/book_theme.css);</style>',
              true,
              $buttons,
              navmenu($course, $cm)
            );


/// prepare chapter navigation icons
$previd = null;
$nextid = null;
$found = 0;
foreach ($chapters as $ch) {
    if ($found) {
        $nextid= $ch->id;
        break;
    }
    if ($ch->id == $chapter->id) {
        $found = 1;
    }
    if (!$found) {
        $previd = $ch->id;
    }
}
if ($ch == current($chapters)) {
    $nextid = $ch->id;
}
$chnavigation = '<div class="nav">';

if ($previd) {
    // Searches to see if current theme is any of the responsive 'KnowJesus' themes.
    if (strpos(current_theme(), 'knowjesus') !== FALSE) {
    // if (current_theme() == "knowjesus-en") {
        $chnavigation .= '<a class="kjbutton" title="'.get_string('navprev', 'book').'" href="view.php?id='.$cm->id.'&amp;chapterid='.$previd.'">'.get_string('navprev', 'book').'</a>';
    }
} else {
    $chnavigation .= '<img src="pix/nav_prev_dis.gif" class="bigicon" alt="" />';
}
if ($nextid) {
    if (strpos(current_theme(), 'knowjesus') !== FALSE) {
    // if (current_theme() == "knowjesus-en") {
        $chnavigation .= '<a class="kjbutton" title="'.get_string('navnext', 'book').'" href="view.php?id='.$cm->id.'&amp;chapterid='.$nextid.'">'.get_string('navnext', 'book').'</a>';
    }
} else {
    $sec = '';
    if ($section = get_record('course_sections', 'id', $cm->section)) {
        $sec = $section->section;
    }
        if (strpos(current_theme(), 'knowjesus') !== FALSE) {
        // if (current_theme() == "knowjesus-en") {
        $chnavigation .= '<a class="kjbutton-g" title="'.get_string('navexit', 'book').'" href="../../course/view.php?id='.$course->id.'#section-'.$sec.'">'.get_string('navexit', 'book').'</a>';
    }
}
$chnavigation .= '</div>';


/// prepare print icons
if ($book->disableprinting) {
    $printbook = '';
    $printchapter = '';
} else {
    if (strpos(current_theme(), 'knowjesus') !== FALSE) {
    // if (current_theme() == "knowjesus-en") {
        $printbook = '<a title="'.get_string('printbook', 'book').'" href="print.php?id='.$cm->id.'" onclick="this.target=\'_blank\'">'.get_string('printbook', 'book').'</a>';
        $printchapter = '<a title="'.get_string('printchapter', 'book').'" href="print.php?id='.$cm->id.'&amp;chapterid='.$chapter->id.'" onclick="this.target=\'_blank\'">'.get_string('printchapter', 'book').'</a>';        
    }
}

// prepare $toc and $currtitle, $currsubtitle
require('toc.php');

if ($edit) {
    $tocwidth = $CFG->book_tocwidth + 80;
} else {
    $tocwidth = $CFG->book_tocwidth;
}

$doimport = ($allowedit and $edit) ? '<a href="import.php?id='.$cm->id.'">'.get_string('doimport', 'book').'</a>' : '';

/// Enable the IMS CP button
$generateimscp = ($allowedit) ? '<a title="'.get_string('generateimscp', 'book').'" href="generateimscp.php?id='.$cm->id.'"><img class="bigicon" src="pix/generateimscp.gif" height="24" width="24" border="0"></img></a>' : '';


// =====================================================
// Book display HTML code
// =====================================================

?>

<!-- subchapter title and upper navigation row //-->
<?php
echo ('<h2 class="headingblock">'. $book->name.'</h2>');
if (!empty($doimport)) {
    echo "<br/>($doimport)";
}
?>
<div class="generateimscp"><?php echo $generateimscp ?><?php echo $chnavigation; ?></div>

<!-- toc and chapter row //-->
<?php
if ($allowedit and $edit) {
    echo '<div class="faq">';
    helpbutton('faq', get_string('faq','book'), 'book', true, true);
    echo '</div>';
}

print_box_start('bookcontainer');
if (!$book->customtitles) {
  if ($currsubtitle == '&nbsp;') {
      echo '<h1 class="book_chapter_title">'.$currtitle.'</h1>';
  } else {
      echo '<h1 class="book_chapter_title">'.$currtitle.'<br />'.$currsubtitle.'</h1>';
  }
}
echo $toc;
$content = '';
$content .= $chapter->content;

$nocleanoption = new object();
$nocleanoption->noclean = true;
echo format_text($content, FORMAT_HTML, $nocleanoption, $course->id);
print_box_end();

/// lower navigation

echo '<div class="footer">'.'<div class="print">'.$printbook.$printchapter.'</div>'.$chnavigation.'</div>';
?>

<?php
print_footer($course);

?>

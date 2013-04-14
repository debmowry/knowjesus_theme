knowjesus_theme
===============

Responsive theme for KnowJesus Moodle instance. Images are copyrighted to BGEA.

This is the English language modifications for the Moodle 1.9.x KnowJesus Discipleship Course.
This can be used as a starting point for other Moodle 1.9.x themes.

The KnowJesus Discipleship Course does not use every type of activity or resource available to
a Moodle course so only types used have been modified. They can be found within the "otherfiles"
directory within this theme directory. They should be moved into the appropriate folders within
the Moodle installation. For instance:
index.php found in otherfiles/moodlerootdir should be placed in the Moodle root directory.

Avetica had made custom modifications to eotopics and the book module so theme modifications were
made from those modified files rather than Moodle core module files.

The non-theme files, such as index.php, have been modified in such a way that Moodle display
will not break if a non-KnowJesus theme is selected by in if statement similar to the following:

$customtheme = substr(current_theme(), 0, 9);
if ($customtheme != 'knowjesus') { /* If responsive theme is not selected the
    following code is used otherwise it will skip this section and jump to the
    code needed for the responsive layout. */

The theme uses some HTML5 semantic tags such as <header>, <footer>, <article>, and <section>
and CSS3 to provide structure and replace some graphics with text which are styled to look
like buttons.

<?php
/**
 *
 * Short Description of the file
 *
 * Long Description of the file (if any)...
 *
 * @author Nicola Wilkinson
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 0.0.0.1
 * @since 10 Aug 2007
 *
 */

//index page
define('WELCOME' ,'Welcome to WebPA, the easiest way for your students to carry out peer assessment reviews on the web. Using this system, students doing group work activities can mark each other\'s contributions, providing each student with an overall score.');
define('SECTIONS__INTRO', 'WebPA contains the following sections:');
define('OPT__FORMS__DESC', 'Create peer assessment forms for your students to complete. You can re-use your forms with many different assessments.');
define('OPT__GROUPS__DESC', 'Organise your students into groups. You can create new groups from scratch, or use existing groups that have been set up by other staff members.');
define('OPT__ASSESSMENTS__DESC', 'Create, edit and schedule your peer assessments sessions so they only run how and when you want.');
define('GETTING__STARTED__TITLE', 'Getting Started');
define('GETTING__STARTED__DESC', 'The fastest way to get started is for you to choose <a href="forms/">my forms</a> from the left-hand menu, there you can begin creating a peer assessment form that your students will use later to grade each other.');

//groups index page
define('GROUPS__WELCOME', 'Here you can edit your groups, and organise how students are allocated to the individual groups.');
define('GROUPS__TITLE', 'Existing Groups');
define('NO__GROUPS__DESC', 'You have no groups. To add a collection use the <a href="create/">create new groups wizard</a>.');
define('GROUPS__INSTRUCT__1','These are your groups. To view or edit a collection of groups, click on ');
define('GROUPS__INSTRUCT__2','in the list below.');

define('GROUPS__NOTE', 'Any changes you make to your groups here will <strong>not</strong> affect any assessments you may have created. If you want to change the groups in use with an assessment, you must edit the assessment and choose the option to select a new set of groups.');

//groups edit index page
define ('GROUPS__EDIT__DESC', 'Here you can edit your collections of groups, and organise how students are allocated to the individual groups.');
define ('GROUPS__EDIT_TITLE', 'Group Collections');
define ('NO_COLLECTIONS','You have no collections. To add a collection use the <a href="../create/">create new groups wizard</a>.');
define ('GROUPS__EDIT__INST', 'These are your group collections. To view or edit a collection, click on its name in the list below:');

//groups edit edit_group page
define ('GROUPS__EDIT_SAVE_ERR', 'You must give this group a name.');

define ('COLLECTION__LOCKED', '<p>This group belongs to a collection that has been locked, and cannot be edited. You can still view the details of this group, but not edit its name or members.</p>');

define ('GROUPS__EDIT_INST', '<p>On this page you can change the name of this group, and add/remove students from it.</p>');
define ('GROUP__SELECTED', '<p>The group you selected could not be loaded for some reason - please go back and try again.</p>');

?>
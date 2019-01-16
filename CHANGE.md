Version Information
===================
Version 3.2.0
  1. Removed gravatar code and replaced images with core_course\external\course_summary_exporter::get_course_pattern.
Version 3.1.0
  1. Refactored JS to fix bugs with scrolling and reloading.
  2. Used delegation to assign click listener.
  3. Changed timed reload to simulate click instead of just calling function reloadevents(Specific backwards compatoble change for our Org).
Version 3.0.2 
  1. Changed template to display description instead of just event name.
Version 3.0.0 
  1. Refactored block to use new calendar api.
  2. Refactored block to use renderables and templates.
  3. Added privacy api functions.

Version 2.3.0 
  1. Added 0 to array of lookahead days to allow for displaying only todays events.
  2. Added lookahead config to block instance.

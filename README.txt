EX_BATCH_DRUSH9
===============

This is a simple example of creating a custom Drush 9 command to launch
a batch process.

Read the blog post here:
http://karimboudjema.com/en/drupal/20181127/how-run-batch-processing-custom-drush-9-command-drupal-8

This kind of command can be launched by a *nix crontab or by the
console command line.

This command will first load the nid of the content type passed as argument.
Next it will launch a batch process for each node and simulate an long
operation.

Install
-------
- Download the module in your /modules/custom directory.
- Install it with Drupal Console: drupal moi ex_batch_drush9

Use
---
drush update-node
drush update-node page
drush update-node article

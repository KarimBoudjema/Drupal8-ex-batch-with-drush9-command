<?php

namespace Drupal\ex_batch_drush9\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class ExBatchDrush9Commands extends DrushCommands {

  /**
   * Entity type service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerChannelFactory;

  /**
   * Constructs a new UpdateVideosStatsController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoggerChannelFactoryInterface $loggerChannelFactory) {
    $this->entityTypeManager = $entityTypeManager;
    $this->loggerChannelFactory = $loggerChannelFactory;
  }

  /**
   * Update Node.
   *
   * @param string $type
   *   Type of node to update
   *   Argument provided to the drush command.
   *
   * @command update:node
   * @aliases update-node
   *
   * @usage update:node foo
   *   foo is the type of node to update
   */
  public function updateNode($type = '') {

    // 1. Log the start of the script.
    $this->loggerChannelFactory->get('ex_batch_drush9')->info('Update nodes batch operations start');

    // Check the type of node given as argument, if not, set article as default.
    if (strlen($type) == 0) {
      $type = 'article';
    }

    // 2. Retrieve all nodes of this type.
    try {
      $storage = $this->entityTypeManager->getStorage('node');
      $query = $storage->getQuery()
        ->condition('type', $type)
        ->condition('status', '1');
      $nids = $query->execute();
    }
    catch (\Exception $e) {
      $this->output()->writeln($e);
      $this->loggerChannelFactory->get('ex_batch_drush9')->warning('Error found @e', ['@e' => $e]);
    }

    // 3. Create the operations array for the batch.
    $operations = [];
    $numOperations = 0;
    $batchId = 1;

    if (!empty($nids)) {
      foreach ($nids as $nid) {
        // Prepare the operation. Here we could do other operations on nodes.
        $this->output()->writeln("Preparing batch: " . $batchId);

        $operations[] = [
          '\Drupal\ex_batch_drush9\BatchService::processMyNode',
          [
            $batchId,
            t('Updating node @nid', ['@nid' => $nid]),
          ],
        ];
        $batchId++;
        $numOperations++;
      }
    }
    else {
      $this->logger()->warning('No nodes of this type @type', ['@type' => $type]);
    }

    // 4. Create the batch.
    $batch = [
      'title' => t('Updating @num node(s)', ['@num' => $numOperations]),
      'operations' => $operations,
      'finished' => '\Drupal\ex_batch_drush9\BatchService::processMyNodeFinished',
    ];

    // 5. Add batch operations as new batch sets.
    batch_set($batch);

    // 6. Process the batch sets.
    drush_backend_batch_process();

    // 6. Show some information.
    $this->logger()->notice("Batch operations end.");
    // 7. Log some information.
    $this->loggerChannelFactory->get('ex_batch_drush9')->info('Update batch operations end.');
  }

}

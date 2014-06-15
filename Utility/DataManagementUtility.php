<?php

namespace Utility;

use Entity\FileMetric;

Class DataManagementUtility
{

  protected $monolog;

  public function __construct($monolog) {
    $this->monolog = $monolog;
  }

  public function groupByInterval($list, $interval, $value = 'doc'){
    //for each interval, get class with a method rate contained is this one
    $previousInterval = -1;
    $listInterval = array();
    foreach ($interval as $item) {
      $this->monolog->addDebug(
          sprintf("item: '%s'; previous: '%s'", $item, $previousInterval));

      $average = 0;
      $total = 0;
      foreach($list as $metric) {
        if(isset($metric[$value]['methodRate'])
        && $metric[$value]['methodRate'] > $previousInterval
        && $metric[$value]['methodRate']  <= $item) {
          $average ++;
        }
      }

      array_push($listInterval , round(($average / count($list)*100), 2));

      $previousInterval = $item;
      $this->monolog->addDebug(
          sprintf("total: '%s'", count($list)));

    }
    return $listInterval;
  }
}

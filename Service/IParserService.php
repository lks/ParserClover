<?php

namespace Service;

interface IParserService
{

  /**
   * Get the report for the metrics given in the params array and count the high priority number by 
   * desired metric.
   * The aim is to extract the high priority violation to have an overview of the refactoring task.
   * 
   * @param  array  $params Contained the metric to analyse, null if we want to analyse all metrics
   * @return Boolean
   */
  public function populate($params = null);

  /**
   * Parse the report of Clover and insert it in the database
   *
   * @return List of vialation inserted in the database
   */
  public function parseCloverReport($category);

  /**
   * Parse the report of Pmd and insert it in the database
   *
   * @param  category String
   *
   * @return List of vialation inserted in the database
   */
  public function parsePmdReport($category);

   /**
   * Parse the report of Pmd and insert it in the database
   *
   * @param  category String
   *
   * @return List of vialation inserted in the database
   */
  public function parseCpdReport($category);

}

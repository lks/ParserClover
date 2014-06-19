<?php

namespace Service;

interface IParserService
{
  /**
   * Parse the report of Clover and split by categories.
   * For exemple, we will have the code coverage for the Controller
   * the services, ...
   *
   * @return List of vialation by typology.
   */
  public function parseCloverReport();

  /**
   * Parse the report of Pmd and split by categories.
   * For exemple, we will have the code coverage for the Controller
   * the services, ...
   *
   * @return List of vialation by typology.
   */
  public function parsePmdReport();
}

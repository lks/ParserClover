<?php

namespace Service;


interface IParserService
{
    /**
     * Get the report for the metrics given in the params array and count the high priority number by
     * desired metric.
     * The aim is to extract the high priority violation to have an overview of the refactoring task.
     *
     * @return array of FileStats object
     */
    public function mergeReport();

    /**
     * Parse the report of Pmd and insert it in the database
     *
     * @return List of vialation inserted in the database
     */
    public function parsePmdReport();

    /**
     * Parse the reports of PhpUnit and insert it in the database
     * @return List of vialation inserted in the database
     */
    public function parsePhpUnitReport();

}

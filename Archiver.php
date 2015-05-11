<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\NewspaperReporting;

use Piwik\Config;
use Piwik\DataArray;
use Piwik\Metrics;
/**
 * Class Archiver
 * @package Piwik\Plugins\NewspaperReporting
 *
 * Archiver is class processing raw data into ready ro read reports.
 * It must implement two methods for aggregating daily reports
 * aggregateDayReport() and other for summing daily reports into periods
 * like week, month, year or custom range aggregateMultipleReports().
 *
 * For more detailed information about Archiver please visit Piwik developer guide
 * http://developer.piwik.org/api-reference/Piwik/Plugin/Archiver
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    /**
     * It is a good practice to store your archive names (reports stored in database)
     * in Archiver class constants. You can define as many record names as you want
     * for your plugin.
     *
     * Also important thing is that record name must be prefixed with plugin name.
     *
     * This is only an example record name, so feel free to change it to suit your needs.
     */
    const NEWSPAPERREPORTING_ARCHIVE_RECORD = "NewspaperReporting_archive_record";

    /**
     * @var DataArray
     */
    protected $dataArray;
    protected $maximumRowsInDataTableLevelZero;
    protected $maximumRowsInSubDataTable;

    function __construct($processor)
    {
        parent::__construct($processor);

        $this->maximumRowsInDataTableLevelZero = Config::getInstance()->General['datatable_archiving_maximum_rows_custom_variables'];
        $this->maximumRowsInSubDataTable = Config::getInstance()->General['datatable_archiving_maximum_rows_subtable_custom_variables'];
    }

    public function aggregateDayReport()
    {
        $this->dataArray = new DataArray();

        $settings = new \Piwik\Plugins\NewspaperReporting\Settings();
        $paywallCvarId = $settings->paywallId->getValue();
        $articleCvarId = $settings->articleId->getValue();

        $this->aggregatePaywallVariables($paywallCvarId, $articleCvarId);

        $table = $this->dataArray->asDataTable();

        $blob = $table->getSerialized(
            $this->maximumRowsInDataTableLevelZero,
            $this->maximumRowsInSubDataTable,
            $columnToSort = Metrics::INDEX_NB_VISITS
        );

        $this->getProcessor()->insertBlobRecord(self::NEWSPAPERREPORTING_ARCHIVE_RECORD, $blob);
    }



    protected function aggregatePaywallVariables($paywallSlot, $articleSlot)
    {
        $paywallKeyField = "custom_var_k" . $paywallSlot;
        $paywallValueField = "custom_var_v" . $paywallSlot;
        $paywallWhere = "%s.$paywallKeyField != ''";
        $paywallDimensions = array($paywallKeyField, $paywallValueField);

        $articleKeyField = "custom_var_k" . $articleSlot;
        $articleValueField = "custom_var_v" . $articleSlot;



        $paywallQuery = $this->getLogAggregator()->queryVisitsByDimension($paywallDimensions, $paywallWhere);
        $this->aggregateVisits($paywallQuery, $articleQuery, $paywallKeyField, $paywallValueField, $articleKeyField, $articleValueField);
//        $paywallQuery = $this->getLogAggregator()->queryActionsByDimension($paywallDimensions, $paywallWhere);
//        $this->aggregateActions($paywallQuery, $articleQuery, $paywallKeyField, $paywallValueField, $articleKeyField, $articleValueField);
    }

    protected function aggregateVisits($paywallQuery, $articleQuery, $paywallKeyField, $paywallValueField, $articleKeyField, $articleValueField)
    {
        while ($paywallRow = $paywallQuery->fetch()) {
            $paywallKey = $paywallRow[$paywallKeyField];
            $paywallValue = $this->cleanCustomVarValue($paywallRow[$paywallValueField]);
            $paywallLabel = $paywallKey." ".$paywallValue;

            $this->dataArray->sumMetricsVisits($paywallLabel, $paywallRow);

            $articleWhere = "%s.{$paywallKeyField} != '' AND %s.{$paywallValueField} = {$paywallValue}";
            $articleDimensions = array($articleKeyField, $articleValueField);
            $articleQuery = $this->getLogAggregator()->queryVisitsByDimension($articleDimensions, $articleWhere);
            while ($articleRow = $articleQuery->fetch()) {
                $articleKey = $articleRow[$articleKeyField];
                $articleValue = $this->cleanCustomVarValue($articleRow[$articleValueField]);
                $articleLabel = $articleKey." ".$articleValue;

                $this->dataArray->sumMetricsVisitsPivot($paywallLabel, $articleLabel, $articleRow);
            }
        }
    }

    protected function aggregateActions($paywallQuery, $articleQuery, $paywallKeyField, $paywallValueField, $articleKeyField, $articleValueField)
    {
        while ($paywallRow = $paywallQuery->fetch()) {
            $paywallKey = $paywallRow[$paywallKeyField];
            $paywallValue = $this->cleanCustomVarValue($paywallRow[$paywallValueField]);
            $paywallLabel = $paywallKey." ".$paywallValue;

            $this->dataArray->sumMetricsActions($paywallLabel, $paywallRow);

            $articleWhere = "%s.{$paywallKeyField} != '' AND %s.{$paywallValueField} = {$paywallValue}";
            $articleDimensions = array($articleKeyField, $articleValueField);
            $articleQuery = $this->getLogAggregator()->queryActionsByDimension($articleDimensions, $articleWhere);
            while ($articleRow = $articleQuery->fetch()) {
                $articleKey = $articleRow[$articleKeyField];
                $articleValue = $this->cleanCustomVarValue($articleRow[$articleValueField]);
                $articleLabel = $articleKey." ".$articleValue;

                $this->dataArray->sumMetricsActionsPivot($paywallLabel, $articleLabel, $articleRow);
            }
        }
    }

    protected function cleanCustomVarValue($value)
    {
        if (strlen($value)) {
            return $value;
        }
        return self::LABEL_CUSTOM_VALUE_NOT_DEFINED;
    }

    public function aggregateMultipleReports()
    {
        $columnsAggregationOperation = null;

        $this->getProcessor()->aggregateDataTableRecords(
            self::NEWSPAPERREPORTING_ARCHIVE_RECORD,
            $this->maximumRowsInDataTableLevelZero,
            $this->maximumRowsInSubDataTable,
            $columnToSort = Metrics::INDEX_NB_VISITS,
            $columnsAggregationOperation,
            $columnsToRenameAfterAggregation = null,
            $countRowsRecursive = array()
        );
    }
}

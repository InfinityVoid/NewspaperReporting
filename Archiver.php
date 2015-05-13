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
    const NEWSPAPERREPORTING_PAYWALL_ARCHIVE_RECORD = "NewspaperReporting_paywall_archive_record";
    const NEWSPAPERREPORTING_ARTICLE_ARCHIVE_RECORD = "NewspaperReporting_article_archive_record";

    /**
     * @var DataArray
     */
    protected $paywallDataArray;
    /**
     * @var DataArray
     */
    protected $articleDataArray;

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
        $this->paywallDataArray = new DataArray();
        $this->articleDataArray = new DataArray();


        $settings = new \Piwik\Plugins\NewspaperReporting\Settings();
        $paywallCvarId = $settings->paywallId->getValue();
        $articleCvarId = $settings->articleId->getValue();

        $this->aggregatePaywallVariables($paywallCvarId, $articleCvarId);
        $this->aggregateArticleVariables($articleCvarId);

        $paywallTable = $this->paywallDataArray->asDataTable();
        $articleTable = $this->articleDataArray->asDataTable();

        $paywallBlob = $paywallTable->getSerialized(
            $this->maximumRowsInDataTableLevelZero,
            $this->maximumRowsInSubDataTable,
            $columnToSort = Metrics::INDEX_NB_VISITS
        );
        $articleBlob = $articleTable->getSerialized(
            $this->maximumRowsInDataTableLevelZero,
            $this->maximumRowsInSubDataTable,
            $columnToSort = Metrics::INDEX_NB_VISITS
        );

        $this->getProcessor()->insertBlobRecord(self::NEWSPAPERREPORTING_PAYWALL_ARCHIVE_RECORD, $paywallBlob);
        $this->getProcessor()->insertBlobRecord(self::NEWSPAPERREPORTING_ARTICLE_ARCHIVE_RECORD, $articleBlob);
    }

    protected function aggregateArticleVariables($articleSlot)
    {
        $articleKeyField = "custom_var_k" . $articleSlot;
        $articleValueField = "custom_var_v" . $articleSlot;
        $articleWhere = "%s.$articleKeyField != ''";
        $articleDimensions = array($articleKeyField, $articleValueField);

        $articleQuery = $this->getLogAggregator()->queryActionsByDimension($articleDimensions, $articleWhere);
        while ($articleRow = $articleQuery->fetch()) {
            $articleKey = $articleRow[$articleKeyField];
            $articleValue = $this->cleanCustomVarValue($articleRow[$articleValueField]);
            $articleLabel = $articleKey." ".$articleValue;

            $this->articleDataArray->sumMetricsActions($articleValue, $articleRow);
        }
    }

    protected function aggregatePaywallVariables($paywallSlot, $articleSlot)
    {
        $paywallKeyField = "custom_var_k" . $paywallSlot;
        $paywallValueField = "custom_var_v" . $paywallSlot;
        $paywallWhere = "%s.$paywallKeyField != ''";
        $paywallDimensions = array($paywallKeyField, $paywallValueField, 'idvisit');

        $articleKeyField = "custom_var_k" . $articleSlot;
        $articleValueField = "custom_var_v" . $articleSlot;

        $paywallQuery = $this->getLogAggregator()->queryVisitsByDimension($paywallDimensions, $paywallWhere);
        $this->aggregateRows($paywallQuery, $paywallKeyField, $paywallValueField, $articleKeyField, $articleValueField);
    }

    protected function aggregateRows($paywallQuery, $paywallKeyField, $paywallValueField, $articleKeyField, $articleValueField)
    {
        while ($paywallRow = $paywallQuery->fetch()) {
            $paywallKey = $paywallRow[$paywallKeyField];
            $paywallValue = $this->cleanCustomVarValue($paywallRow[$paywallValueField]);
            $paywallLabel = $paywallKey." ".$paywallValue;

            $this->paywallDataArray->sumMetricsVisits($paywallValue, $paywallRow);

            $articleWhere = "%s.{$articleKeyField} != '' AND %s.idvisit = {$paywallRow['idvisit']}";
            $articleDimensions = array($articleKeyField, $articleValueField);
            $articleQuery = $this->getLogAggregator()->queryActionsByDimension($articleDimensions, $articleWhere);
            while ($articleRow = $articleQuery->fetch()) {
                $articleKey = $articleRow[$articleKeyField];
                $articleValue = $this->cleanCustomVarValue($articleRow[$articleValueField]);
                $articleLabel = $articleKey." ".$articleValue;

                $this->paywallDataArray->sumMetricsActionsPivot($paywallValue, $articleValue, $articleRow);
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

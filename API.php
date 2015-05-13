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

use Piwik\DataTable\Row;

use Piwik\Piwik;
use Piwik\Date;
use Piwik\DataTable;
use Piwik\Archive;

/**
 * API for plugin NewspaperReporting
 *
 * @method static \Piwik\Plugins\NewspaperReporting\API getInstance()
 */
class API extends \Piwik\Plugin\API
{

    protected function getDataTable($name, $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable)
    {
        $dataTable = Archive::createDataTableFromArchive($name, $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable);
        $dataTable->queueFilter('ColumnDelete', 'nb_uniq_visitors');
        $dataTable->queueFilter('ColumnCallbackReplace', array('label', array($this, 'renameLabel'), array($name, $idSubtable)));


        if ($flat) {
            $dataTable->filterSubtables('Sort', array(Metrics::INDEX_NB_ACTIONS, 'desc', $naturalSort = false, $expanded));
            $dataTable->queueFilterSubtables('ColumnDelete', 'nb_uniq_visitors');
        }

        return $dataTable;
    }

    public function renameLabel($label, $labelType, $idSubtable)
    {
        if ($labelType === Archiver::NEWSPAPERREPORTING_ARTICLE_ARCHIVE_RECORD || $idSubtable) {
            return sprintf(Piwik::translate('NewspaperReporting_NArticle'), $label);
        }
        if ($labelType === Archiver::NEWSPAPERREPORTING_PAYWALL_ARCHIVE_RECORD) {
            return sprintf(Piwik::translate('NewspaperReporting_NPaywall'), $label);
        }
        return Piwik::translate('VisitHoursEvenOdd_UnknownError');

    }

    /**
     * Another example method that returns a data table.
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getNewspaperReport($idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $flat = false;
        $expanded = false;
        $dataTable = $this->getDataTable(Archiver::NEWSPAPERREPORTING_PAYWALL_ARCHIVE_RECORD, $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable = null);

        return $dataTable;
    }

    /**
     * Another example method that returns a data table.
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getArticleReport($idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $flat = false;
        $expanded = false;
        $dataTable = $this->getDataTable(Archiver::NEWSPAPERREPORTING_ARTICLE_ARCHIVE_RECORD, $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable = null);
        $dataTable->applyQueuedFilters();

        return $dataTable;
    }

    /**
     * @param int $idSite
     * @param string $period
     * @param Date $date
     * @param int $idSubtable
     * @param string|bool $segment
     * @param bool $_leavePriceViewedColumn
     *
     * @return DataTable|DataTable\Map
     */
    public function getCustomVariablesValuesFromNameId($idSite, $period, $date, $idSubtable, $segment = false, $_leavePriceViewedColumn = false)
    {
        $dataTable = $this->getDataTable(Archiver::NEWSPAPERREPORTING_PAYWALL_ARCHIVE_RECORD, $idSite, $period, $date, $segment, $expanded = false, $flat = false, $idSubtable);

        if (!$_leavePriceViewedColumn) {
            $dataTable->deleteColumn('price_viewed');
        } else {
            // Hack Ecommerce product price tracking to display correctly
            $dataTable->renameColumn('price_viewed', 'price');
        }
        //$dataTable->filter('Piwik\Plugins\CustomVariables\DataTable\Filter\CustomVariablesValuesFromNameId');

        return $dataTable;
    }
}

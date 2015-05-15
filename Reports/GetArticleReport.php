<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\NewspaperReporting\Reports;

use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;

use Piwik\View;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetArticleReport extends Base
{
    protected function init()
    {
        parent::init();

        $this->name          = Piwik::translate('NewspaperReporting_ArticleReport');
        $this->dimension     = null;
        $this->documentation = Piwik::translate('This report reports an article views per article.');

        // This defines in which order your report appears in the mobile app, in the menu and in the list of widgets
        $this->order = 1;

        // If a menu title is specified, the report will be displayed in the menu
        $this->menuTitle    = 'NewspaperReporting_ArticleReport';

        // If a widget title is specified, the report will be displayed in the list of widgets and the report can be
        // exported as a widget
        $this->widgetTitle  = 'NewspaperReporting_ArticleReport';
    }

    /**
     * Here you can configure how your report should be displayed. For instance whether your report supports a search
     * etc. You can also change the default request config. For instance change how many rows are displayed by default.
     *
     * @param ViewDataTable $view
     */
    public function configureView(ViewDataTable $view)
    {
        $view->config->addTranslations(array('label' => 'Article'));

        $view->config->columns_to_display = array_merge(array('label'), 'nb_actions', 'nb_visits');
    }

    /**
     * Here you can define related reports that will be shown below the reports. Just return an array of related
     * report instances if there are any.
     *
     * @return \Piwik\Plugin\Report[]
     */
    public function getRelatedReports()
    {
        return array(); // eg return array(new XyzReport());
    }
}

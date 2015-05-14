<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\NewspaperReporting;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\View;
use Piwik\API\Request;

/**
 * A controller let's you for example create a page that can be added to a menu. For more information read our guide
 * http://developer.piwik.org/guides/mvc-in-piwik or have a look at the our API references for controller and view:
 * http://developer.piwik.org/api-reference/Piwik/Plugin/Controller and
 * http://developer.piwik.org/api-reference/Piwik/View
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function index()
    {
        // Render the Twig template templates/index.twig and assign the view variable answerToLife to the view.
        return $this->renderTemplate('index', array(
            'answerToLife' => 42
        ));
    }

    public function articleReport()
    {
        $this->checkUserAccess();
        $dataTable = API::getInstance()->getArticleReport(
            Common::getRequestVar('idSite'),
            Common::getRequestVar('period'),
            Common::getRequestVar('date'),
            Common::getRequestVar('segment', false)
        );
        $dataTable->applyQueuedFilters();

        $view = new View("@NewspaperReporting/articleReport.twig");
        $this->setGeneralVariablesView($view);

        $view->rows = $dataTable->getRows();

        return $view->render();
    }

    public function paywallReport()
    {
        $this->checkUserAccess();
        $dataTable = API::getInstance()->getNewspaperReport(
            Common::getRequestVar('idSite'),
            Common::getRequestVar('period'),
            Common::getRequestVar('date'),
            Common::getRequestVar('segment', false)
        );
        $dataTable->applyQueuedFilters();

        $view = new View("@NewspaperReporting/paywallReport.twig");
        $this->setGeneralVariablesView($view);

        $view->rows = $dataTable->getRows();

        return $view->render();
    }

    private function checkUserAccess()
    {
        $idSite = Common::getRequestVar('idSite');
        Piwik::checkUserHasViewAccess($idSite);
    }
}

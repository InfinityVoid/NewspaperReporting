<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\NewspaperReporting;

use Piwik\Common;

class NewspaperReporting extends \Piwik\Plugin
{
    public function getListHooksRegistered()
    {
        return array(
            'Tracker.newVisitorInformation' => 'processNewVisitor',
            'Tracker.existingVisitInformation' => 'processExistingVisitInformation',
        );
    }


    public function processNewVisitor(&$valuesToUpdate, $visitorInfo)
    {
        var_dump($valuesToUpdate);
        var_dump($visitorInfo);
    }

    public function processExistingVisitInformation(&$valuesToUpdate, $visitorInfo)
    {
        var_dump($valuesToUpdate);
        var_dump($visitorInfo);
        $articleId = Common::getRequestVar('ArticleId');
        var_dump($articleId);
    }
}

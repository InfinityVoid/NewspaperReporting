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

use Piwik\Common;

class NewspaperReporting extends \Piwik\Plugin
{
    public function getListHooksRegistered()
    {
        return array(
            'Tracker.newVisitorInformation' => 'processNewVisitor',
            'Tracker.existingVisitInformation' => 'processExistingVisitInformation',
            'Tracker.recordAction' => 'processAction'
        );
    }

    public function processNewVisitor(&$valuesToUpdate, $visitorInfo)
    {
        $valuesToUpdate = $this->processUrlVars($valuesToUpdate);
    }

    public function processExistingVisitInformation(&$valuesToUpdate, $visitorInfo)
    {
        $valuesToUpdate = $this->processUrlVars($valuesToUpdate);
    }

    public function processAction($trackerAction, $visitAction)
    {
        var_dump($visitAction);
    }

    private function processUrlVars($valuesToUpdate)
    {
        $url = Common::getRequestVar('url');
        $url = preg_replace('/&amp;/', '&', urldecode($url));
        $urlParts = parse_url($url);
        $vars = [];
        parse_str($urlParts['query'], $vars);
        if (isset($vars['ArticleId'])) {
            $valuesToUpdate['custom_var_k3'] = 'ArticleId';
            $valuesToUpdate['custom_var_v3'] = $vars['ArticleId'];
        }
        if (isset($vars['PaywallPlan'])) {
            $valuesToUpdate['custom_var_k4'] = 'PaywallPlan';
            $valuesToUpdate['custom_var_v4'] = $vars['PaywallPlan'];
        }
        return $valuesToUpdate;
    }
}

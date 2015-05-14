<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */
namespace Piwik\Plugins\NewspaperReporting\Test\Fixtures;

use Piwik\Date;
use Piwik\Tests\Framework\Fixture;

class NewspaperReportingFixture extends Fixture
{
    public $dateTime = '2013-01-23 01:00:00';
    public $idSite = 1;

    public function setUp()
    {
        $this->setUpWebsite();
        $this->trackSomeVisits();
    }

    public function tearDown()
    {
        // empty
    }

    private function setUpWebsite()
    {
        if (!self::siteCreated($this->idSite)) {
            $idSite = self::createWebsite($this->dateTime);
            $this->assertSame($this->idSite, $idSite);
        }
    }

    protected function trackSomeVisits()
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);

        for ($i=0; $i<4; $i++) {
            $t->setNewVisitorId();
            $t->setForceNewVisit();
            $t->setCustomVariable(2, 'PaywallPlan', $i+1);
            for ($j=$i; $j<$i+5; $j++) {
                $t->setUrl('http://example.com/?PaywallPlan=' . $i . '&ArticleId=' . $j);
                $timeToAdd = $i*4+$j*0.1;
                $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour($timeToAdd)->getDatetime());
                $t->setLocalTime(Date::factory($this->dateTime)->addHour($timeToAdd)->toString('H:i:s'));
                $t->setCustomVariable(1, 'ArticleId', $j+1, 'page');
                $response = $t->doTrackPageView('Viewing article_id: ' . $j . ' on paywall plan: ' . $i);
                self::checkResponse($response);
            }
        }
    }
}

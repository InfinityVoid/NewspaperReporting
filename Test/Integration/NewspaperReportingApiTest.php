<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\NewspaperReporting\Test\Integration;

use Piwik\Date;
use Piwik\Translate;
use Piwik\Plugins\NewspaperReporting\Test\Fixtures\NewspaperReportingFixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group NewspaperReporting
 * @group NewspaperReportingTest
 * @group Plugins
 */
class NewspaperReportingApiTest extends IntegrationTestCase
{
    /**
     * @var NewspaperReportingFixture
     */
    public static $fixture = null;

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     */
    public function testApi($api, $params)
    {
        Translate::loadEnglishTranslation();
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $date = Date::factory(self::$fixture->dateTime);

        $apiToTest = array(
            array(
                'NewspaperReporting.getNewspaperReport',
                array(
                    'idSite'     => $idSite,
                    'date'       => $date,
                    'periods' => array('day'),
                )
            ),
            array(
                'NewspaperReporting.getArticleReport',
                array(
                    'idSite'     => $idSite,
                    'date'       => $date,
                    'periods' => array('day'),
                )
            )
        );

        return $apiToTest;
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}
NewspaperReportingApiTest::$fixture = new NewspaperReportingFixture();

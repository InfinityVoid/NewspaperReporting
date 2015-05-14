/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */
describe("NewspaperReportingUiTest", function () {
    this.timeout(0);

    var urlToTest = '?module=CoreHome&action=index&idSite=1&period=day&date=2013-01-23#/module=NewspaperReporting&action=menuGetNewspaperReport&idSite=1&period=day&date=2013-01-23';
    this.fixture = "Piwik\\Plugins\\NewspaperReporting\\Test\\Fixtures\\NewspaperReportingFixture";
    var contentSelector = '.pageWrap';

    testEnvironment.debug = "1";
    testEnvironment.save();

    it('should load a newspaper report', function (done) {
        var screenshotName = 'newspaperReport';

        expect.screenshot(screenshotName).to.be.captureSelector(contentSelector, function (page) {
            page.load(urlToTest);
        }, done);
    });

    var url2ToTest = '?module=CoreHome&action=index&idSite=1&period=day&date=2013-01-23#/module=NewspaperReporting&action=menuGetArticleReport&idSite=1&period=day&date=2013-01-23';

    it('should load a article report', function (done) {
        var screenshotName = 'articleReport';

        expect.screenshot(screenshotName).to.be.captureSelector(contentSelector, function (page) {
            page.load(url2ToTest);
        }, done);
    });

    var url3ToTest = "?module=NewspaperReporting&action=paywallReport&idSite=1&period=day&date=2013-01-23";
    var contentSelector2 = '.reportContent';

    it('should load a paywall report from a controller', function (done) {
        var screenshotName = 'controllerPaywallReport';

        expect.screenshot(screenshotName).to.be.captureSelector(contentSelector2, function (page) {
            page.load(url3ToTest);
        }, done);
    });

    var url4ToTest = "?module=NewspaperReporting&action=articleReport&idSite=1&period=day&date=2013-01-23";

    it('should load a article report from a controller', function (done) {
        var screenshotName = 'controllerArticleReport';

        expect.screenshot(screenshotName).to.be.captureSelector(contentSelector2, function (page) {
            page.load(url4ToTest);
        }, done);
    });
});
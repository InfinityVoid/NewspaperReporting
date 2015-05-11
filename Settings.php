<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\NewspaperReporting;

use Piwik\Settings\SystemSetting;
use Piwik\Settings\UserSetting;

/**
 * Defines Settings for NewspaperReporting.
 *
 * Usage like this:
 * $settings = new Settings('NewspaperReporting');
 * $settings->autoRefresh->getValue();
 * $settings->metric->getValue();
 */
class Settings extends \Piwik\Plugin\Settings
{

    /** @var SystemSetting */
    public $paywallId;

    /** @var SystemSetting */
    public $articleId;

    protected function init()
    {
        $this->setIntroduction('Here you can specify the settings for this plugin.');


        $this->createPayWallSettings();
        $this->createArticleSettings();
    }

    private function createPayWallSettings()
    {
        $this->paywallId = new SystemSetting('paywallCvarId', 'PayWall custom_var id');
        $this->paywallId->readableByCurrentUser = true;
        $this->paywallId->uiControlType = static::CONTROL_TEXT;
        $this->paywallId->introduction  = 'Insert numeric id of custom var, where PayWall id will be stored';
        $this->paywallId->inlineHelp    = 'Just a number between 1 and 5';
        $this->paywallId->description   = 'Custom variable id for PayWall identification';
        $this->paywallId->defaultValue  = "2";
        $this->paywallId->transform     = function ($value) {
            return intval($value);
        };

        $this->addSetting($this->paywallId);
    }

    private function createArticleSettings()
    {
        $this->articleId = new SystemSetting('articleCvarId', 'Article custom_var id');
        $this->articleId->readableByCurrentUser = true;
        $this->articleId->uiControlType = static::CONTROL_TEXT;
        $this->articleId->introduction  = 'Insert numeric id of custom var, where Article id will be stored';
        $this->articleId->inlineHelp    = 'Just a number between 1 and 5';
        $this->articleId->description   = 'Custom variable id for Article identification';
        $this->articleId->defaultValue  = "1";
        $this->articleId->transform     = function ($value) {
            return intval($value);
        };

        $this->addSetting($this->articleId);
    }
}

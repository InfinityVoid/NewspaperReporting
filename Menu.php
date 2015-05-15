<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\NewspaperReporting;

use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuReporting;
use Piwik\Menu\MenuTop;
use Piwik\Menu\MenuUser;

/**
 * This class allows you to add, remove or rename menu items.
 * To configure a menu (such as Admin Menu, Reporting Menu, User Menu...) simply call the corresponding methods as
 * described in the API-Reference http://developer.piwik.org/api-reference/Piwik/Menu/MenuAbstract
 */
class Menu extends \Piwik\Plugin\Menu
{

    public function configureUserMenu(MenuUser $menu)
    {
        // reuse an existing category. Execute the showList() method within the controller when menu item was clicked
        $menu->addItem('NewspaperReporting', 'index', $this->urlForAction('index'), $orderId = 1);
        $menu->addItem('NewspaperReporting', 'Paywall Report', $this->urlForAction('paywallReport'), $orderId = 2);
        $menu->addItem('NewspaperReporting', 'Article Report', $this->urlForAction('articleReport'), $orderId = 3);
    }
}

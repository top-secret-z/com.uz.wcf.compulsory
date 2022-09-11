<?php

namespace wcf\system\event\listener;

use wcf\data\page\PageCache;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\HeaderUtil;

/**
 * Listen to Page display actions for Compulsory
 *
 * @author        2016-2022 Darkwood.Design
 * @license        Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package        com.uz.wcf.compulsory
 */
class CompulsoryPageListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        // if configured
        if (!MODULE_COMPULSORY) {
            return;
        }

        // only users
        if (!WCF::getUser()->userID) {
            return;
        }

        // get pageID
        $page = PageCache::getInstance()->getPageByController($className);
        if ($page !== null) {
            $pageID = $page->pageID;
        } else {
            if (isset($eventObj->pageID)) {
                $pageID = $eventObj->pageID;
            } else {
                return;
            }
        }

        // never on compulsory page itself
        if ($className == 'wcf\form\CompulsoryForm') {
            return;
        }

        // skip registration, login and activation and similar
        if ($className == 'wcf\form\LoginForm') {
            return;
        }
        if ($className == 'wcf\form\LostPasswordForm') {
            return;
        }
        if ($className == 'wcf\form\RegisterForm') {
            return;
        }
        if ($className == 'wcf\form\RegisterActivationForm') {
            return;
        }
        if ($className == 'wcf\form\RegisterNewActivationCodeForm') {
            return;
        }

        // get requested page
        $requestURL = WCF::getRequestURI();

        // get user's compulsories
        $compulsories = WCF::getCompulsoryHandler()->getVisibleCompulsories();
        if (count($compulsories)) {
            // excluded page?
            $pageIDs = explode("\n", USER_COMPULSORY_EXCEPTIONS);
            if (count($pageIDs) && in_array($pageID, $pageIDs)) {
                return;
            }

            // check show pages
            $trimmedRequestURL = rtrim($requestURL, '/');

            foreach ($compulsories as $compulsory) {
                $pageCondition = $found = 0;

                if (empty($compulsory->pages)) {
                    break;
                } else {
                    $pageCondition = 1;
                }

                $pages = ArrayUtil::trim(explode("\n", $compulsory->pages));
                foreach ($pages as $page) {
                    // check asterix / 1v1
                    if (substr($page, -1) == '*') {
                        $page = rtrim($page, '*');
                        $len = strlen($page);
                        if (0 == strcasecmp(substr($requestURL, 0, $len), $page)) {
                            $found = 1;
                            break 2;
                        }
                    } else {
                        $page = rtrim($page, '/');
                        if (0 == strcasecmp($trimmedRequestURL, $page)) {
                            $found = 1;
                            break 2;
                        }
                    }
                }
            }

            if ($pageCondition && !$found) {
                return;
            }

            // redirect
            HeaderUtil::redirect(LinkHandler::getInstance()->getLink('Compulsory', ['object' => $compulsory]));
        }
    }
}

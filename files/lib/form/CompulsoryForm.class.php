<?php

namespace wcf\form;

use wcf\data\compulsory\Compulsory;
use wcf\system\WCF;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;

/**
 * Shows the compulsory display form.
 *
 * @author        2016-2022 Darkwood.Design
 * @license        Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package        com.uz.wcf.compulsory
 */
class CompulsoryForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * compulsory and texts
     */
    public $compulsory = null;
    public $content = [];
    public $text = '';

    /**
     * affected user
     */
    public $user = null;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        // get compulsory to be shown
        if (!empty($_REQUEST['id'])) {
            $compulsoryID = intval($_REQUEST['id']);
            $this->compulsory = new Compulsory($compulsoryID);
            if (!$this->compulsory->compulsoryID) {
                throw new IllegalLinkException();
            }
        } else {
            throw new IllegalLinkException();
        }

        // prevent opening the page if no compulsories
        $compulsories = WCF::getCompulsoryHandler()->getVisibleCompulsories();
        if (!count($compulsories)) {
            throw new PermissionDeniedException();
        }

        // get content and replace username
        $this->content = $this->compulsory->getCompulsoryContent();
        $this->content->content = str_replace('[username]', WCF::getUser()->username, $this->content->content);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        $acceptConfirmGroup = $acceptConfirmBreak = 0;
        if (count(unserialize($this->compulsory->acceptAddGroupIDs)) || count(unserialize($this->compulsory->acceptRemoveGroupIDs))) {
            $acceptConfirmGroup = 1;
        }
        $acceptConfirmAction = $this->compulsory->acceptUserAction;
        if ($acceptConfirmGroup || $acceptConfirmAction != 'none') {
            $acceptConfirmBreak = 1;
        }

        $refuseConfirmGroup = $refuseConfirmBreak = 0;
        if (count(unserialize($this->compulsory->refuseAddGroupIDs)) || count(unserialize($this->compulsory->refuseRemoveGroupIDs))) {
            $refuseConfirmGroup = 1;
        }
        $refuseConfirmAction = $this->compulsory->refuseUserAction;
        if ($refuseConfirmGroup || $refuseConfirmAction != 'none') {
            $refuseConfirmBreak = 1;
        }

        WCF::getTPL()->assign([
                                  'user' => $this->user,
                                  'compulsory' => $this->compulsory,
                                  'content' => $this->content,
                                  'acceptConfirmBreak' => $acceptConfirmBreak,
                                  'acceptConfirmAction' => $acceptConfirmAction,
                                  'acceptConfirmGroup' => $acceptConfirmGroup,
                                  'refuseConfirmBreak' => $refuseConfirmBreak,
                                  'refuseConfirmAction' => $refuseConfirmAction,
                                  'refuseConfirmGroup' => $refuseConfirmGroup
                              ]);
    }
}

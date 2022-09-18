<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace wcf\form;

use wcf\data\compulsory\Compulsory;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Shows the compulsory display form.
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
    public $compulsory;

    public $content = [];

    public $text = '';

    /**
     * affected user
     */
    public $user;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        // get compulsory to be shown
        if (!empty($_REQUEST['id'])) {
            $compulsoryID = \intval($_REQUEST['id']);
            $this->compulsory = new Compulsory($compulsoryID);
            if (!$this->compulsory->compulsoryID) {
                throw new IllegalLinkException();
            }
        } else {
            throw new IllegalLinkException();
        }

        // prevent opening the page if no compulsories
        $compulsories = WCF::getCompulsoryHandler()->getVisibleCompulsories();
        if (!\count($compulsories)) {
            throw new PermissionDeniedException();
        }

        // get content and replace username
        $this->content = $this->compulsory->getCompulsoryContent();
        $this->content->content = \str_replace('[username]', WCF::getUser()->username, $this->content->content);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        $acceptConfirmGroup = $acceptConfirmBreak = 0;
        if (\count(\unserialize($this->compulsory->acceptAddGroupIDs)) || \count(\unserialize($this->compulsory->acceptRemoveGroupIDs))) {
            $acceptConfirmGroup = 1;
        }
        $acceptConfirmAction = $this->compulsory->acceptUserAction;
        if ($acceptConfirmGroup || $acceptConfirmAction != 'none') {
            $acceptConfirmBreak = 1;
        }

        $refuseConfirmGroup = $refuseConfirmBreak = 0;
        if (\count(\unserialize($this->compulsory->refuseAddGroupIDs)) || \count(\unserialize($this->compulsory->refuseRemoveGroupIDs))) {
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
            'refuseConfirmGroup' => $refuseConfirmGroup,
        ]);
    }
}

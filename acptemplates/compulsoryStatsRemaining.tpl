{assign var='pageTitle' value='wcf.acp.compulsory.stats.remaining'}

{include file='header'}

<header class="contentHeader">
    <div class="contentHeaderTitle">
        <h1 class="contentTitle">{lang}{@$pageTitle}{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
    </div>

    {hascontent}
        <nav class="contentHeaderNavigation">
            <ul>
                {content}
                    <li><a href="{link controller='CompulsoryStats' id=$compulsory->compulsoryID}{/link}" class="button"><span class="icon icon16 fa-bar-chart-o"></span> <span>{lang}wcf.acp.compulsory.stats{/lang}</span></a></li>

                    {event name='contentHeaderNavigation'}
                {/content}
            </ul>
        </nav>
    {/hascontent}
</header>

{hascontent}
    <div class="paginationTop">
        {content}
            {pages print=true assign=pagesLinks controller="CompulsoryStatsRemaining" id=$compulsory->compulsoryID link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
        {/content}
    </div>
{/hascontent}

{if $objects|count}
    <div class="section tabularBox">
        <table class="table">
            <thead>
                <tr>
                    <th class="columnID columnUserID">{lang}wcf.global.objectID{/lang}</th>
                    <th class="columnTitle columnUsername{if $sortField == 'username'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='CompulsoryStatsRemaining' id=$compulsory->compulsoryID}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.username{/lang}</a></th>

                    {if $__wcf->session->getPermission('admin.user.canEditMailAddress')}
                        <th class="columnText columnEmail{if $sortField == 'email'} active {@$sortOrder}{/if}"><a href="{link controller='CompulsoryStatsRemaining' id=$compulsory->compulsoryID}pageNo={@$pageNo}&sortField=email&sortOrder={if $sortField == 'email' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.email{/lang}</a></th>
                    {/if}
                    <th class="columnDigits columnActivityPoints{if $sortField == 'activityPoints'} active {@$sortOrder}{/if}"><a href="{link controller='CompulsoryStatsRemaining' id=$compulsory->compulsoryID}pageNo={@$pageNo}&sortField=activityPoints&sortOrder={if $sortField == 'activityPoints' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.sortField.activityPoints{/lang}</a></th>
                    {if MODULE_LIKE}
                        <th class="columnDigits columnLikesReceived{if $sortField == 'likesReceived'} active {@$sortOrder}{/if}"><a href="{link controller='CompulsoryStatsRemaining' id=$compulsory->compulsoryID}pageNo={@$pageNo}&sortField=likesReceived&sortOrder={if $sortField == 'likesReceived' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.like.cumulativeLikes{/lang}</a></th>
                    {/if}
                    {if MODULE_TROPHY}
                        <th class="columnDigits columnTrophyPoints{if $sortField == 'trophyPoints'} active {@$sortOrder}{/if}"><a href="{link controller='CompulsoryStatsRemaining' id=$compulsory->compulsoryID}pageNo={@$pageNo}&sortField=trophyPoints&sortOrder={if $sortField == 'trophyPoints' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.trophy.trophyPoints{/lang}</a></th>
                    {/if}
                    <th class="columnDate columnRegistrationDate{if $sortField == 'registrationDate'} active {@$sortOrder}{/if}"><a href="{link controller='CompulsoryStatsRemaining' id=$compulsory->compulsoryID}pageNo={@$pageNo}&sortField=registrationDate&sortOrder={if $sortField == 'registrationDate' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.registrationDate{/lang}</a></th>
                    <th class="columnDate columnLastActivityTime{if $sortField == 'lastActivityTime'} active {@$sortOrder}{/if}"><a href="{link controller='CompulsoryStatsRemaining' id=$compulsory->compulsoryID}pageNo={@$pageNo}&sortField=lastActivityTime&sortOrder={if $sortField == 'lastActivityTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.lastActivityTime{/lang}</a></th>
                </tr>
            </thead>

            <tbody>
                {foreach from=$objects item=user}
                    <tr class="jsUserRow" data-object-id="{@$user->userID}" data-banned="{if $user->banned}true{else}false{/if}" data-enabled="{if !$user->activationCode}true{else}false{/if}">
                        <td class="columnID columnUserID">{@$user->userID}</td>
                        <td class="columnIcon">{@$user->getAvatar()->getImageTag(24)}</td>
                        <td class="columnTitle columnUsername">
                            <span class="username">{$user->username}</span>

                            <span class="userStatusIcons">
                                {if $user->banned}<span class="icon icon16 fa-lock jsTooltip jsUserStatusBanned" title="{lang}wcf.user.status.banned{/lang}"></span>{/if}
                                {if $user->activationCode != 0}
                                    <span class="icon icon16 fa-power-off jsTooltip jsUserStatusIsDisabled" title="{lang}wcf.user.status.isDisabled{/lang}"></span>
                                    {if !$user->getBlacklistMatches()|empty}
                                        <span class="icon icon16 fa-warning jsTooltip jsUserStatusBlacklistMatches" title="{lang}wcf.user.status.blacklistMatches{/lang}"></span>
                                    {/if}
                                {/if}
                            </span>

                            {if MODULE_USER_RANK}
                                {if $user->getUserTitle()} <span class="badge userTitleBadge{if $user->getRank() && $user->getRank()->cssClassName} {@$user->getRank()->cssClassName}{/if}">{$user->getUserTitle()}</span>{/if}
                                {if $user->getRank() && $user->getRank()->rankImage} <span class="userRankImage">{@$user->getRank()->getImage()}</span>{/if}
                            {/if}
                        </td>
                        {if $__wcf->session->getPermission('admin.user.canEditMailAddress')}
                            <td class="columnText columnEmail">{@$user->email}</td>
                        {/if}
                        <td class="columnDigits columnActivityPoints">{#$user->activityPoints}</td>
                        {if MODULE_LIKE}
                            <td class="columnDigits columnLikesReceived">{#$user->likesReceived}</td>
                        {/if}
                        {if MODULE_TROPHY}
                            <td class="columnDigits columnTrophyPoints">{#$user->trophyPoints}</td>
                        {/if}
                        <td class="columnDate columnRegistrationDate">{@$user->registrationDate|date}</td>
                        <td class="columnDate columnLastActivityTime">{@$user->lastActivityTime|plainTime}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>

    <footer class="contentFooter">
        {hascontent}
            <div class="paginationBottom">
                {content}{@$pagesLinks}{/content}
            </div>
        {/hascontent}

        {hascontent}
            <nav class="contentFooterNavigation">
                <ul>
                    {content}
                        <li><a href="{link controller='CompulsoryStats' id=$compulsory->compulsoryID}{/link}" class="button"><span class="icon icon16 fa-bar-chart-o"></span> <span>{lang}wcf.acp.compulsory.stats{/lang}</span></a></li>

                        {event name='contentFooterNavigation'}
                    {/content}
                </ul>
            </nav>
        {/hascontent}
    </footer>
{else}
    <p class="info">{lang}wcf.acp.compulsory.stats.none{/lang}</p>
{/if}

{include file='footer'}

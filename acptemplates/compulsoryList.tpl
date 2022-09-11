{include file='header' pageTitle='wcf.acp.compulsory.list'}

<script data-relocate="true">
	$(function() {
		new WCF.Action.Delete('wcf\\data\\compulsory\\CompulsoryAction', $('.jsCompulsoryRow'));
		new WCF.Action.Toggle('wcf\\data\\compulsory\\CompulsoryAction', $('.jsCompulsoryRow'));
		
		var options = { };
		{if $pages > 1}
			options.refreshPage = true;
			{if $pages == $pageNo}
				options.updatePageNumber = -1;
			{/if}
		{else}
			options.emptyMessage = '{lang}wcf.global.noItems{/lang}';
		{/if}
		
		new WCF.Table.EmptyTableHandler($('#compulsoryTableContainer'), 'jsCompulsoryRow', options);
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.compulsory.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='CompulsoryAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.compulsory.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="CompulsoryList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div id="compulsoryTableContainer" class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnCompulsoryID{if $sortField == 'compulsoryID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='CompulsoryList'}pageNo={@$pageNo}&sortField=compulsoryID&sortOrder={if $sortField == 'compulsoryID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnUsername{if $sortField == 'username'} active {@$sortOrder}{/if}"><a href="{link controller='CompulsoryList'}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.compulsory.username{/lang}</a></th>
					<th class="columnText columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link controller='CompulsoryList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.compulsory.time{/lang}</a></th>
					<th class="columnText columnTitle{if $sortField == 'title'} active {@$sortOrder}{/if}"><a href="{link controller='CompulsoryList'}pageNo={@$pageNo}&sortField=title&sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.compulsory.title{/lang}</a></th>
					<th class="columnText columnAccept{if $sortField == 'statAccept'} active {@$sortOrder}{/if}"><a href="{link controller='CompulsoryList'}pageNo={@$pageNo}&sortField=statAccept&sortOrder={if $sortField == 'statAccept' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.compulsory.statAccept{/lang}</a></th>
					<th class="columnText columnRefuse{if $sortField == 'statRefuse'} active {@$sortOrder}{/if}"><a href="{link controller='CompulsoryList'}pageNo={@$pageNo}&sortField=statRefuse&sortOrder={if $sortField == 'statRefuse' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.compulsory.statRefuse{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=compulsory}
					<tr class="jsCompulsoryRow">
						<td class="columnIcon">
							<span class="icon icon16 fa-{if !$compulsory->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $compulsory->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$compulsory->compulsoryID}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}"></span>
							<a href="{link controller='CompulsoryEdit' id=$compulsory->compulsoryID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							<span class="icon icon16 fa-remove jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$compulsory->compulsoryID}" data-confirm-message="{lang}wcf.acp.compulsory.delete.sure{/lang}"></span>
							{if $compulsory->activationTime}
								<a href="{link controller='CompulsoryStats' id=$compulsory->compulsoryID}{/link}" title="{lang}wcf.acp.compulsory.stats{/lang}" class="jsTooltip"><span class="icon icon16 fa-bar-chart-o"></span></a>
							{/if}
						</td>
						<td class="columnID columnCompulsoryID">{@$compulsory->compulsoryID}</td>
						<td class="columnText columnUsername">{$compulsory->username}</td>
						<td class="columnText columnTime">{@$compulsory->time|time}</td>
						<td class="columnText columnTitle">{lang}{$compulsory->title}{/lang}</td>
						<td class="columnText columnAccept">{@$compulsory->statAccept}</td>
						<td class="columnText columnRefuse">{@$compulsory->statRefuse}</td>
						
						{event name='columns'}
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
		
		<nav class="contentFooterNavigation">
			<ul>
				<li><a href="{link controller='CompulsoryAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.compulsory.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}

{include file='header' pageTitle='wcf.acp.compulsory.stats'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}{$compulsory->getTitle()}{/lang}</h1>
		{if !$compulsory->hasPeriod}
			<p class="contentDescription">{@$compulsory->time|date}</p>
		{else}
			<p class="contentDescription">{@$compulsory->periodStart|date} - {@$compulsory->periodEnd|date}</p>
		{/if}
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='CompulsoryList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.compulsory.list{/lang}</span></a></li>
			<li><a href="{link controller='CompulsoryEdit' id=$compulsory->compulsoryID}{/link}" class="button"><span class="icon icon16 fa-pencil"></span> <span>{lang}wcf.global.button.edit{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>
{if USER_COMPULSORY_STATS_OLD}
	{assign var=lastPeriod value=''}
	<section class="section">
		<h1 class="sectionTitle">{lang}wcf.acp.compulsory.stats.accept{/lang} <span class="badge">{#$acceptCount}</span></h1>
		
		{if $acceptCount}
			{foreach from=$acceptUsers item=$dismiss}
				{if $dismiss->getPeriod() != $lastPeriod}
					{if $lastPeriod}
							</ul>
						</section>
					{/if}
					{assign var=lastPeriod value=$dismiss->getPeriod()}
					
					<section class="section">
						<h2 class="sectionTitle">{$lastPeriod}</h2>
						
						<ul class="inlineList commaSeparated">
				{/if}
						<li>{$dismiss->username}</li>
			{/foreach}
				</ul>
			</section>
		{else}
			{lang}wcf.acp.compulsory.stats.none{/lang}
		{/if}
	</section>
	
	{assign var=lastPeriod value=''}
	<section class="section">
		<h1 class="sectionTitle">{lang}wcf.acp.compulsory.stats.refuse{/lang} <span class="badge">{#$refuseCount}</span></h1>
		
		{if $refuseCount}
			{foreach from=$refuseUsers item=$dismiss}
				{if $dismiss->getPeriod() != $lastPeriod}
					{if $lastPeriod}
							</ul>
						</section>
					{/if}
					{assign var=lastPeriod value=$dismiss->getPeriod()}
					
					<section class="section">
						<h2 class="sectionTitle">{$lastPeriod}</h2>
						
						<ul class="inlineList commaSeparated">
				{/if}
						<li>{$dismiss->username}</li>
			{/foreach}
				</ul>
			</section>
		{else}
			{lang}wcf.acp.compulsory.stats.none{/lang}
		{/if}
	</section>
	
	<section class="section">
		<h1 class="sectionTitle">{lang}wcf.acp.compulsory.stats.remaining{/lang} <span class="badge">{#$remainingCount}</span></h1>
		
		{if $remainingCount}
			<ul class="inlineList commaSeparated">
				{foreach from=$remainingUsers item=$user}
					<li>{$user->username}</li>
				{/foreach}
			</ul>
		{else}
			{lang}wcf.acp.compulsory.stats.none{/lang}
		{/if}
	</section>
{else}
	<section class="section">
		<h1 class="sectionTitle">{lang}wcf.acp.compulsory.stats.accept{/lang} <span class="badge">{lang}wcf.acp.compulsory.stats.accept.count{/lang}</span></h1>
		
		{if $acceptCount}
			<p>{lang}wcf.acp.compulsory.stats.accept.last{/lang}</p>
			<ul class="nativeList">
				{foreach from=$acceptUsers item=$dismiss}
					<li><strong>{$dismiss->username}</strong> <small>{@$dismiss->time|plainTime}</small></li>
				{/foreach}
			</ul>
			{if $acceptCountDeleted}
				<p>{lang}wcf.acp.compulsory.stats.accept.deleted{/lang}</p>
				<br>
			{/if}
			<p><a href="{link controller='CompulsoryStatsAccept' id=$compulsory->compulsoryID}{/link}">{lang}wcf.acp.compulsory.stats.show.all{/lang}</a></p>
			
		{else}
			<p>{lang}wcf.acp.compulsory.stats.none{/lang}</p>
			{if $acceptCountDeleted}
				<p>{lang}wcf.acp.compulsory.stats.accept.deleted{/lang}</p>
			{/if}
		{/if}
	</section>
	
	<section class="section">
		<h1 class="sectionTitle">{lang}wcf.acp.compulsory.stats.refuse{/lang} <span class="badge">{lang}wcf.acp.compulsory.stats.refuse.count{/lang}</span></h1>
		
		{if $refuseCount}
			<p>{lang}wcf.acp.compulsory.stats.refuse.last{/lang}</p>
			<ul class="nativeList">
				{foreach from=$refuseUsers item=$dismiss}
					<li><strong>{$dismiss->username}</strong> <small>{@$dismiss->time|plainTime}</small></li>
				{/foreach}
			</ul>
			{if $refuseCountDeleted}
				<p>{lang}wcf.acp.compulsory.stats.refuse.deleted{/lang}</p>
				<br>
			{/if}
			<p><a href="{link controller='CompulsoryStatsRefuse' id=$compulsory->compulsoryID}{/link}">{lang}wcf.acp.compulsory.stats.show.all{/lang}</a></p>
			
		{else}
			{lang}wcf.acp.compulsory.stats.none{/lang}
			{if $refuseCountDeleted}
				<p>{lang}wcf.acp.compulsory.stats.refuse.deleted{/lang}</p>
			{/if}
		{/if}
	</section>
	
	<section class="section">
		<h1 class="sectionTitle">{lang}wcf.acp.compulsory.stats.remaining{/lang} <span class="badge">{#$remainingCount}</span></h1>
		
		{if $remainingCount}
			<p>{lang}wcf.acp.compulsory.stats.remaining.left{/lang}</p>
			<br>
			<a href="{link controller='CompulsoryStatsRemaining' id=$compulsory->compulsoryID}{/link}">{lang}wcf.acp.compulsory.stats.show.all{/lang}</a>
			
		{else}
			{lang}wcf.acp.compulsory.stats.none{/lang}
		{/if}
	</section>
{/if}

{include file='footer'}

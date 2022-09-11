{capture assign='pageTitle'}{lang}wcf.user.compulsory.topic{/lang}{/capture}

{if USER_COMPULSORY_DEFAULTTITLE}
	{capture assign='contentTitle'}{lang}wcf.user.compulsory.topic{/lang}{/capture}
{else}
	{capture assign='contentTitle'}{$content->subject}<br><small>{@$compulsory->time|date}</small>{/capture}
{/if}

{include file='header' __disableAds=true}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

<section class="section">
	{if USER_COMPULSORY_DEFAULTTITLE}
		<header class="sectionHeader">
			<h2 class="sectionTitle">{$content->subject}</h2>
			<p class="sectionDescription">{@$compulsory->time|date}</p>
		</header>
	{/if}
	
	<div class="htmlContent">
		{@$content->getFormattedContent()}
	</div>
</section>

<div class="formSubmit">
	{if $compulsory}
		<a class="button" href="{link controller='CompulsoryAccept' id=$compulsory->compulsoryID}t={csrfToken type=url}{/link}" onclick="WCF.System.Confirmation.show('{lang __encode=true}wcf.user.compulsory.button.accept.sure{/lang}', $.proxy(function (action) { if (action == 'confirm') window.location.href = $(this).attr('href'); }, this), undefined, undefined, true); return false;">
			<span>{lang}wcf.user.compulsory.button.accept{/lang}</span>
		</a>
		{if $compulsory->isRefusable}
			<a class="button" href="{link controller='CompulsoryRefuse' id=$compulsory->compulsoryID}t={csrfToken type=url}{/link}" onclick="WCF.System.Confirmation.show('{lang __encode=true}wcf.user.compulsory.button.refuse.sure{/lang}', $.proxy(function (action) { if (action == 'confirm') window.location.href = $(this).attr('href'); }, this), undefined, undefined, true); return false;">
				<span>{lang}wcf.user.compulsory.button.refuse{/lang}</span>
			</a>
		{/if}
		{if USER_COMPULSORY_PRINT}
			<a class="button" href="javascript:window.print();"> <span>{lang}wcf.user.compulsory.button.print{/lang}</span></a>
		{/if}
	{else}
		{lang}wcf.user.compulsory.button.error.fatal{/lang}
	{/if}
</div>

{include file='footer' __disableAds=true}

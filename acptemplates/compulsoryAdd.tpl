{include file='header' pageTitle='wcf.acp.compulsory.'|concat:$action}

{if $action == 'edit'}
	<script data-relocate="true">
		require(['Language', 'UZ/Compulsory/Acp/Copy'], function(Language, CompulsoryAcpCopy) {
			Language.addObject({
				'wcf.acp.compulsory.copy.confirm': '{jslang}wcf.acp.compulsory.copy.confirm{/jslang}'
			});
			new CompulsoryAcpCopy();
		});
	</script>
{/if}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.compulsory.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $action == 'edit'}
				<li><a class="jsButtonCompulsoryCopy button" data-object-id="{@$compulsoryID}"><span class="icon icon16 fa-files-o"></span> <span>{lang}wcf.acp.compulsory.copy{/lang}</span></a></li>
			{/if}
			
			<li><a href="{link controller='CompulsoryList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.compulsory.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if $activationTime && $action == 'edit'}
	<p class="warning">{lang}wcf.acp.compulsory.isDisabled.warning{/lang}</p>
{/if}

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{@$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link controller='CompulsoryAdd'}{/link}{else}{link controller='CompulsoryEdit' id=$compulsoryID}{/link}{/if}">
	<div class="section tabMenuContainer">
		<nav class="tabMenu">
			<ul>
				<li><a href="{@$__wcf->getAnchor('tabGeneral')}">{lang}wcf.acp.compulsory.general{/lang}</a></li>
				<li><a href="{@$__wcf->getAnchor('tabContent')}">{lang}wcf.acp.compulsory.display{/lang}</a></li>
				<li><a href="{@$__wcf->getAnchor('tabAction')}">{lang}wcf.acp.compulsory.action{/lang}</a></li>
				<li><a href="{@$__wcf->getAnchor('tabCondition')}">{lang}wcf.acp.compulsory.conditions{/lang}</a></li>
			</ul>
		</nav>
		
		<div id="tabGeneral" class="tabMenuContent hidden">
			<div class="section">
				<header class="sectionHeader">
					<h2 class="sectionTitle">{lang}wcf.acp.compulsory.general{/lang}</h2>
					<p class="sectionDescription">{lang}wcf.acp.compulsory.general.description{/lang}</p>
				</header>
				
				<!-- title -->
				<dl{if $errorField == 'title'} class="formError"{/if}>
					<dt><label for="title">{lang}wcf.acp.compulsory.title{/lang}</label></dt>
					<dd>
						<input type="text" id="title" name="title" value="{$i18nPlainValues['title']}"  class="long" maxlength="80" />
						<small>{lang}wcf.acp.compulsory.title.description{/lang}</small>
						{if $errorField == 'title'}
							<small class="innerError">
								{if $errorType == 'multilingual'}
									{lang}wcf.global.form.error.multilingual{/lang}
								{elseif $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.compulsory.title.error.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				{include file='multipleLanguageInputJavascript' elementIdentifier='title' forceSelection=false}
				
				<!-- isDisabled -->
				<dl>
					<dt></dt>
					<dd>
						<label><input type="checkbox" name="isDisabled" value="1"{if $isDisabled} checked{/if}> {lang}wcf.acp.compulsory.isDisabled{/lang}</label>
						<small>{lang}wcf.acp.compulsory.isDisabled.description{/lang}</small>
					</dd>
				</dl>
				
				<!-- isRefusable -->
				<dl>
					<dt></dt>
					<dd>
						<label><input type="checkbox" name="isRefusable" value="1"{if $isRefusable} checked{/if}> {lang}wcf.acp.compulsory.isRefusable{/lang}</label>
						<small>{lang}wcf.acp.compulsory.isRefusable.description{/lang}</small>
					</dd>
				</dl>
				
				<!-- addNewUser -->
				<dl>
					<dt></dt>
					<dd>
						<label><input type="checkbox" name="addNewUser" value="1"{if $addNewUser} checked{/if}> {lang}wcf.acp.compulsory.addNewUser{/lang}</label>
						<small>{lang}wcf.acp.compulsory.addNewUser.description{/lang}</small>
					</dd>
				</dl>
				
				<!-- hasPeriod -->
				<dl>
					<dt></dt>
					<dd>
						<label><input type="checkbox" id="hasPeriod" name="hasPeriod" value="1"{if $hasPeriod} checked{/if}> {lang}wcf.acp.compulsory.hasPeriod{/lang}</label>
					</dd>
				</dl>
				
				<dl id="periodSettings"{if $errorField == 'period'} class="formError"{/if}>
					<dt><label for="period" id="period">{lang}wcf.acp.compulsory.period{/lang}</label></dt>
					<dd>
						<input type="datetime" id="periodStart" name="periodStart" value="{$periodStart}" placeholder="{lang}wcf.acp.compulsory.period.start{/lang}">
						<input type="datetime" id="periodEnd" name="periodEnd" value="{$periodEnd}" placeholder="{lang}wcf.acp.compulsory.period.end{/lang}">
						<small>{lang}wcf.acp.compulsory.period.description{/lang}</small>

						{if $errorField == 'period'}
							<small class="innerError">
								{lang}wcf.acp.compulsory.period.error.{$errorType}{/lang}
							</small>
						{/if}
						
						<script data-relocate="true">
							$('#hasPeriod').change(function (event) {
								if ($('#hasPeriod').is(':checked')) {
									$('#periodSettings').show();
								}
								else {
									$('#periodSettings').hide();
								}
							});
							$('#hasPeriod').change();
						</script>
						
					</dd>
				</dl>
			</div>
		</div>
		
		<div id="tabContent" class="tabMenuContent hidden">
			<div class="section">
				<header class="sectionHeader">
					<h2 class="sectionTitle">{lang}wcf.acp.compulsory.display{/lang}</h2>
					<p class="sectionDescription">{lang}wcf.acp.compulsory.display.description{/lang}</p>
				</header>
				
				<!-- content -->
				{if !$isMultilingual}
					<div class="section">
						<!-- subject -->
						<dl{if $errorField == 'subject'} class="formError"{/if}>
							<dt><label for="subject0">{lang}wcf.acp.compulsory.subject{/lang}</label></dt>
							<dd>
								<input type="text" id="subject0" name="subject[0]" value="{if !$subject[0]|empty}{$subject[0]}{/if}" class="long" maxlength="255">
								<small>{lang}wcf.acp.compulsory.subject.description{/lang}</small>
								
								{if $errorField == 'subject'}
									<small class="innerError">
										{if $errorType == 'empty'}
											{lang}wcf.global.form.error.empty{/lang}
										{else}
											{lang}wcf.acp.compulsory.subject.error.{$errorType}{/lang}
										{/if}
									</small>
								{/if}
							</dd>
						</dl>
						
						<dl{if $errorField == 'content'} class="formError"{/if}>
							<dt><label for="content0">{lang}wcf.acp.compulsory.content{/lang}</label></dt>
							<dd>
								<textarea name="content[0]" id="content0" class="wysiwygTextarea" data-autosave="com.uz.wcf.compulsory{$action|ucfirst}-{if $action == 'edit'}{@$compulsoryID}{else}0{/if}-0">{if !$content[0]|empty}{$content[0]}{/if}</textarea>
								<small>{lang}wcf.acp.compulsory.content.description{/lang}</small>
								{include file='wysiwyg' wysiwygSelector='content0'}
								
								{if $errorField == 'content'}
									<small class="innerError">
										{if $errorType == 'empty'}
											{lang}wcf.global.form.error.empty{/lang}
										{else}
											{lang}wcf.acp.compulsory.content.error.{@$errorType}{/lang}
										{/if}
									</small>
								{/if}
							</dd>
						</dl>
					</div>
				{else}
					<div class="section tabMenuContainer">
						<nav class="tabMenu">
							<ul>
								{foreach from=$availableLanguages item=availableLanguage}
									{assign var='containerID' value='language'|concat:$availableLanguage->languageID}
									<li><a href="{@$__wcf->getAnchor($containerID)}">{$availableLanguage->languageName}</a></li>
								{/foreach}
							</ul>
						</nav>
						
						{foreach from=$availableLanguages item=availableLanguage}
							<div id="language{@$availableLanguage->languageID}" class="tabMenuContent">
								<div class="section">
									<dl{if $errorField == 'subject'|concat:$availableLanguage->languageID} class="formError"{/if}>
										<dt><label for="subject{@$availableLanguage->languageID}">{lang}wcf.acp.compulsory.subject{/lang}</label></dt>
										<dd>
											<input type="text" id="subject{@$availableLanguage->languageID}" name="subject[{@$availableLanguage->languageID}]" value="{if !$subject[$availableLanguage->languageID]|empty}{$subject[$availableLanguage->languageID]}{/if}" class="long" maxlength="255">
											<small>{lang}wcf.acp.compulsory.subject.description{/lang}</small>
											{if $errorField == 'subject'|concat:$availableLanguage->languageID}
												<small class="innerError">
													{if $errorType == 'empty'}
														{lang}wcf.global.form.error.empty{/lang}
													{else}
														{lang}wcf.acp.compulsory.subject.error.{$errorType}{/lang}
													{/if}
												</small>
											{/if}
										</dd>
									</dl>
									
									<dl{if $errorField == 'content'|concat:$availableLanguage->languageID} class="formError"{/if}>
										<dt><label for="content{@$availableLanguage->languageID}">{lang}wcf.acp.compulsory.content{/lang}</label></dt>
										<dd>
											<textarea name="content[{@$availableLanguage->languageID}]" id="content{@$availableLanguage->languageID}" class="wysiwygTextarea" data-autosave="com.uz.wcf.compulsory{$action|ucfirst}-{if $action == 'edit'}{@$compulsoryID}{else}0{/if}-{@$availableLanguage->languageID}">{if !$content[$availableLanguage->languageID]|empty}{$content[$availableLanguage->languageID]}{/if}</textarea>
											<small>{lang}wcf.acp.compulsory.content.description{/lang}</small>
											{include file='wysiwyg' wysiwygSelector='content'|concat:$availableLanguage->languageID}
											
											{if $errorField == 'content'|concat:$availableLanguage->languageID}
												<small class="innerError">
													{if $errorType == 'empty'}
														{lang}wcf.global.form.error.empty{/lang}
													{else}
														{lang}wcf.acp.compulsory.content.error.{@$errorType}{/lang}
													{/if}
												</small>
											{/if}
										</dd>
									</dl>
								</div>
							</div>
						{/foreach}
					</div>
				{/if}
			</div>
		</div>
		
		<div id="tabAction" class="tabMenuContent hidden">
			<div class="section">
				<header class="sectionHeader">
					<h2 class="sectionTitle">{lang}wcf.acp.compulsory.action{/lang}</h2>
					<p class="sectionDescription">{lang}wcf.acp.compulsory.action.description{/lang}</p>
				</header>
				
					<div class="section tabMenuContainer">
					<nav class="tabMenu">
						<ul>
							<li><a href="{@$__wcf->getAnchor('tabActionAccept')}">{lang}wcf.acp.compulsory.action.accept{/lang}</a></li>
							<li><a href="{@$__wcf->getAnchor('tabActionRefuse')}">{lang}wcf.acp.compulsory.action.refuse{/lang}</a></li>
						</ul>
					</nav>
					
					<div id="tabActionAccept" class="tabMenuContent hidden">
						
						<div class="section">
							<header class="sectionHeader">
								<h2 class="sectionTitle">{lang}wcf.acp.compulsory.action.url{/lang}</h2>
								<p class="sectionDescription">{lang}wcf.acp.compulsory.action.url.description{/lang}</p>
							</header>
							
							<dl>
								<dt></dt>
								<dd>
									<input type="text" id="acceptUrl" name="acceptUrl" value="{$acceptUrl}" maxlength="255" class="long" />
								</dd>
							</dl>
						</div>
						
						<div class="section">
							<header class="sectionHeader">
								<h2 class="sectionTitle">{lang}wcf.acp.compulsory.action.user{/lang}</h2>
								<p class="sectionDescription">{lang}wcf.acp.compulsory.action.user.description{/lang}</p>
							</header>
							
							<dl>
								<dt></dt>
								<dd class="floated">
									<label><input type="radio" name="acceptUserAction" value="none"{if $acceptUserAction == "none"} checked{/if}> {lang}wcf.acp.compulsory.action.user.none{/lang}</label>
									<label><input type="radio" name="acceptUserAction" value="enable"{if $acceptUserAction == "enable"} checked{/if}> {lang}wcf.acp.compulsory.action.user.enable{/lang}</label>
									<label><input type="radio" name="acceptUserAction" value="disable"{if $acceptUserAction == "disable"} checked{/if}> {lang}wcf.acp.compulsory.action.user.disable{/lang}</label>
									<label><input type="radio" name="acceptUserAction" value="ban"{if $acceptUserAction == "ban"} checked{/if}> {lang}wcf.acp.compulsory.action.user.ban{/lang}</label>
								</dd>
							</dl>
						</div>
						
						<div class="section">
							<header class="sectionHeader">
								<h2 class="sectionTitle">{lang}wcf.acp.compulsory.action.group.add{/lang}</h2>
								<p class="sectionDescription">{lang}wcf.acp.compulsory.action.group.add.description{/lang}</p>
							</header>
							
							<dl{if $errorField == 'acceptAddGroupIDs'} class="formError"{/if}>
								<dt></dt>
								<dd>
									{htmlCheckboxes options=$groups name=acceptAddGroupIDs selected=$acceptAddGroupIDs}
									{if $errorField == 'acceptAddGroupIDs'}
										<small class="innerError">
											{if $errorType == 'invalidGroup'}{lang}wcf.acp.compulsory.action.group.error.invalidGroup{/lang}{/if}
										</small>
									{/if}
								<dd>
							</dl>
						</div>
						
						<div class="section">
							<header class="sectionHeader">
								<h2 class="sectionTitle">{lang}wcf.acp.compulsory.action.group.remove{/lang}</h2>
								<p class="sectionDescription">{lang}wcf.acp.compulsory.action.group.remove.description{/lang}</p>
							</header>
							
							<dl{if $errorField == 'acceptRemoveGroupIDs'} class="formError"{/if}>
								<dt></dt>
								<dd>
									{htmlCheckboxes options=$groups name=acceptRemoveGroupIDs selected=$acceptRemoveGroupIDs}
									{if $errorField == 'acceptRemoveGroupIDs'}
										<small class="innerError">
											{if $errorType == 'invalidGroup'}{lang}wcf.acp.compulsory.action.group.error.invalidGroup{/lang}{/if}
										</small>
									{/if}
								<dd>
							</dl>
						</div>
					</div>
					
					<div id="tabActionRefuse" class="tabMenuContent hidden">
						<div class="section">
							<header class="sectionHeader">
								<h2 class="sectionTitle">{lang}wcf.acp.compulsory.action.url{/lang}</h2>
								<p class="sectionDescription">{lang}wcf.acp.compulsory.action.url.description{/lang}</p>
							</header>
							
							<dl>
								<dt></dt>
								<dd>
									<input type="text" id="refuseUrl" name="refuseUrl" value="{$refuseUrl}" maxlength="255" class="long" />
								</dd>
							</dl>
						</div>
						
						<div class="section">
							<header class="sectionHeader">
								<h2 class="sectionTitle">{lang}wcf.acp.compulsory.action.user{/lang}</h2>
								<p class="sectionDescription">{lang}wcf.acp.compulsory.action.user.description{/lang}</p>
							</header>
							
							<dl>
								<dt></dt>
								<dd class="floated">
									<label><input type="radio" name="refuseUserAction" value="none"{if $refuseUserAction == "none"} checked{/if}> {lang}wcf.acp.compulsory.action.user.none{/lang}</label>
									<label><input type="radio" name="refuseUserAction" value="enable"{if $refuseUserAction == "enable"} checked{/if}> {lang}wcf.acp.compulsory.action.user.enable{/lang}</label>
									<label><input type="radio" name="refuseUserAction" value="disable"{if $refuseUserAction == "disable"} checked{/if}> {lang}wcf.acp.compulsory.action.user.disable{/lang}</label>
									<label><input type="radio" name="refuseUserAction" value="ban"{if $refuseUserAction == "ban"} checked{/if}> {lang}wcf.acp.compulsory.action.user.ban{/lang}</label>
								</dd>
							</dl>
						</div>
						
						<div class="section">
							<header class="sectionHeader">
								<h2 class="sectionTitle">{lang}wcf.acp.compulsory.action.group.add{/lang}</h2>
								<p class="sectionDescription">{lang}wcf.acp.compulsory.action.group.add.description{/lang}</p>
							</header>
							
							<dl{if $errorField == 'refuseAddGroupIDs'} class="formError"{/if}>
								<dt></dt>
								<dd>
									{htmlCheckboxes options=$groups name=refuseAddGroupIDs selected=$refuseAddGroupIDs}
									{if $errorField == 'refuseAddGroupIDs'}
										<small class="innerError">
											{if $errorType == 'invalidGroup'}{lang}wcf.acp.compulsory.action.group.error.invalidGroup{/lang}{/if}
										</small>
									{/if}
								<dd>
							</dl>
						</div>
						
						<div class="section">
							<header class="sectionHeader">
								<h2 class="sectionTitle">{lang}wcf.acp.compulsory.action.group.remove{/lang}</h2>
								<p class="sectionDescription">{lang}wcf.acp.compulsory.action.group.remove.description{/lang}</p>
							</header>
							
							<dl{if $errorField == 'refuseRemoveGroupIDs'} class="formError"{/if}>
								<dt></dt>
								<dd>
									{htmlCheckboxes options=$groups name=refuseRemoveGroupIDs selected=$refuseRemoveGroupIDs}
									{if $errorField == 'refuseRemoveGroupIDs'}
										<small class="innerError">
											{if $errorType == 'invalidGroup'}{lang}wcf.acp.compulsory.action.group.error.invalidGroup{/lang}{/if}
										</small>
									{/if}
								<dd>
							</dl>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div id="tabCondition" class="tabMenuContent hidden">
			<div class="section">
				<header class="sectionHeader">
					<h2 class="sectionTitle">{lang}wcf.acp.compulsory.pages{/lang}</h2>
					<p class="sectionDescription">{lang}wcf.acp.compulsory.pages.description{/lang}</p>
				</header>
				
				<dl>
					<dt></dt>
					<dd>
						<textarea id="pages" name="pages" cols="40" rows="10">{$pages}</textarea>
					</dd>
				</dl>
			</div>
			
			<div class="section">
				<header class="sectionHeader">
					<h2 class="sectionTitle">{lang}wcf.acp.compulsory.conditions{/lang}</h2>
					<p class="sectionDescription">{lang}wcf.acp.compulsory.conditions.description{/lang}</p>
				</header>
				
				{include file='userConditions'}
			</div>
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		<input type="hidden" name="isMultilingual" value="{@$isMultilingual}">
		{csrfToken}
	</div>
</form>

{include file='footer'}

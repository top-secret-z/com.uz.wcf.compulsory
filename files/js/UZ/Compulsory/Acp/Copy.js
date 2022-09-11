/**
 * Copies a compulsory topic.
 * 
 * @author		2017-2022 Darkwood.Design
 * @license		Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package		com.uz.wcf.compulsory
 */
define(['Ajax', 'Language', 'Ui/Confirmation', 'Ui/Notification'], function(Ajax, Language, UiConfirmation, UiNotification) {
	"use strict";
	
	function CompulsoryAcpCopy() { this.init(); }
	
	CompulsoryAcpCopy.prototype = {
		init: function() {
			var button = elBySel('.jsButtonCompulsoryCopy');
			
			button.addEventListener(WCF_CLICK_EVENT, this._click.bind(this));
		},
		
		_click: function(event) {
			event.preventDefault();
			var objectID = ~~elData(event.currentTarget, 'object-id');
			
			UiConfirmation.show({
				confirm: function() {
					Ajax.apiOnce({
						data: {
							actionName: 'copy',
							className: 'wcf\\data\\compulsory\\CompulsoryAction',
							parameters: {
								objectID: objectID
							}
						},
						success: function(data) {
							UiNotification.show();
							window.location = data.returnValues.redirectURL;
						}
					});
				},
				message: Language.get('wcf.acp.compulsory.copy.confirm')
			});	
		}
	};
	return CompulsoryAcpCopy;
});

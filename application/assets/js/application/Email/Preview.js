Ext.ns('ExtMail', 'ExtMail.Email');
ExtMail.Email.Preview = Ext.extend(Ext.Panel, {
	layout: 'border',
	borders: false,
	cls: 'preview',
	loadMask: null,
	initComponent: function() {
		
		this.headerTpl = new Ext.Template([
		                             '<div class="email-header">',
		                             '	<div class="email-date">' + _('Date:') + ' {date:date("d.m.Y H:i")}</div>',
		                             '	<h3 class="email-subject">{subject}</h3>',
		                             '	<h4 class="email-from">' + _('From:') + ' {sender:defaultValue("Unknown")}</h4>',
		                             '</div>' ,
		                             ], {
			compiled: true
		});
		
		this.headerId = Ext.id();
		this.bodyId = Ext.id();
		
		Ext.apply(this, {
			items: [{
				id: this.headerId,
				region: 'north',
				autoHeight: true
			}, {
				id: this.bodyId,
				autoScroll: true,
				region: 'center'
			}]
		});
		
		ExtMail.Email.Preview.superclass.initComponent.call(this);
	},
	getTemplate: function() {
		return this.headerTpl;
	},
	getHeader: function() {
		return Ext.getCmp(this.headerId);
	},
	getBody: function() {
		return Ext.getCmp(this.bodyId);
	},
	showLoading: function() {
		if (null === this.loadMask) {
			this.loadMask = new Ext.LoadMask(this.body, {msg: _('Loading message ...')});
		}
		this.loadMask.show();
		this.mainpanel.getSouth().showBusy(_('Loading preview ...'));
	},
	hideLoading: function() {
		this.loadMask.hide();
		this.mainpanel.getSouth().clearStatus();
	},
	scrollToTop: function() {
		var dom = this.getBody().getEl().child('.x-panel-body').dom;
		
		dom.scrollTop  = 0;
		dom.scrollLeft = 0;
	}
});
Ext.reg('extmail_email_preview', ExtMail.Email.Preview);
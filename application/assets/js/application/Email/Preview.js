Ext.ns('ExtMail', 'ExtMail.Email');
ExtMail.Email.Preview = Ext.extend(Ext.Panel, {
	layout: 'border',
	borders: false,
	cls: 'preview',
	loadMask: null,
	sourceWindow: null,
	initComponent: function() {
		this.headerId = Ext.id();
		this.bodyId = Ext.id();
		
		Ext.apply(this, {
			tbar: new Ext.Toolbar({
				hidden: true,
				items: ['->', {
					text: _('Reply'),
					iconCls: 'ico_reply',
					disabled: true
				}, {
					text: _('Forward'),
					iconCls: 'ico_forward',
					disabled: true
				}, {
					text: _('Archive'),
					iconCls: 'ico_archive',
					disabled: true
				}, {
					text: _('Junk'),
					iconCls: 'ico_junk',
					disabled: true
				}, {
					text: _('Delete'),
					iconCls: 'ico_delete',
					handler: this.removeMessage,
					scope: this
				}, {
					iconCls: 'x-toolbar-more-icon',
					menu: [{
						text: _('View Source'),
						iconCls: 'ico_source',
						handler: this.showSource,
						scope: this
					}]
				}]
			}),
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
		
		this.headerTpl = new Ext.Template([
		                             '<div class="email-header">',
		                             '	<div class="email-date">' + _('Date:') + ' {date:date("d.m.Y H:i")}</div>',
		                             '	<h3 class="email-subject">{subject}</h3>',
		                             '	<h4 class="email-from">' + _('From:') + ' {sender:defaultValue("Unknown")}</h4>',
		                             '</div>' ,
		                             ], {
			compiled: true
		});
		
		this.addEvents('source', 'removemessage', 'junk', 'archive', 'forward', 'reply', 'showimages');
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
		if (null !== this.loadMask) {
			this.loadMask.hide();
		}
		this.mainpanel.getSouth().clearStatus();
	},
	showImagesBlocked: function() {
		var emailBody = this.getBody().getEl().child('.email-body'),
			divId = Ext.id(),
			button;
		
		emailBody.insertFirst({
			tag: 'div',
			cls: 'image-info',
			children: [{
				tag: 'div',
				id: divId,
				html: _('To protect your privacy, remote images are blocked in this message.')
			}]
		});
		
		button = new Ext.Button({
			applyTo: divId,
			text: _('Display images'),
			handler: this.showImages,
			scope: this
		});
	},
	scrollToTop: function() {
		var dom = this.getBody().getEl().child('.x-panel-body').dom;
		
		dom.scrollTop  = 0;
		dom.scrollLeft = 0;
	},
	removeMessage: function() {
		this.fireEvent('removemessage', this);
	},
	showImages: function() {
		this.fireEvent('showimages', this);
	},
	showSource: function() {
		if (this.sourceWindow == null) {
			this.sourceWindow = new ExtMail.Email.SourceWindow({
				closeAction: 'hide',
				layout: 'fit',
				width: 800,
				height: 350,
				plain: true,
				iconCls: 'ico_source',
				autoScroll: true
			});
		}
		this.fireEvent('source', this.sourceWindow);
	},
	restoreDefault: function() {
		this.getHeader().update('');
		this.getBody().update('');
		this.getTopToolbar().hide();
	}
});
Ext.reg('extmail_email_preview', ExtMail.Email.Preview);
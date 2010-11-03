Ext.ns('ExtMail', 'ExtMail.Email');
ExtMail.Email.Preview = Ext.extend(Ext.Panel, {
	initComponent: function() {
		ExtMail.Email.Preview.superclass.initComponent.call(this);
		
		this.tpl = new Ext.Template([
		                             '<div class="preview">',
		                             '	<div class="email-header">',
		                             '		<div class="email-date">' + _('Date:') + ' {date:date("d.m.Y H:i")}</div>',
		                             '		<h3 class="email-subject">{subject}</h3>',
		                             '		<h4 class="email-from">' + _('From:') + ' {sender:defaultValue("Unknown")}</h4>',
		                             '	</div>' ,
		                             '	<div class="email-body">{body:this.getBody}</div>',
		                             '</div>'
		                             ], {
			compiled: true
		});
		
		this.tpl.getBody = function(v, all) {
			console.log(v);
			return Ext.util.Format.stripScripts(v.content);
		};
	
	},
	getTemplate: function() {
		return this.tpl;
	}
});
Ext.reg('extmail_email_preview', ExtMail.Email.Preview);
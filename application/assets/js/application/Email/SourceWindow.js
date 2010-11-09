Ext.ns('ExtMail', 'ExtMail.Email');
ExtMail.Email.SourceWindow = Ext.extend(Ext.Window, {
	loadMask: null,
	show: function() {
		ExtMail.Email.SourceWindow.superclass.show.call(this);
		
		this.getContentTarget().update('&nbsp;');
		if (this.loadMask == null) {
			this.loadMask = new Ext.LoadMask(this.body, {msg:_('Loading source ...')});
		}
		this.loadMask.show();
	},
	update: function() {
		ExtMail.Email.SourceWindow.superclass.update.apply(this, arguments);
		this.loadMask.hide();
	}
});
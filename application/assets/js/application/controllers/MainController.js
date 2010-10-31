Ext.ns('ExtMail.Controllers');
ExtMail.Controllers.MainController = Ext.extend(Stachl.Controller, {
	title: 'Email - ExtMail - Stachl.me',
	copyright: '© 2010 by <a href="http://www.stachl.me/" target="_blank" title="Stachl.me">Stachl.me</a>',
	initComponent: function() {
		ExtMail.Controllers.MainController.superclass.initComponent.call(this);
	},
	show: function() {
		this.views.add('mainpanel', new ExtMail.MainPanel());
		this.setActiveItem('mainpanel').doLayout();
		ExtMail.Controllers.MainController.superclass.show.call(this);
	}
});
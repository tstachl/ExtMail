Ext.ns('ExtMail');
ExtMail.MainPanel = Ext.extend(Stachl.Module, {
	west: 'extmail_navigation',
	westConfig: {
		split: true,
		width: 250
	},
	center: 'extmail_cardcontainer',
	south: 'extmail_status'
});
Ext.ns('ExtMail', 'ExtMail');
ExtMail.CardContainer = Ext.extend(Stachl.CardContainer, {
	addIconCls: false,
	addTitle: false
});
Ext.reg('extmail_cardcontainer', ExtMail.CardContainer);
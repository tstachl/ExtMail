Ext.ns('ExtMail');
ExtMail.Status = Ext.extend(Ext.ux.StatusBar, {
	defaultText: '© 2010 by <a href="http://www.stachl.me/" target="_blank" title="Stachl.me">Stachl.me</a>',
	initComponent: function() {
		ExtMail.Status.superclass.initComponent.call(this);
	},
	clearStatus: function(o) {
        o = o || {};

        if(o.threadId && o.threadId !== this.activeThreadId){
            // this means the current call was made internally, but a newer
            // thread has set a message since this call was deferred.  Since
            // we don't want to overwrite a newer message just ignore.
            return this;
        }

        var text = this.defaultText,
            iconCls = o.useDefaults ? (this.defaultIconCls ? this.defaultIconCls : '') : '';

        if(o.anim){
            // animate the statusEl Ext.Element
            this.statusEl.el.fadeOut({
                remove: false,
                useDisplay: true,
                scope: this,
                callback: function(){
                    this.setStatus({
	                    text: text,
	                    iconCls: iconCls
	                });

                    this.statusEl.el.show();
                }
            });
        }else{
            // hide/show the el to avoid jumpy text or icon
            this.statusEl.hide();
	        this.setStatus({
	            text: text,
	            iconCls: iconCls
	        });
            this.statusEl.show();
        }
        return this;	
	}
});
Ext.reg('extmail_status', ExtMail.Status);
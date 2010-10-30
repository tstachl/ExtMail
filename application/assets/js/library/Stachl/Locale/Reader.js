Ext.ns('Stachl', 'Stachl.Locale');
Stachl.Locale.Reader = Ext.extend(Ext.data.DataReader, {
	read: function(r) {
		var j = r.responseText;
		if(!j)
			throw {message: "Stachl.Locale.Reader.read: File not found"};
						
		return this.readRecords(j);
	},
	
	readRecords: function(j){
		var o = Ext.decode(j);
		var totalRecords = parseInt(o.translations), success = true;
		var records = [];

		myRecords = o.records;

		for (var key in myRecords) {
			var value = myRecords[key];
			var record = new Ext.data.Record(value, key);
			records.push(record);	
		}
		
		return {
	        success : success,
	        records : records,
	        totalRecords : totalRecords
	    };
	}
});
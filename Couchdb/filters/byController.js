function(doc, req) {
	if(doc.type == 'Controller') {
		return true;
	} else {
		return false;
	}
}
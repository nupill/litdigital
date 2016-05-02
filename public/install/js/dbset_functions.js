function input_check() {
	if (document.install.dbhost.value == "") {
		alert("Fill in the blanks");
		return false;
	} else if (document.install.dbname.value == "") {
		alert("Fill in the blanks");
		return false;
	} else if (document.install.dbuser.value == "") {
		alert("Fill in the blanks");
		return false;
	} else {
		return true;
	}
}
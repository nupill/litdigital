function input_check(){
	if (document.install.password.value == ""){
		alert("fill in the blanks");
		return false;
	} else {
		return true;
	}
}
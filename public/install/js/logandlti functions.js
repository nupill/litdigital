function input_check() {
	if (document.install.log_email.value == "") {
		alert("Fill in the blanks");
		return false;
	} else if (document.install.lti_consumer_url.value == "") {
		alert("Fill in the blanks");
		return false;
	}else {
		return true;
	}

}

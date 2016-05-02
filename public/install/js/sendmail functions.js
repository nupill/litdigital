function input_check() {
	if (document.install.smtp_user.value == "") {
		alert("Fill in the blanks");
		return false;
	} else if (document.install.smtp_email.value == "") {
		alert("Fill in the blanks");
		return false;
	} else if (document.install.smtp_password.value == "") {
		alert("Fill in the blanks");
		return false;
	} else if (document.install.smtp_host.value == "") {
		alert("Fill in the blanks");
		return false;
	}else {
		return true;
	}

}
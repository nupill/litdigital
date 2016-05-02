function input_check() {
	if (document.install.ga_profile.value != "") {
		if (document.install.ga_email.value == "") {
			alert("Fill in the blanks");
			return false;
		} else if (document.install.ga_password.value == "") {
			alert("Fill in the blanks");
			return false;
		} else if (document.install.ga_tracker.value == "") {
			alert("Fill in the blanks");
			return false;
		}else {
			return true;
		}
	}

}
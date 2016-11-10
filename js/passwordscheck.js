$(document).ready(function() {
	$('#pass1').keyup(function() {
		$('#result').html(checkStrength($('#pass1').val()))
	})
	function checkStrength(password) {
		var strength = 0
		if (password.length < 6) {
			$('#result').removeClass('weak fa fa-battery-2')
			$('#result').removeClass('good fa fa-battery-3')
			$('#result').removeClass('strong fa fa-battery-4')
			$('#result').addClass('short fa fa-battery-1')// too short
			return ''
		}
		if (password.length > 7) strength += 1
		// If password contains both lower and uppercase characters, increase strength value.
		if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1
		// If it has numbers and characters, increase strength value.
		if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/)) strength += 1
		// If it has one special character, increase strength value.
		if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1
		// If it has two special characters, increase strength value.
		if (password.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1
		// Calculated strength value, we can return messages
		// If value is less than 2
		if (strength < 2) {
			$('#result').removeClass('short fa fa-battery-1')
			$('#result').removeClass('good fa fa-battery-3')
			$('#result').removeClass('strong fa fa-battery-4')
			$('#result').addClass('weak fa fa-battery-2')// weak
			return ''
		} else if (strength == 2) {
			$('#result').removeClass('short fa fa-battery-1')
			$('#result').removeClass('weak fa fa-battery-2')
			$('#result').removeClass('strong fa fa-battery-4')
			$('#result').addClass('good fa fa-battery-3')// good
			return ''
		} else {
			$('#result').removeClass('short fa fa-battery-1')
			$('#result').removeClass('weak fa fa-battery-2')
			$('#result').removeClass('good fa fa-battery-3')
			$('#result').addClass('strong fa fa-battery-4')// strong
			return ''
		}
	}
});

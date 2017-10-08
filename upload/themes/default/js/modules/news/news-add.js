$(function(){
	window.onload = function () {
		tinymce.init({
			selector: "textarea.tinymce",
			language: "ru",
			theme: "modern",
			skin: 'lightgray',
			browser_spellcheck: true,
			plugins: "code preview image imagetools link textcolor wordcount pagebreak",
			menubar: "file edit insert view format table tools",
			toolbar1: "bold italic strikethrough | formatselect fontsizeselect | backcolor forecolor",
			toolbar2: "alignleft aligncenter alignright alignjustify | numlist bullist outdent indent | link image | pagebreak | removeformat",
			pagebreak_separator: "{READMORE}"
		});
	}
});

function check_cpp(checkbox) {
	if ($(checkbox).is(':checked')) {
		$('#date_publish').show();
	} else {
		$('#date_publish').hide();
	}
}

$(document).ready(function () {
	$('.switch').on('click', '[name="planed_publish"]', function () {
		check_cpp(this);
	});

	check_cpp('[name="planed_publish"]');

	$('#input_publish_time').datetimepicker({
		lang:'ru',
		timepicker:true,
		value:'',
		format:'d.m.Y H:i:s'
	});
});

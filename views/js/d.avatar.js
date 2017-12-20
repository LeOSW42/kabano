
$(window).ready(function() {
	$("#deleteavatar").click(function() {
		$("aside").removeClass("avatar").addClass("noavatar");
		$("#deleteavatar").hide();
		$("#avatarcheckbox").prop("checked", false);
		$('#avatarfile').val('');
	});

	$("#uploadavatar").click(function() {
		$('#avatarfile').trigger('click');
	});

	$("#avatarfile").change(function () {
		if($("#avatarfile").val == '') {
			$("#avatarcheckbox").prop("checked", false);
			$("aside").removeClass("avatar").addClass("noavatar");
			$("#deleteavatar").hide();
		}
		else {
			$("#avatarcheckbox").prop("checked", true);
			$("aside").removeClass("noavatar").addClass("avatar");
			$("#deleteavatar").show();
			readURL(this);
		}
	});
});


function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#profileavatar').attr('src', e.target.result);
        }

        reader.readAsDataURL(input.files[0]);
    }
}$
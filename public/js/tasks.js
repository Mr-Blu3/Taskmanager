$(document).ready(function () {
	cleanForm();

	if(currentBoard) {
		loadItems();
	} else {
		$("#new_board_name").focus();
	}


	$("#new_board_name").keyup(function (e) {
		if (e.keyCode === 13)
			$("#new_board_create").trigger("click");
	});

	$(document).keyup(function (e) {
		if (e.keyCode === 27) {
			cleanForm();
			$("#edit").hide();
			$("#header").show();
			$("#new_board_start").show();
			$("#new_board_continue").hide();
		} else if(e.keyCode === 46) {
			if($("#edit #id").val())
				$("#edit #remove").trigger("click");
		} else if(e.altKey && e.keyCode === 78) {
			$("#new_item").trigger("click");
			e.preventDefault();
		}
	});

	$(document).on("click", ".item", function (e) {
		e.stopPropagation();
		loadItem($(this).attr("id"));
		$("#edit").show();
		$("#header").hide();
		$("#edit #title").focus();
	});

	$(document).on("click", ".project .name", function () {
		cleanForm();
		$("#header").hide();
		$("#edit").show();
		$("#cancel").show();
		$("#edit #existing_project, #edit #project").val($(this).closest(".project").attr('data-project'));
		$("#edit #title").focus();
	});

	$(document).on("mouseenter", ".item", function () {
		$(this).css('opacity', '0.9');
		$(this).find(".right_pen").css('opacity', '1');
	});
	$(document).on("mouseleave", ".item", function () {
		$(this).css('opacity', '1');
		$(this).find(".right_pen").css('opacity', '0');
	});

	$("#deadline").datepicker({dateFormat: 'yy-mm-dd'});

	$("#add, #save").click(function () {
		save();
		$("#edit #cancel").show();
	});

	$("input#project, input#title, input#days").keyup(function (e) {
		if (e.keyCode == 13)
			save();
	});

	$("#remove").click(function () {
		if (confirm("Do you still want to remove this item?"))
			removeItem($("#edit #id").val());
		$("#edit #cancel").show();	
	});

	$("#existing_project").change(function () {
		$("#project").val($("#existing_project").val());
	});

	$("#cancel").click(function () {
		$("#header").show();
		$("#edit").hide();
	});

	$("#new_item").click(function () {
		$("#header").hide();
		$("#edit").show();
		$("#cancel").show();
		$("#edit #existing_project").val('');
		$("#edit #title").focus();
	});

	$("#new_board").click(function () {
		$("#new_board_start").hide();
		$("#new_board_continue").show();
		$("#new_board_name").focus();
	});

	$("#new_board_create").click(function () {
		var boardName = $("#new_board_name").val();
		boardName = boardName.replace(/[^A-Za-z0-9\s!?]/g, '');
		if (boardName.length > 1) {
			window.location = '?board=' + newBoardId + '-' + boardName;
		} else {
			$("#new_board_name").addClass('error');
		}

	});

});

function getHeight(days) {
	var height = 20;
	height += days * 10;

	if (height < 20) {
		return 20;
	} else if (height > 400) {
		return 400;
	} else {
		return height;
	}
}

function cleanForm() {
	$("#edit #id").val('');
	$("#edit #project").val('');
	$("#edit #title").val('');
	$("#edit #title, #edit #days").removeClass('error');
	$("#edit #days").val('');
	$("#edit #deadline").val('');
	$("#edit #description").val('');
	$("#edit #save").hide();
	$("#edit #cancel").show();
	$("#edit #remove").hide();
	$("#edit #add").show();
	$("#edit #existing_project").show();
	$("#edit #description").height("30px");
}

function loadItems() {
	apiCall('load-items', {}, function (response) {
		$("#container").html("");
		$("#existing_project").html('<option value="">New</option>');

		var usedResource = 0;
		var projectWidth = (($("#container").width() - 30) * 1) / Object.keys(response).length;
		var isItem = false;
		var htmlContent = '';
		$.each(response, function (project, items) {
			isItem = true;
			var resource = project.search(/\([0-9\.]+\)/i);
			console.log(resource);
			if (resource !== -1)
				usedResource += parseFloat(project.substring(resource).replace('(', '').replace(')', ''));
			$("#existing_project").append('<option value="' + project + '">' + project + '</option>');
			var projectString = '<div style="width: ' + Math.round(projectWidth) + 'px;" class="project" data-project="' + project + '"><div class="name ' + (resource !== -1 ? 'has-resources' : '') + '">' + project.replace('(', '').replace(')', '%') + ' <span class="add">+</span></div><div class="item-list">';
			$.each(items, function (order, item) {
				if (typeof item != 'undefined') {
					projectString += '<div id="' + item._id + '" class="item prio-' + item.prio + '" style="width: 100%;height:' + getHeight(item.days) + 'px"><img class="left_pen" src="#" /><span class="item_title">' + item.title + '</span><img class="right_pen" src="#" /></div>';
					if (item.deadline)
						projectString += '<div class="deadline"><img src="#" />' + item.deadline + '</div>';
				}

			});

			projectString += '</div></div>';
			htmlContent += projectString;
		});

		if (isItem) {
		$("#container").html(htmlContent);
				$(".item-list").sortable({forceHelperSize: true, opacity: 0.7, axis: 'yx', connectWith: '.item-list', stop: function (event, ui) {
						moveItem(ui.item, ui.item.closest('.item-list'), ui.sender);
				}});
		} else {
			$("#container").html($("#welcome_container").html());
		}

	$("#resources").html(usedResource);
	});
}

function loadItem(id) {
	apiCall('load-item', {id: id}, function (item) {
		$("#edit #id").val(item._id);
		$("#edit #existing_project").val(item.project);
		$("#edit #project").val(item.project);
		$("#edit #title").val(item.title);
		$("#edit #prio").val(item.prio);
		$("#edit #days").val(item.days);
		$("#edit #deadline").val(item.deadline);
		$("#edit #description").val(item.description);
		$("#edit #existing_project").hide();
		$("#edit #save").show();
		$("#edit #cancel").show();
		$("#edit #remove").show();
		$("#edit #add").hide();
		$("#edit #title").focus();

		if (item.description && item.description.split("\n").length > 2) {
			$("#edit #description").height("200px");
		} else {
			$("#edit #description").height("30px");
		}
	});
}

function save() {
	var isError = false;
	if (!$("#edit #title").val()) {
		$("#edit #title").addClass('error');
		isError = true;
	} else {
		$("#edit #title").removeClass('error');
	}

	if ($("#edit #days").val() && isNaN($("#edit #days").val())) {
		$("#edit #days").addClass('error');
		isError = true;
	} else {
		$("#edit #days").removeClass('error');
	}

	if (!isError) {
		var item = {
			id: $("#edit #id").val(),
			project: $("#edit #project").val() ? $("#edit #project").val() : 'Other',
			existing_project: $("#edit #existing_project").val() ? $("#edit #existing_project").val() : 'New',
			title: $("#edit #title").val(),
			prio: $("#edit #prio").val(),
			days: $("#edit #days").val() ? $("#edit #days").val() : 1,
			deadline: $("#edit #deadline").val(),
			description: $("#edit #description").val(),
		}

		if (!item.id)
			item.group = 1;

		apiCall('save-item', item, function () {
			cleanForm();
			loadItems();
		});
	}
}

function removeItem(id) {
	apiCall('remove-item', {id: id}, function () {
		cleanForm();
		loadItems();
	});
}

function moveItem(item, toElement, fromElement) {
	var toIds = $(toElement).sortable("toArray");

	if(fromElement)
		var fromIds = (fromElement)? $(fromElement).sortable("toArray") : [];

	apiCall('move-item', {project: $(toElement).closest(".project").attr('data-project'), to_ids: toIds, from_ids: fromIds, id: $(item).attr('id')}, function () {
		loadItems();
	});
}

function apiCall(service, data, callback) {
	data['service'] = service;
	data['board'] = currentBoard;
	$.post('api.php', data, 'json').then(callback);
}

function order_submit(item) {
	const item_data = jQuery(item.target).closest('a');
	let data = jQuery(item_data).data('update');
	let ajax_url = jQuery(item_data).data('ajax-url');
	data = data.split("&");
	let new_data = {};
	data = data.map(data => new_data[data.split("=")[0]] = data.split("=")[1]);
	let post_data = {
		action: jQuery(item_data).data('action'),
		nonce: jQuery(item_data).data('nonce'),
		post_data: new_data
	}
	console.log(post_data);
	jQuery.ajax({
		type: "post",
		dataType: "json",
		url: ajax_url,
		data: post_data,
		success: function(response) {
			console.log(response);
			if (response.success) {
				location.reload();
			}
		},
		error: function(err) {
			console.log(err);
		}
	});
}
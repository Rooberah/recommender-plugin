(function($){
	function get_clicked_product_id(selector){
		var tmp = $(selector).parents().closest('li').attr('class');
		tmp = tmp.substr(tmp.indexOf('post-'));
		tmp = tmp.substr(0, tmp.indexOf(" "));
		tmp = tmp.substr(tmp.indexOf("-") + 1);
		return tmp;
	}

	function send_click_event(url, site_name, user_id, item_id, product_id=null){
		xmlhttp=new XMLHttpRequest();
		xmlhttp.open("POST", url , false);
		xmlhttp.setRequestHeader("Content-type", "application/json");
		var data = {
			'site_name': site_name,
			'user_id': user_id,
			'item_id': item_id
		}
		if(product_id != null)
			data['product_id'] = product_id

		xmlhttp.send(JSON.stringify(data));
	}

	$(document).ready(function(){
		$('.recommender-block-class a').click(function(e){
			clicked_product_id = get_clicked_product_id(this);
			send_click_event(
				recommender_info['click_event_url'],
				recommender_info['site_name'],
				recommender_info['user_id'],
				clicked_product_id
			)
			
		});
		if(recommender_info['is_product']){
			$('.related.products a').click(function(e){
				clicked_product_id = get_clicked_product_id($(this));
				send_click_event(
					recommender_info['click_event_url'],
					recommender_info['site_name'],
					recommender_info['user_id'],
					clicked_product_id,
					recommender_info['product_id']
				)
			});
		}
	});
}( jQuery3_1_1 ) );
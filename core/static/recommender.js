(function($){
	function get_clicked_product_id(selector){
		var tmp = $(selector).parents().closest('li').attr('class');
		tmp = tmp.substr(tmp.indexOf('post-'));
		tmp = tmp.substr(0, tmp.indexOf(" "));
		tmp = tmp.substr(tmp.indexOf("-") + 1);
		return tmp;
	}

	function send_click_event(url, site_name, user_id, item_id, product_id=null, interaction_id, jwt, a_id){
		xmlhttp=new XMLHttpRequest();
		xmlhttp.open("POST", url , false);
		xmlhttp.setRequestHeader("Content-type", "application/json");
		xmlhttp.setRequestHeader("Authorization", `JWT ${jwt}`);
        var date = new Date().toISOString();
        console.log(date);

        var data = {
			'site_name': site_name,
			'user_id': user_id,
			'item_id': item_id,
			'interaction_id': interaction_id,
			'interaction_value': 1,
			'interaction_time': date,
			'interaction_type': "click_on_recommended",
			'anonymous_id': a_id
		};
		if(product_id != null)
			data['related_to_item_id'] = product_id;

		xmlhttp.send(JSON.stringify(data));
	}

	$(document).ready(function(){
		$('.recommender-block-class a').click(function(e){
			clicked_product_id = get_clicked_product_id(this);
			send_click_event(
				recommender_info['interaction_url'],
				recommender_info['site_name'],
				recommender_info['user_id'],
				clicked_product_id,
				null,
				recommender_info['jwt_pool'][i]['interaction_id'],
				recommender_info['jwt_pool'][i]['jwt'],
				recommender_info['anonymous_id']
			);
			i++;

		});
		if(recommender_info['is_product']){
			$('.' + 
			  recommender_info['related_products_section_class'].split(' ').join('.') + 
			  ' a').click(function(e){
				clicked_product_id = get_clicked_product_id($(this));
				send_click_event(
					recommender_info['interaction_url'],
					recommender_info['site_name'],
					recommender_info['user_id'],
					clicked_product_id,
					recommender_info['product_id'],
					recommender_info['jwt_pool'][i]['interaction_id'],
					recommender_info['jwt_pool'][i]['jwt'],
					recommender_info['anonymous_id']
				);
				i++;
			});
		}
	});
}( jQuery3_1_1 ) );

let i = 0;
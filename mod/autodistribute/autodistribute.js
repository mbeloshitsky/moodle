$(function () {
	var QueryString = function () {
  		// This function is anonymous, is executed immediately and 
  		// the return value is assigned to QueryString!
  		var query_string = {};
  		var query = window.location.search.substring(1);
  		var vars = query.split("&");
  		for (var i=0;i<vars.length;i++) {
    		var pair = vars[i].split("=");
    		// If first entry with this name
    		if (typeof query_string[pair[0]] === "undefined") {
      			query_string[pair[0]] = pair[1];
    		// If second entry with this name
    		} else if (typeof query_string[pair[0]] === "string") {
      			var arr = [ query_string[pair[0]], pair[1] ];
      			query_string[pair[0]] = arr;
    		// If third or later entry with this name
    		} else {
      			query_string[pair[0]].push(pair[1]);
    		}
  		} 
    	return query_string;
	} ();

	function moveStudent(ev, dir) {
		var targetSpan 		= $(ev.target).parent()
		var student_id 		= targetSpan.attr('id')
		var group_div 		= $(ev.target).parents('div[group_id]')
		var from_group_id   = group_div.attr('group_id')
		var targetDiv 		= (dir == 'up' ? 
								group_div.prev() : 
							   dir == 'down' ? group_div.next() : 
							    group_div).first() 
		var to_group_id 	= targetDiv.attr('group_id')
		if (!to_group_id)
			return;
		targetSpan.css('color','grey');
		$.getJSON('/mod/autodistribute/ajax.php',
			{id: QueryString.id, action:'movestudent', student:student_id, from:from_group_id, to:to_group_id},
			function (out) {
				if (out.output == 'ok') {
					targetSpan.appendTo(targetDiv);
				}
				targetSpan.css('color','black');
			});
	}

	$('.moveup').click(function (ev) { moveStudent(ev, 'up') })
	$('.movedown').click(function (ev) { moveStudent(ev, 'down') })
})
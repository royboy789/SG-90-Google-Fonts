var $ = jQuery;

$(document).ready(function(){
	if( $('input.sg_Gfont').length > 0 && sgGfonts.gApi.length > 1 ) {
		
		var table = $('table.fontWrapper');
		$.each( table, function( i, v ) {
			$(this).find('tr:first').find('a.deleteFont').hide();
		})
		
		var autoCopts = {
			source: function( request, response ) {
				$.get('https://www.googleapis.com/webfonts/v1/webfonts?key=' + sgGfonts.gApi + '&css=family=a' , function(res){
					if( res.items ){
						var items = res.items,
						results = [];
						$.each(items, function(i, v){
							if( v.family.toLowerCase().indexOf( request.term.toLowerCase() ) >= 0 ) {
								results.push( v );
							}
						});
						response( $.map(results, function(item){
							return {
								label: item.family,
								variants: item.variants
							}	
						}));
					}
				}).fail(function( res, text ) {
					alert( 'Permission Error: API Key or Domain not allowed. Check Google Console' );
				});
			},
			create: function() {
				$('.ui-autocomplete').css({ 'height' : '300px', 'overflow' : 'scroll' });
			},
			select: function( event, ui ) {
				var title = $(event.target).data('title'),
				options = [];
				$.each( ui.item.variants, function( i, v ) {
					options.push('<option value="' + v + '">' + v + '</option>');
				})
				if( $( event.target ).parent('td').siblings('.variant') ) {
					$( event.target ).parent('td').siblings('.variant').remove();
				}
				$( event.target ).parent('td').after('<td class="variant"><select style="width:200px" name="_sg_' + title + '_gFont[variant][]" >' + options + '</select></td>');
			}
		};
		
		$('input.sg_Gfont').autocomplete(autoCopts);
		
		$('body').on('click', '.addFont', function(e) {
			e.preventDefault();
			var table = $(this).siblings('table.fontWrapper');
			table.find('tr:first').clone().appendTo(table);
			table.find('tr:last').find('a.deleteFont').show();
			table.find('tr:last').find('input').val('');
			table.find('tr:last').find('td.variant').remove();
			$('input.sg_Gfont').autocomplete(autoCopts);
		});
		
		$('body').on('click', '.deleteFont', function(e) {
			e.preventDefault();
			$(this).parents('tr').remove();	
		});
	}
	
});
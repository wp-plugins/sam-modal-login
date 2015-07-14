jQuery( document ).ready(function( $ ) {

	/**
	 * Emulate 2 labels fields by splitting the default one for #sam_modal_link
	 * and hide url for #sam_modal_link and #sam_modal_link_register
	 */
	$( '#update-nav-menu' ).bind( 'click', function( e ) {
		if ( e.target && e.target.className && -1 != e.target.className.indexOf( 'item-edit' ) ) {
			$( "input[value='#pa_modal_login'][type=text]" ).parent().parent().parent().each( function(){
				var $this = $( this );
				var item_id = $this.attr('id').substring( 'menu-item-settings-'.length );
				var $url = $( '#edit-menu-item-url-' + item_id );
				var $title = $( '#edit-menu-item-title-' + item_id );
				var helper_tpl = '<p class="description description-thin"><label for="sam-helper-{item_id}-{item_part}">{item_label}<br><input id="sam-helper-{item_id}-{item_part}" class="widefat" type="text"></label></p>';

				// Hide unwanted fields
				$url.parent().parent().hide();
				$title.parent().parent().hide();

				// Remove helpers added previously
				$( 'input[id^="sam-helper-"]' ).parent().parent().remove();

				// Split the Label to two part, login and logout labels
				$this.prepend( $ ( helper_tpl.replaceArray( ['{item_id}', '{item_part}', '{item_label}'] , [ item_id, '2', sam_strings.label_logout ] ) ) );
				$this.prepend( $ ( helper_tpl.replaceArray( ['{item_id}', '{item_part}', '{item_label}'] , [ item_id, '1', sam_strings.label_login ] ) ) );

				// Populate Labels
				var val_1 = $title.val().substr( 0, $title.val().indexOf(' // ' ) );
				var val_2 = $title.val().substr( - $title.val().length + ' // '.length + val_1.length );
				$( '#sam-helper-' + item_id + '-1' ).val( val_1 );
				$( '#sam-helper-' + item_id + '-2' ).val( val_2 );

				// Bind on-change handlers that update the hidden label field
				$( 'input[id^="sam-helper-"]', $this ).keyup( function() {
					$title.val ( 
						$( '#sam-helper-' + item_id + '-1' ).val() 
						+ ' // ' +
						$( '#sam-helper-' + item_id + '-2' ).val()
					);
				});
			});
			$( "input[value='#pa_modal_register'][type=text]" ).parent().parent().parent().each( function(){
				var $this = $( this );
				var item_id = $this.attr('id').substring( 'menu-item-settings-'.length );
				var $url = $( '#edit-menu-item-url-' + item_id );

				// Hide unwanted fields
				$url.parent().parent().hide();
			});
		}
	});
});

/**
 * Find/Replace with arrays as parameters, see http://stackoverflow.com/a/5069776/358906
 */
String.prototype.replaceArray = function(find, replace) {
	var replaceString = this;
	var regex; 
	for (var i = 0; i < find.length; i++) {
		regex = new RegExp(find[i], "g");
		replaceString = replaceString.replace(regex, replace[i]);
	}
	return replaceString;
};
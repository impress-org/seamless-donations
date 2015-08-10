function DgxDonateOnCountryChange( event ) {
	var countrySelectEl = jQuery( this );

	var stateSelectEl = countrySelectEl.parents( '.dgx_donate_geography_selects' ).first().find( '.dgx_donate_state_select' ).parents( 'p' ).first();
	var provinceSelectEl = countrySelectEl.parents( '.dgx_donate_geography_selects' ).first().find( '.dgx_donate_province_select' ).parents( 'p' ).first();
	var postalCodeEl = countrySelectEl.parents( '.dgx_donate_geography_selects' ).first().find( '.dgx_donate_zip_input' ).parents( 'p' ).first();
	var ukGiftAidEl = countrySelectEl.parents( '.dgx_donate_geography_selects' ).first().find( '.dgx_donate_uk_gift_aid' ).parents( 'p' ).first();

	var country = countrySelectEl.val();
	if ( 'US' == country ) {
		stateSelectEl.show();
		provinceSelectEl.hide();
	} else if ( 'CA' == country ) {
		stateSelectEl.hide();
		provinceSelectEl.show();
	} else {
		stateSelectEl.hide();
		provinceSelectEl.hide();
	}

	if ( 'GB' == country ) {
		ukGiftAidEl.show();
	} else {
		ukGiftAidEl.hide();
	}

	if ( 'undefined' != typeof dgxDonateAjax ) {
		if ( -1 == dgxDonateAjax.postalCodeRequired.indexOf( country ) ) {
			postalCodeEl.hide();
		} else {
			postalCodeEl.show();
		}
	}
}

jQuery( document ).ready( function() {
	jQuery( '.dgx_donate_country_select' ).change( DgxDonateOnCountryChange );
	jQuery( '.dgx_donate_country_select' ).trigger( 'change' );
} );
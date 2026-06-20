/**
 * اسکریپت پنل مدیریت افزونه نفس.
 */
( function ( $ ) {
	'use strict';

	$( function () {

		/* ---------- تب‌ها ---------- */
		var $tabs   = $( '.nafas-tab' );
		var $panels = $( '.nafas-tab-panel' );

		function activateTab( hash ) {
			var $target = $( hash );
			if ( ! $target.length ) { return; }
			$tabs.removeClass( 'is-active' );
			$panels.removeClass( 'is-active' );
			$tabs.filter( '[href="' + hash + '"]' ).addClass( 'is-active' );
			$target.addClass( 'is-active' );
			if ( window.history && window.history.replaceState ) {
				window.history.replaceState( null, '', hash );
			}
		}

		$tabs.on( 'click', function ( e ) {
			e.preventDefault();
			activateTab( $( this ).attr( 'href' ) );
		} );

		if ( window.location.hash && $( window.location.hash ).hasClass( 'nafas-tab-panel' ) ) {
			activateTab( window.location.hash );
		}

		/* ---------- انتخاب رنگ ---------- */
		if ( $.fn.wpColorPicker ) {
			$( '.nafas-color-picker' ).wpColorPicker();
		}

		/* ---------- نمایش شرطی فیلدهای AI ---------- */
		function toggleAiFields() {
			var v = $( '#ai_provider' ).val();
			$( '.nafas-ai-gemini' ).toggle( v === 'gemini' );
			$( '.nafas-ai-openai' ).toggle( v === 'openai' );
			$( '.nafas-ai-claude' ).toggle( v === 'claude' );
			$( '.nafas-ai-custom' ).toggle( v === 'custom' );
			$( '.nafas-ai-webhook' ).toggle( v === 'webhook' );
			// فیلدهای مشترک (دستورالعمل سیستمی و حافظه) برای همه موتورهای واقعی AI.
			$( '.nafas-ai-shared' ).toggle( v === 'gemini' || v === 'openai' || v === 'claude' || v === 'custom' );
		}
		$( '#ai_provider' ).on( 'change', toggleAiFields );
		toggleAiFields();

		/* ---------- نمایش شرطی فیلدهای محدودیت استفاده ---------- */
		function toggleRateFields() {
			var m = $( '#rate_limit_mode' ).val();
			$( '.nafas-rl-ip' ).toggle( m === 'ip' || m === 'both' );
			$( '.nafas-rl-session' ).toggle( m === 'session' || m === 'both' );
		}
		$( '#rate_limit_mode' ).on( 'change', toggleRateFields );
		toggleRateFields();

		/* ---------- مدیریت محصولات (افزودن/حذف ردیف) ---------- */
		$( '#nafas-add-product' ).on( 'click', function () {
			var $tbody = $( '#nafas-products tbody' );
			var row =
				'<tr class="nafas-product-row">' +
					'<td><input type="text" name="product_id[]" value="" dir="ltr" class="widefat"></td>' +
					'<td><input type="text" name="product_name[]" value="" class="widefat"></td>' +
					'<td><textarea name="product_knowledge[]" rows="2" class="widefat"></textarea></td>' +
					'<td><input type="url" name="product_image[]" value="" dir="ltr" class="widefat" placeholder="https://..."></td>' +
					'<td><input type="url" name="product_brochure[]" value="" dir="ltr" class="widefat" placeholder="https://..."></td>' +
					'<td><button type="button" class="button nafas-remove-product">&times;</button></td>' +
				'</tr>';
			$tbody.append( row );
		} );

		$( '#nafas-products' ).on( 'click', '.nafas-remove-product', function () {
			$( this ).closest( 'tr' ).remove();
		} );

		/* ---------- تغییر وضعیت سریع درخواست ---------- */
		$( '.nafas-status-select' ).on( 'change', function () {
			var url = $( this ).data( 'url' );
			var status = $( this ).val();
			if ( url ) {
				window.location.href = url + '&status=' + encodeURIComponent( status );
			}
		} );

		/* ---------- نمایش/مخفی جزئیات درخواست ---------- */
		$( '.nafas-submissions' ).on( 'click', '.nafas-detail-toggle', function () {
			var $detail = $( this ).closest( 'tr' ).next( '.nafas-detail-row' );
			$detail.prop( 'hidden', ! $detail.prop( 'hidden' ) );
			$( this ).toggleClass( 'is-active' );
		} );

		/* ---------- افزودن/حذف ردیف پاسخ پیشنهادی ---------- */
		$( '#nafas-add-quick' ).on( 'click', function () {
			var row =
				'<tr class="nafas-quick-row">' +
					'<td><input type="text" name="quick_reply_label[]" value="" class="widefat" placeholder="برچسب دکمه"></td>' +
					'<td><input type="text" name="quick_reply_question[]" value="" class="widefat" placeholder="متن سوالی که ارسال می‌شود"></td>' +
					'<td><button type="button" class="button nafas-remove-quick">&times;</button></td>' +
				'</tr>';
			$( '#nafas-quick-replies tbody' ).append( row );
		} );
		$( '#nafas-quick-replies' ).on( 'click', '.nafas-remove-quick', function () {
			$( this ).closest( 'tr' ).remove();
		} );

		/* ---------- افزودن/حذف ردیف بانک پاسخ ---------- */
		$( '#nafas-add-qa' ).on( 'click', function () {
			var tpl = document.getElementById( 'nafas-qa-template' );
			if ( tpl && tpl.content ) {
				$( '#nafas-qa-table tbody' ).append( document.importNode( tpl.content, true ) );
			}
		} );
		$( '#nafas-qa-table' ).on( 'click', '.nafas-remove-qa', function () {
			$( this ).closest( 'tr' ).remove();
		} );

		/* ---------- تست اتصال هوش مصنوعی ---------- */
		$( '#nafas-test-ai' ).on( 'click', function () {
			var $btn = $( this );
			var $res = $( '#nafas-test-ai-result' );
			if ( typeof NafasAdmin === 'undefined' ) { return; }
			$btn.prop( 'disabled', true );
			$res.removeClass( 'is-ok is-err' ).text( 'در حال بررسی...' );

			$.post( NafasAdmin.ajaxUrl, {
				action: 'nafas_chatbot_test_ai',
				nonce: NafasAdmin.nonce
			} ).done( function ( r ) {
				if ( r && r.success ) {
					$res.addClass( 'is-ok' ).text( '✅ ' + ( r.data.message || 'موفق' ) + ( r.data.reply ? ' — «' + r.data.reply + '»' : '' ) );
				} else {
					$res.addClass( 'is-err' ).text( '❌ ' + ( ( r && r.data && r.data.message ) || 'خطای نامشخص' ) );
				}
			} ).fail( function () {
				$res.addClass( 'is-err' ).text( '❌ خطا در ارتباط با سرور وردپرس.' );
			} ).always( function () {
				$btn.prop( 'disabled', false );
			} );
		} );
	} );

} )( jQuery );

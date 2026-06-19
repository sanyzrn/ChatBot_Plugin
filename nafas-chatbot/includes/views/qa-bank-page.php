<?php
/**
 * نمای صفحه بانک پاسخ‌ها.
 *
 * @package NafasChatbot
 * @var array  $s            تنظیمات.
 * @var array  $products_map نگاشت محصولات.
 * @var string $sample_url   آدرس فایل نمونه.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bank = isset( $bank ) ? (array) $bank : array();

/**
 * چاپ گزینه‌های انتخاب محصول.
 *
 * @param string $selected مقدار انتخاب‌شده.
 * @param array  $map      نگاشت محصولات.
 */
$render_product_options = function ( $selected, $map ) {
	echo '<option value="general"' . selected( $selected, 'general', false ) . '>' . esc_html__( 'عمومی / شرکت', 'nafas-chatbot' ) . '</option>';
	foreach ( $map as $pid => $pname ) {
		echo '<option value="' . esc_attr( $pid ) . '"' . selected( $selected, $pid, false ) . '>' . esc_html( $pname ) . '</option>';
	}
};
?>
<div class="wrap nafas-admin" dir="rtl">
	<h1 class="nafas-admin__title">
		<span class="dashicons dashicons-database"></span>
		<?php esc_html_e( 'بانک پاسخ‌های آماده', 'nafas-chatbot' ); ?>
	</h1>

	<p class="description" style="max-width:820px;font-size:13px">
		<?php esc_html_e( 'بانک سوال/جواب آفلاین. وقتی هوش مصنوعی در دسترس نباشد (قطعی، تحریم، تایم‌اوت)، چت‌بات از این بانک پاسخ می‌دهد. تطبیق بر اساس کلمات سوال کاربر با «سوال» و «کلیدواژه‌ها» انجام می‌شود.', 'nafas-chatbot' ); ?>
	</p>

	<form method="post" action="" enctype="multipart/form-data" class="nafas-settings-form">
		<?php wp_nonce_field( 'nafas_chatbot_qa' ); ?>

		<table class="form-table">
			<tr>
				<th><label for="qa_mode"><?php esc_html_e( 'ترتیب پاسخ‌گویی', 'nafas-chatbot' ); ?></label></th>
				<td>
					<select name="qa_mode" id="qa_mode">
						<option value="ai_first" <?php selected( $s['qa_mode'], 'ai_first' ); ?>><?php esc_html_e( 'اول هوش مصنوعی، سپس بانک (پیشنهادی)', 'nafas-chatbot' ); ?></option>
						<option value="bank_first" <?php selected( $s['qa_mode'], 'bank_first' ); ?>><?php esc_html_e( 'اول بانک، سپس هوش مصنوعی', 'nafas-chatbot' ); ?></option>
						<option value="bank_only" <?php selected( $s['qa_mode'], 'bank_only' ); ?>><?php esc_html_e( 'فقط بانک (بدون هوش مصنوعی)', 'nafas-chatbot' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'حالت پیش‌فرض: ابتدا هوش مصنوعی بررسی می‌شود؛ اگر در دسترس نبود، بانک پاسخ می‌دهد.', 'nafas-chatbot' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'ذخیره گفتگوها', 'nafas-chatbot' ); ?></th>
				<td>
					<label class="nafas-switch"><input type="checkbox" name="chatlog_enabled" value="yes" <?php checked( $s['chatlog_enabled'], 'yes' ); ?>><span class="nafas-switch__slider"></span></label>
					<p class="description"><?php esc_html_e( 'پاسخ‌های هوش مصنوعی و بانک در «تاریخچه گفتگو» ذخیره می‌شوند تا بتوانید موارد مناسب را به این بانک اضافه کنید.', 'nafas-chatbot' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="chatlog_retention_days"><?php esc_html_e( 'نگهداری تاریخچه (روز)', 'nafas-chatbot' ); ?></label></th>
				<td>
					<input type="number" id="chatlog_retention_days" name="chatlog_retention_days" value="<?php echo esc_attr( $s['chatlog_retention_days'] ); ?>" min="0" max="3650" class="small-text">
					<p class="description"><?php esc_html_e( 'تاریخچه قدیمی‌تر از این تعداد روز به‌صورت خودکار (روزانه) پاک می‌شود. مقدار ۰ = بدون پاک‌سازی.', 'nafas-chatbot' ); ?></p>
				</td>
			</tr>
		</table>

		<h3 class="nafas-section"><?php esc_html_e( 'سوال و جواب‌ها', 'nafas-chatbot' ); ?> <span class="nafas-count">(<?php echo esc_html( number_format_i18n( count( $bank ) ) ); ?>)</span></h3>

		<table class="nafas-products-table widefat" id="nafas-qa-table">
			<thead>
				<tr>
					<th style="width:150px"><?php esc_html_e( 'محصول', 'nafas-chatbot' ); ?></th>
					<th style="width:220px"><?php esc_html_e( 'سوال', 'nafas-chatbot' ); ?></th>
					<th style="width:170px"><?php esc_html_e( 'کلیدواژه‌ها (با | جدا کنید)', 'nafas-chatbot' ); ?></th>
					<th><?php esc_html_e( 'پاسخ', 'nafas-chatbot' ); ?></th>
					<th style="width:40px"></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $bank ) ) : ?>
					<tr class="nafas-qa-row">
						<td><select name="qa_product[]" class="widefat"><?php $render_product_options( 'general', $products_map ); ?></select></td>
						<td><textarea name="qa_question[]" rows="2" class="widefat"></textarea></td>
						<td><textarea name="qa_keywords[]" rows="2" class="widefat"></textarea></td>
						<td><textarea name="qa_answer[]" rows="2" class="widefat"></textarea></td>
						<td><button type="button" class="button nafas-remove-qa">&times;</button></td>
					</tr>
				<?php else : ?>
					<?php foreach ( $bank as $row ) : ?>
						<tr class="nafas-qa-row">
							<td><select name="qa_product[]" class="widefat"><?php $render_product_options( isset( $row['product_id'] ) ? $row['product_id'] : 'general', $products_map ); ?></select></td>
							<td><textarea name="qa_question[]" rows="2" class="widefat"><?php echo esc_textarea( isset( $row['question'] ) ? $row['question'] : '' ); ?></textarea></td>
							<td><textarea name="qa_keywords[]" rows="2" class="widefat"><?php echo esc_textarea( isset( $row['keywords'] ) ? $row['keywords'] : '' ); ?></textarea></td>
							<td><textarea name="qa_answer[]" rows="2" class="widefat"><?php echo esc_textarea( isset( $row['answer'] ) ? $row['answer'] : '' ); ?></textarea></td>
							<td><button type="button" class="button nafas-remove-qa">&times;</button></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		<p><button type="button" class="button button-secondary" id="nafas-add-qa"><?php esc_html_e( '+ افزودن سوال/جواب', 'nafas-chatbot' ); ?></button></p>

		<!-- قالب ردیف خالی برای JS -->
		<template id="nafas-qa-template">
			<tr class="nafas-qa-row">
				<td><select name="qa_product[]" class="widefat"><?php $render_product_options( 'general', $products_map ); ?></select></td>
				<td><textarea name="qa_question[]" rows="2" class="widefat"></textarea></td>
				<td><textarea name="qa_keywords[]" rows="2" class="widefat"></textarea></td>
				<td><textarea name="qa_answer[]" rows="2" class="widefat"></textarea></td>
				<td><button type="button" class="button nafas-remove-qa">&times;</button></td>
			</tr>
		</template>

		<h3 class="nafas-section" style="margin-top:24px"><?php esc_html_e( 'ورود گروهی از فایل (CSV یا JSON)', 'nafas-chatbot' ); ?></h3>
		<table class="form-table">
			<tr>
				<th><label for="qa_import"><?php esc_html_e( 'فایل', 'nafas-chatbot' ); ?></label></th>
				<td>
					<input type="file" id="qa_import" name="qa_import" accept=".csv,.json,text/csv,application/json">
					<p class="description">
						<?php esc_html_e( 'ستون‌های CSV: product,question,keywords,answer — کلیدواژه‌ها با | جدا شوند.', 'nafas-chatbot' ); ?>
						<a href="<?php echo esc_url( $sample_url ); ?>" download><?php esc_html_e( 'دانلود فایل نمونه', 'nafas-chatbot' ); ?></a>
					</p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'نحوه ورود', 'nafas-chatbot' ); ?></th>
				<td>
					<label><input type="radio" name="import_mode" value="append" checked> <?php esc_html_e( 'افزودن به ردیف‌های موجود', 'nafas-chatbot' ); ?></label><br>
					<label><input type="radio" name="import_mode" value="replace"> <?php esc_html_e( 'جایگزینی کامل', 'nafas-chatbot' ); ?></label>
				</td>
			</tr>
		</table>

		<p class="submit">
			<button type="submit" name="nafas_chatbot_save_qa" class="button button-primary button-hero"><?php esc_html_e( 'ذخیره بانک پاسخ‌ها', 'nafas-chatbot' ); ?></button>
		</p>
	</form>
</div>

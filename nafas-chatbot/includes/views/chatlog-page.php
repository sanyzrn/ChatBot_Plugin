<?php
/**
 * نمای صفحه تاریخچه گفتگو.
 *
 * @package NafasChatbot
 * @var array  $result       نتیجه واکشی.
 * @var array  $products_map نگاشت محصولات.
 * @var string $source       فیلتر منبع.
 * @var string $search       جستجو.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base_url = admin_url( 'admin.php?page=nafas-chatbot-chatlog' );

$source_labels = array(
	'ai'         => __( 'هوش مصنوعی', 'nafas-chatbot' ),
	'bank'       => __( 'بانک', 'nafas-chatbot' ),
	'unanswered' => __( 'بی‌پاسخ', 'nafas-chatbot' ),
);
?>
<div class="wrap nafas-admin" dir="rtl">
	<h1 class="nafas-admin__title">
		<span class="dashicons dashicons-format-chat"></span>
		<?php esc_html_e( 'تاریخچه گفتگو', 'nafas-chatbot' ); ?>
	</h1>

	<?php if ( isset( $_GET['added'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'به بانک پاسخ‌ها اضافه شد. می‌توانید در صفحه «بانک پاسخ‌ها» ویرایشش کنید.', 'nafas-chatbot' ); ?></p></div>
	<?php endif; ?>

	<p class="description" style="max-width:820px;font-size:13px">
		<?php esc_html_e( 'سوال‌ها و پاسخ‌های ثبت‌شده. موارد مفید را با دکمه «افزودن به بانک» به بانک پاسخ‌ها منتقل کنید تا حتی بدون هوش مصنوعی هم در دسترس باشند.', 'nafas-chatbot' ); ?>
	</p>

	<form method="get" class="nafas-filters">
		<input type="hidden" name="page" value="nafas-chatbot-chatlog">
		<select name="source">
			<option value=""><?php esc_html_e( 'همه منابع', 'nafas-chatbot' ); ?></option>
			<option value="ai" <?php selected( $source, 'ai' ); ?>><?php esc_html_e( 'هوش مصنوعی', 'nafas-chatbot' ); ?></option>
			<option value="bank" <?php selected( $source, 'bank' ); ?>><?php esc_html_e( 'بانک', 'nafas-chatbot' ); ?></option>
			<option value="unanswered" <?php selected( $source, 'unanswered' ); ?>><?php esc_html_e( 'بی‌پاسخ (نیازمند بانک)', 'nafas-chatbot' ); ?></option>
		</select>
		<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'جستجو در سوال یا پاسخ...', 'nafas-chatbot' ); ?>">
		<button type="submit" class="button"><?php esc_html_e( 'فیلتر', 'nafas-chatbot' ); ?></button>
	</form>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th style="width:90px"><?php esc_html_e( 'محصول', 'nafas-chatbot' ); ?></th>
				<th style="width:25%"><?php esc_html_e( 'سوال', 'nafas-chatbot' ); ?></th>
				<th><?php esc_html_e( 'پاسخ', 'nafas-chatbot' ); ?></th>
				<th style="width:90px"><?php esc_html_e( 'منبع', 'nafas-chatbot' ); ?></th>
				<th style="width:130px"><?php esc_html_e( 'تاریخ', 'nafas-chatbot' ); ?></th>
				<th style="width:130px"><?php esc_html_e( 'عملیات', 'nafas-chatbot' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $result['items'] ) ) : ?>
				<tr><td colspan="6" class="nafas-empty"><?php esc_html_e( 'هنوز گفتگویی ثبت نشده است.', 'nafas-chatbot' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $result['items'] as $row ) : ?>
					<?php
					$pname    = isset( $products_map[ $row->product ] ) ? $products_map[ $row->product ] : $row->product;
					$tobank   = wp_nonce_url( add_query_arg( array( 'nafas_action' => 'tobank', 'cid' => $row->id ), $base_url ), 'nafas_chatlog_action' );
					$dellog   = wp_nonce_url( add_query_arg( array( 'nafas_action' => 'dellog', 'cid' => $row->id ), $base_url ), 'nafas_chatlog_action' );
					?>
					<tr>
						<td><?php echo esc_html( $pname ? $pname : '—' ); ?></td>
						<td><?php echo esc_html( $row->question ); ?></td>
						<td class="nafas-desc"><?php echo esc_html( wp_trim_words( $row->answer, 30 ) ); ?></td>
						<td><span class="nafas-badge <?php echo 'ai' === $row->source ? 'nafas-badge--purple' : 'nafas-badge--red'; ?>"><?php echo esc_html( isset( $source_labels[ $row->source ] ) ? $source_labels[ $row->source ] : $row->source ); ?></span></td>
						<td><?php echo esc_html( $row->created_at ); ?></td>
						<td>
							<?php if ( $row->in_bank ) : ?>
								<span class="nafas-inbank">✓ <?php esc_html_e( 'در بانک', 'nafas-chatbot' ); ?></span>
							<?php else : ?>
								<a href="<?php echo esc_url( $tobank ); ?>" class="button button-small"><?php esc_html_e( '➕ افزودن به بانک', 'nafas-chatbot' ); ?></a>
							<?php endif; ?>
							<a href="<?php echo esc_url( $dellog ); ?>" class="nafas-del-link" title="<?php esc_attr_e( 'حذف', 'nafas-chatbot' ); ?>" onclick="return confirm('<?php esc_attr_e( 'حذف این مورد؟', 'nafas-chatbot' ); ?>');"><span class="dashicons dashicons-trash"></span></a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>

	<?php if ( ! empty( $result['items'] ) ) : ?>
		<form method="post" style="margin-top:14px" onsubmit="return confirm('<?php esc_attr_e( 'کل تاریخچه گفتگو پاک شود؟', 'nafas-chatbot' ); ?>');">
			<?php wp_nonce_field( 'nafas_chatlog_clear' ); ?>
			<button type="submit" name="nafas_chatbot_clear_log" class="button button-secondary"><?php esc_html_e( 'پاک‌سازی کل تاریخچه', 'nafas-chatbot' ); ?></button>
		</form>
	<?php endif; ?>

	<?php if ( $result['total_pages'] > 1 ) : ?>
		<div class="nafas-pagination tablenav">
			<div class="tablenav-pages">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'base'      => add_query_arg( 'paged', '%#%' ),
							'format'    => '',
							'current'   => $result['page'],
							'total'     => $result['total_pages'],
							'prev_text' => '‹',
							'next_text' => '›',
						)
					)
				);
				?>
			</div>
		</div>
	<?php endif; ?>
</div>

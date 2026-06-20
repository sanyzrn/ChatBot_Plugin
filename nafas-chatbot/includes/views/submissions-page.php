<?php
/**
 * نمای صفحه مدیریت درخواست‌ها.
 *
 * @package NafasChatbot
 * @var array $result نتیجه واکشی.
 * @var array $counts شمارش‌ها.
 * @var string $type   فیلتر نوع.
 * @var string $status فیلتر وضعیت.
 * @var string $search جستجو.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$status_labels = array(
	'new'         => __( 'جدید', 'nafas-chatbot' ),
	'in_progress' => __( 'در حال پیگیری', 'nafas-chatbot' ),
	'done'        => __( 'انجام شده', 'nafas-chatbot' ),
	'archived'    => __( 'بایگانی', 'nafas-chatbot' ),
);

$base_url = admin_url( 'admin.php?page=nafas-chatbot-submissions' );
?>
<div class="wrap nafas-admin" dir="rtl">
	<h1 class="nafas-admin__title">
		<span class="dashicons dashicons-list-view"></span>
		<?php esc_html_e( 'درخواست‌های دریافتی', 'nafas-chatbot' ); ?>
	</h1>
	<p class="description"><?php esc_html_e( 'مدیریت درخواست‌های ثبت‌شده از طریق چت‌بات (گزارش عوارض دارویی و درخواست مشاوره).', 'nafas-chatbot' ); ?></p>

	<div class="nafas-stats">
		<div class="nafas-stat-card">
			<span class="nafas-stat-card__num"><?php echo esc_html( number_format_i18n( $counts['total'] ) ); ?></span>
			<span class="nafas-stat-card__label"><?php esc_html_e( 'کل درخواست‌ها', 'nafas-chatbot' ); ?></span>
		</div>
		<div class="nafas-stat-card nafas-stat-card--red">
			<span class="nafas-stat-card__num"><?php echo esc_html( number_format_i18n( isset( $counts['گزارش عوارض دارویی'] ) ? $counts['گزارش عوارض دارویی'] : 0 ) ); ?></span>
			<span class="nafas-stat-card__label"><?php esc_html_e( 'گزارش عوارض', 'nafas-chatbot' ); ?></span>
		</div>
		<div class="nafas-stat-card nafas-stat-card--purple">
			<span class="nafas-stat-card__num"><?php echo esc_html( number_format_i18n( isset( $counts['درخواست مشاوره'] ) ? $counts['درخواست مشاوره'] : 0 ) ); ?></span>
			<span class="nafas-stat-card__label"><?php esc_html_e( 'درخواست مشاوره', 'nafas-chatbot' ); ?></span>
		</div>
	</div>

	<form method="get" class="nafas-filters">
		<input type="hidden" name="page" value="nafas-chatbot-submissions">
		<select name="type">
			<option value=""><?php esc_html_e( 'همه انواع', 'nafas-chatbot' ); ?></option>
			<option value="گزارش عوارض دارویی" <?php selected( $type, 'گزارش عوارض دارویی' ); ?>><?php esc_html_e( 'گزارش عوارض', 'nafas-chatbot' ); ?></option>
			<option value="درخواست مشاوره" <?php selected( $type, 'درخواست مشاوره' ); ?>><?php esc_html_e( 'درخواست مشاوره', 'nafas-chatbot' ); ?></option>
		</select>
		<select name="status">
			<option value=""><?php esc_html_e( 'همه وضعیت‌ها', 'nafas-chatbot' ); ?></option>
			<?php foreach ( $status_labels as $sk => $sl ) : ?>
				<option value="<?php echo esc_attr( $sk ); ?>" <?php selected( $status, $sk ); ?>><?php echo esc_html( $sl ); ?></option>
			<?php endforeach; ?>
		</select>
		<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'جستجوی نام، تلفن یا متن...', 'nafas-chatbot' ); ?>">
		<label class="nafas-date-label"><?php esc_html_e( 'از', 'nafas-chatbot' ); ?> <input type="date" name="date_from" value="<?php echo esc_attr( isset( $date_from ) ? $date_from : '' ); ?>"></label>
		<label class="nafas-date-label"><?php esc_html_e( 'تا', 'nafas-chatbot' ); ?> <input type="date" name="date_to" value="<?php echo esc_attr( isset( $date_to ) ? $date_to : '' ); ?>"></label>
		<button type="submit" class="button"><?php esc_html_e( 'فیلتر', 'nafas-chatbot' ); ?></button>

		<?php
		$export_args = array_filter(
			array(
				'type'      => $type,
				'status'    => $status,
				's'         => $search,
				'date_from' => isset( $date_from ) ? $date_from : '',
				'date_to'   => isset( $date_to ) ? $date_to : '',
			)
		);
		$export_url = wp_nonce_url( add_query_arg( array_merge( array( 'action' => 'nafas_chatbot_export' ), $export_args ), admin_url( 'admin-post.php' ) ), 'nafas_export' );
		?>
		<a href="<?php echo esc_url( $export_url ); ?>" class="button button-secondary nafas-export-btn">
			<span class="dashicons dashicons-download"></span> <?php echo $export_args ? esc_html__( 'خروجی CSV (فیلترشده)', 'nafas-chatbot' ) : esc_html__( 'خروجی CSV', 'nafas-chatbot' ); ?>
		</a>
	</form>

	<table class="wp-list-table widefat fixed striped nafas-submissions">
		<thead>
			<tr>
				<th style="width:40px">#</th>
				<th style="width:140px"><?php esc_html_e( 'نوع', 'nafas-chatbot' ); ?></th>
				<th style="width:130px"><?php esc_html_e( 'نام', 'nafas-chatbot' ); ?></th>
				<th style="width:120px"><?php esc_html_e( 'تلفن', 'nafas-chatbot' ); ?></th>
				<th style="width:110px"><?php esc_html_e( 'محصول', 'nafas-chatbot' ); ?></th>
				<th><?php esc_html_e( 'توضیحات', 'nafas-chatbot' ); ?></th>
				<th style="width:120px"><?php esc_html_e( 'وضعیت', 'nafas-chatbot' ); ?></th>
				<th style="width:140px"><?php esc_html_e( 'تاریخ', 'nafas-chatbot' ); ?></th>
				<th style="width:90px"><?php esc_html_e( 'عملیات', 'nafas-chatbot' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $result['items'] ) ) : ?>
				<tr><td colspan="9" class="nafas-empty"><?php esc_html_e( 'هیچ درخواستی یافت نشد.', 'nafas-chatbot' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $result['items'] as $row ) : ?>
					<?php
					$is_adr      = ( false !== mb_strpos( $row->type, 'عوارض' ) );
					$status_url  = wp_nonce_url( add_query_arg( array( 'nafas_action' => 'status', 'sid' => $row->id ), $base_url ), 'nafas_sub_action' );
					$delete_url  = wp_nonce_url( add_query_arg( array( 'nafas_action' => 'delete', 'sid' => $row->id ), $base_url ), 'nafas_sub_action' );

					$adr_fields = array(
						'reporter_type'     => __( 'نوع گزارش‌دهنده', 'nafas-chatbot' ),
						'severity'          => __( 'شدت عارضه', 'nafas-chatbot' ),
						'outcome'           => __( 'پیامد', 'nafas-chatbot' ),
						'batch_number'      => __( 'شماره سری ساخت (Batch)', 'nafas-chatbot' ),
						'concomitant_drugs' => __( 'داروهای مصرفی همزمان', 'nafas-chatbot' ),
					);
					$has_extra = false;
					foreach ( $adr_fields as $fk => $fl ) {
						if ( ! empty( $row->$fk ) ) {
							$has_extra = true;
							break;
						}
					}
					?>
					<tr>
						<td><?php echo esc_html( $row->id ); ?></td>
						<td>
							<span class="nafas-badge <?php echo $is_adr ? 'nafas-badge--red' : 'nafas-badge--purple'; ?>">
								<?php echo esc_html( $row->type ); ?>
							</span>
						</td>
						<td><strong><?php echo esc_html( $row->name ); ?></strong></td>
						<td dir="ltr"><a href="tel:<?php echo esc_attr( $row->phone ); ?>"><?php echo esc_html( $row->phone ); ?></a></td>
						<td><?php echo $row->product ? esc_html( $row->product ) : '—'; ?></td>
						<td class="nafas-desc"><?php echo esc_html( wp_trim_words( $row->description, 20 ) ); ?></td>
						<td>
							<select class="nafas-status-select" data-url="<?php echo esc_url( $status_url ); ?>">
								<?php foreach ( $status_labels as $sk => $sl ) : ?>
									<option value="<?php echo esc_attr( $sk ); ?>" <?php selected( $row->status, $sk ); ?>><?php echo esc_html( $sl ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td><?php echo esc_html( $row->created_at ); ?></td>
						<td class="nafas-row-actions">
							<button type="button" class="nafas-detail-toggle" aria-label="<?php esc_attr_e( 'مشاهده جزئیات', 'nafas-chatbot' ); ?>" title="<?php esc_attr_e( 'مشاهده جزئیات', 'nafas-chatbot' ); ?>">
								<span class="dashicons dashicons-visibility"></span>
							</button>
							<a href="<?php echo esc_url( $delete_url ); ?>" class="nafas-del-link" onclick="return confirm('<?php esc_attr_e( 'آیا از حذف این درخواست اطمینان دارید؟', 'nafas-chatbot' ); ?>');">
								<span class="dashicons dashicons-trash"></span>
							</a>
						</td>
					</tr>
					<tr class="nafas-detail-row" hidden>
						<td colspan="9">
							<div class="nafas-detail">
								<div class="nafas-detail__block">
									<h4><?php esc_html_e( 'شرح کامل', 'nafas-chatbot' ); ?></h4>
									<p><?php echo nl2br( esc_html( $row->description ) ); ?></p>
								</div>
								<?php if ( $has_extra ) : ?>
									<div class="nafas-detail__block">
										<h4><?php esc_html_e( 'اطلاعات استاندارد عارضه (ADR)', 'nafas-chatbot' ); ?></h4>
										<dl class="nafas-detail__grid">
											<?php foreach ( $adr_fields as $fk => $fl ) : ?>
												<?php if ( ! empty( $row->$fk ) ) : ?>
													<dt><?php echo esc_html( $fl ); ?></dt>
													<dd><?php echo esc_html( $row->$fk ); ?></dd>
												<?php endif; ?>
											<?php endforeach; ?>
										</dl>
									</div>
								<?php endif; ?>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>

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

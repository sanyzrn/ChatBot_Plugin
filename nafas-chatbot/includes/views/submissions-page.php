<?php
/**
 * نمای صفحه مدیریت درخواست‌ها.
 *
 * @package NafasChatbot
 * @var array  $result  نتیجه واکشی.
 * @var array  $counts  شمارش‌ها.
 * @var string $type    فیلتر نوع.
 * @var string $status  فیلتر وضعیت.
 * @var string $search  جستجو.
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

$notify_labels = array(
	'pending'  => array(
		'label' => __( 'در انتظار', 'nafas-chatbot' ),
		'class' => 'nafas-badge--gray',
	),
	'sent'     => array(
		'label' => __( 'ارسال شد', 'nafas-chatbot' ),
		'class' => 'nafas-badge--green',
	),
	'failed'   => array(
		'label' => __( 'ناموفق', 'nafas-chatbot' ),
		'class' => 'nafas-badge--red',
	),
	'disabled' => array(
		'label' => __( 'غیرفعال', 'nafas-chatbot' ),
		'class' => 'nafas-badge--gray',
	),
);

$base_url    = admin_url( 'admin.php?page=nafas-chatbot-submissions' );
$retry_nonce = wp_create_nonce( 'nafas_retry_notify' );
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
		$export_url  = wp_nonce_url( add_query_arg( array_merge( array( 'action' => 'nafas_chatbot_export' ), $export_args ), admin_url( 'admin-post.php' ) ), 'nafas_export' );
		?>
		<a href="<?php echo esc_url( $export_url ); ?>" class="button button-secondary nafas-export-btn">
			<span class="dashicons dashicons-download"></span> <?php echo $export_args ? esc_html__( 'خروجی CSV (فیلترشده)', 'nafas-chatbot' ) : esc_html__( 'خروجی CSV', 'nafas-chatbot' ); ?>
		</a>
	</form>

	<!-- Bulk Actions Form -->
	<form method="post" id="nafas-bulk-form">
		<?php wp_nonce_field( 'nafas_bulk_action', 'nafas_bulk_nonce' ); ?>
		<div class="nafas-bulk-bar tablenav top">
			<div class="alignleft actions bulkactions" style="display:flex;gap:6px;align-items:center;padding:8px 0">
				<label for="nafas-bulk-select" class="screen-reader-text"><?php esc_html_e( 'عملیات دسته‌ای', 'nafas-chatbot' ); ?></label>
				<select id="nafas-bulk-select" name="nafas_bulk_action">
					<option value=""><?php esc_html_e( 'عملیات دسته‌ای...', 'nafas-chatbot' ); ?></option>
					<optgroup label="<?php esc_attr_e( 'تغییر وضعیت', 'nafas-chatbot' ); ?>">
						<?php foreach ( $status_labels as $sk => $sl ) : ?>
							<option value="<?php echo esc_attr( $sk ); ?>"><?php echo esc_html( $sl ); ?></option>
						<?php endforeach; ?>
					</optgroup>
					<option value="delete"><?php esc_html_e( 'حذف', 'nafas-chatbot' ); ?></option>
				</select>
				<button type="submit" class="button" id="nafas-bulk-apply"
					onclick="return confirm('<?php esc_attr_e( 'آیا مطمئنید؟', 'nafas-chatbot' ); ?>')">
					<?php esc_html_e( 'اجرا', 'nafas-chatbot' ); ?>
				</button>
				<span id="nafas-bulk-count" class="description"></span>
			</div>
		</div>

		<table class="wp-list-table widefat fixed striped nafas-submissions">
			<thead>
				<tr>
					<th style="width:30px"><input type="checkbox" id="nafas-select-all" title="<?php esc_attr_e( 'انتخاب همه', 'nafas-chatbot' ); ?>"></th>
					<th style="width:40px">#</th>
					<th style="width:130px"><?php esc_html_e( 'نوع', 'nafas-chatbot' ); ?></th>
					<th style="width:120px"><?php esc_html_e( 'نام', 'nafas-chatbot' ); ?></th>
					<th style="width:110px"><?php esc_html_e( 'تلفن', 'nafas-chatbot' ); ?></th>
					<th style="width:100px"><?php esc_html_e( 'محصول', 'nafas-chatbot' ); ?></th>
					<th><?php esc_html_e( 'توضیحات', 'nafas-chatbot' ); ?></th>
					<th style="width:110px"><?php esc_html_e( 'وضعیت', 'nafas-chatbot' ); ?></th>
					<th style="width:110px"><?php esc_html_e( 'اعلان', 'nafas-chatbot' ); ?></th>
					<th style="width:130px"><?php esc_html_e( 'تاریخ', 'nafas-chatbot' ); ?></th>
					<th style="width:80px"><?php esc_html_e( 'عملیات', 'nafas-chatbot' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $result['items'] ) ) : ?>
					<tr><td colspan="11" class="nafas-empty"><?php esc_html_e( 'هیچ درخواستی یافت نشد.', 'nafas-chatbot' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $result['items'] as $row ) : ?>
						<?php
						$is_adr     = ( false !== mb_strpos( $row->type, 'عوارض' ) );
						$status_url = wp_nonce_url(
							add_query_arg(
								array(
									'nafas_action' => 'status',
									'sid' => $row->id,
								),
								$base_url
							),
							'nafas_sub_action'
						);
						$delete_url = wp_nonce_url(
							add_query_arg(
								array(
									'nafas_action' => 'delete',
									'sid' => $row->id,
								),
								$base_url
							),
							'nafas_sub_action'
						);

						$notify_status = isset( $row->notify_status ) ? $row->notify_status : 'pending';
						$notify_info   = isset( $notify_labels[ $notify_status ] ) ? $notify_labels[ $notify_status ] : $notify_labels['pending'];

						$adr_fields = array(
							'reporter_type'     => __( 'نوع گزارش‌دهنده', 'nafas-chatbot' ),
							'severity'          => __( 'شدت عارضه', 'nafas-chatbot' ),
							'outcome'           => __( 'پیامد', 'nafas-chatbot' ),
							'batch_number'      => __( 'شماره سری ساخت (Batch)', 'nafas-chatbot' ),
							'concomitant_drugs' => __( 'داروهای مصرفی همزمان', 'nafas-chatbot' ),
						);
						$has_extra  = false;
						foreach ( $adr_fields as $fk => $fl ) {
							if ( ! empty( $row->$fk ) ) {
								$has_extra = true;
								break;
							}
						}
						?>
						<tr>
							<td><input type="checkbox" class="nafas-row-cb" name="sids[]" value="<?php echo esc_attr( $row->id ); ?>"></td>
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
							<td>
								<span class="nafas-badge <?php echo esc_attr( $notify_info['class'] ); ?>" id="notify-badge-<?php echo esc_attr( $row->id ); ?>">
									<?php echo esc_html( $notify_info['label'] ); ?>
								</span>
								<?php if ( in_array( $notify_status, array( 'pending', 'failed' ), true ) ) : ?>
									<button type="button"
										class="nafas-retry-btn button button-small"
										data-id="<?php echo esc_attr( $row->id ); ?>"
										data-nonce="<?php echo esc_attr( $retry_nonce ); ?>"
										title="<?php esc_attr_e( 'ارسال مجدد اعلان', 'nafas-chatbot' ); ?>"
										style="margin-top:4px;display:block">
										<span class="dashicons dashicons-controls-repeat" style="vertical-align:middle"></span>
										<?php esc_html_e( 'ارسال مجدد', 'nafas-chatbot' ); ?>
									</button>
								<?php endif; ?>
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
							<td colspan="11">
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
	</form><!-- end bulk form -->

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

<script>
(function () {
	// Select All checkbox
	var selectAll = document.getElementById('nafas-select-all');
	var bulkCount = document.getElementById('nafas-bulk-count');
	function updateCount() {
		var checked = document.querySelectorAll('.nafas-row-cb:checked').length;
		bulkCount.textContent = checked ? '(' + checked + ' مورد انتخاب شده)' : '';
	}
	if (selectAll) {
		selectAll.addEventListener('change', function () {
			document.querySelectorAll('.nafas-row-cb').forEach(function (cb) { cb.checked = selectAll.checked; });
			updateCount();
		});
	}
	document.querySelectorAll('.nafas-row-cb').forEach(function (cb) {
		cb.addEventListener('change', updateCount);
	});

	// Bulk form — require at least one selection and an action
	var bulkForm = document.getElementById('nafas-bulk-form');
	if (bulkForm) {
		bulkForm.addEventListener('submit', function (e) {
			var action = document.getElementById('nafas-bulk-select').value;
			var checked = document.querySelectorAll('.nafas-row-cb:checked').length;
			if (!action) { e.preventDefault(); alert('<?php esc_html_e( 'لطفاً یک عملیات انتخاب کنید.', 'nafas-chatbot' ); ?>'); return; }
			if (!checked) { e.preventDefault(); alert('<?php esc_html_e( 'لطفاً حداقل یک ردیف انتخاب کنید.', 'nafas-chatbot' ); ?>'); return; }
		});
	}

	// Retry notification buttons
	document.querySelectorAll('.nafas-retry-btn').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var id = btn.dataset.id;
			var nonce = btn.dataset.nonce;
			btn.disabled = true;
			btn.innerHTML = '<span class="dashicons dashicons-update nafas-spin"></span>';

			var data = new FormData();
			data.append('action', 'nafas_retry_notification');
			data.append('nonce', nonce);
			data.append('sid', id);

			fetch(ajaxurl, { method: 'POST', body: data })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					var badge = document.getElementById('notify-badge-' + id);
					var labels = { pending: 'در انتظار', sent: 'ارسال شد', failed: 'ناموفق', disabled: 'غیرفعال' };
					var classes = { pending: 'nafas-badge--gray', sent: 'nafas-badge--green', failed: 'nafas-badge--red', disabled: 'nafas-badge--gray' };
					if (res.success && badge) {
						var ns = res.data.notify_status || 'pending';
						badge.textContent = labels[ns] || ns;
						badge.className = 'nafas-badge ' + (classes[ns] || '');
					}
					if (res.success && res.data.notify_status === 'sent') {
						btn.remove();
					} else {
						btn.disabled = false;
						btn.innerHTML = '<span class="dashicons dashicons-controls-repeat" style="vertical-align:middle"></span> ارسال مجدد';
					}
				})
				.catch(function () {
					btn.disabled = false;
					btn.innerHTML = '<span class="dashicons dashicons-controls-repeat" style="vertical-align:middle"></span> ارسال مجدد';
				});
		});
	});
}());
</script>
<style>
.nafas-badge--green { background:#d1fae5; color:#065f46; }
.nafas-badge--gray  { background:#f3f4f6; color:#6b7280; }
.nafas-spin { animation: spin 1s linear infinite; display:inline-block; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<?php
/**
 * نمای داشبورد آماری.
 *
 * @package NafasChatbot
 * @var array $counts       شمارش درخواست‌ها بر اساس نوع.
 * @var array $chat_stats   آمار گفتگوها.
 * @var array $product_subs شمارش درخواست‌ها بر اساس محصول.
 * @var array $recent       آخرین درخواست‌ها.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$total_chats   = isset( $chat_stats['total'] ) ? (int) $chat_stats['total'] : 0;
$adr_count     = isset( $counts['گزارش عوارض دارویی'] ) ? (int) $counts['گزارش عوارض دارویی'] : 0;
$consult_count = isset( $counts['درخواست مشاوره'] ) ? (int) $counts['درخواست مشاوره'] : 0;
$total_subs    = isset( $counts['total'] ) ? (int) $counts['total'] : 0;

// محبوب‌ترین محصولات: ترکیب تعامل گفتگو و درخواست‌ها بر اساس نام محصول.
$popular = array();
foreach ( (array) ( isset( $chat_stats['by_product'] ) ? $chat_stats['by_product'] : array() ) as $name => $c ) {
	$popular[ $name ] = ( isset( $popular[ $name ] ) ? $popular[ $name ] : 0 ) + (int) $c;
}
foreach ( (array) $product_subs as $name => $c ) {
	$popular[ $name ] = ( isset( $popular[ $name ] ) ? $popular[ $name ] : 0 ) + (int) $c;
}
arsort( $popular );
$popular     = array_slice( $popular, 0, 6, true );
$popular_max = $popular ? max( $popular ) : 0;

// روند گفتگوها در ۱۴ روز اخیر.
$daily      = isset( $chat_stats['daily'] ) && is_array( $chat_stats['daily'] ) ? $chat_stats['daily'] : array();
$trend      = array();
$today_dt   = new DateTimeImmutable( 'now', wp_timezone() ); // تاریخ محلی، هم‌راستا با کلیدهای روزانهٔ record_chat.
for ( $i = 13; $i >= 0; $i-- ) {
	$d           = $today_dt->modify( "-{$i} days" )->format( 'Y-m-d' );
	$trend[ $d ] = isset( $daily[ $d ] ) ? (int) $daily[ $d ] : 0;
}
$trend_max = $trend ? max( $trend ) : 0;

$status_labels = array(
	'new'         => __( 'جدید', 'nafas-chatbot' ),
	'in_progress' => __( 'در حال پیگیری', 'nafas-chatbot' ),
	'done'        => __( 'انجام شده', 'nafas-chatbot' ),
	'archived'    => __( 'بایگانی', 'nafas-chatbot' ),
);
?>
<div class="wrap nafas-admin" dir="rtl">
	<h1 class="nafas-admin__title">
		<span class="dashicons dashicons-chart-area"></span>
		<?php esc_html_e( 'داشبورد دستیار هوشمند', 'nafas-chatbot' ); ?>
		<span class="nafas-admin__ver">v<?php echo esc_html( NAFAS_CHATBOT_VERSION ); ?></span>
	</h1>
	<p class="description"><?php esc_html_e( 'نمای کلی عملکرد دستیار هوشمند: گفتگوها، درخواست‌ها، بازخوردها و رضایت کاربران.', 'nafas-chatbot' ); ?></p>

	<!-- کارت‌های آماری -->
	<div class="nafas-kpi-grid">
		<div class="nafas-kpi nafas-kpi--blue">
			<span class="nafas-kpi__icon dashicons dashicons-format-chat"></span>
			<span class="nafas-kpi__num"><?php echo esc_html( number_format_i18n( $total_chats ) ); ?></span>
			<span class="nafas-kpi__label"><?php esc_html_e( 'تعداد گفتگوها', 'nafas-chatbot' ); ?></span>
		</div>
		<div class="nafas-kpi nafas-kpi--red">
			<span class="nafas-kpi__icon dashicons dashicons-warning"></span>
			<span class="nafas-kpi__num"><?php echo esc_html( number_format_i18n( $adr_count ) ); ?></span>
			<span class="nafas-kpi__label"><?php esc_html_e( 'گزارش عوارض دارویی', 'nafas-chatbot' ); ?></span>
		</div>
		<div class="nafas-kpi nafas-kpi--purple">
			<span class="nafas-kpi__icon dashicons dashicons-sos"></span>
			<span class="nafas-kpi__num"><?php echo esc_html( number_format_i18n( $consult_count ) ); ?></span>
			<span class="nafas-kpi__label"><?php esc_html_e( 'درخواست مشاوره', 'nafas-chatbot' ); ?></span>
		</div>
		<div class="nafas-kpi nafas-kpi--green">
			<span class="nafas-kpi__icon dashicons dashicons-list-view"></span>
			<span class="nafas-kpi__num"><?php echo esc_html( number_format_i18n( $total_subs ) ); ?></span>
			<span class="nafas-kpi__label"><?php esc_html_e( 'کل درخواست‌های ثبت‌شده', 'nafas-chatbot' ); ?></span>
		</div>
	</div>

	<?php $ins = isset( $insights ) ? $insights : array(); ?>
	<div class="nafas-insights">
		<a class="nafas-insight nafas-insight--amber" href="<?php echo esc_url( admin_url( 'admin.php?page=nafas-chatbot-chatlog&source=unanswered' ) ); ?>">
			<span class="nafas-insight__num"><?php echo esc_html( number_format_i18n( isset( $ins['unanswered'] ) ? $ins['unanswered'] : 0 ) ); ?></span>
			<span class="nafas-insight__label">🔍 <?php esc_html_e( 'سوالات بی‌پاسخ', 'nafas-chatbot' ); ?></span>
		</a>
		<a class="nafas-insight" href="<?php echo esc_url( admin_url( 'admin.php?page=nafas-chatbot-qa' ) ); ?>">
			<span class="nafas-insight__num"><?php echo esc_html( number_format_i18n( isset( $ins['qa_count'] ) ? $ins['qa_count'] : 0 ) ); ?></span>
			<span class="nafas-insight__label">🗂️ <?php esc_html_e( 'پاسخ در بانک', 'nafas-chatbot' ); ?></span>
		</a>
		<div class="nafas-insight nafas-insight--green">
			<span class="nafas-insight__num">👍 <?php echo esc_html( number_format_i18n( isset( $ins['feedback']['up'] ) ? $ins['feedback']['up'] : 0 ) ); ?> &nbsp; 👎 <?php echo esc_html( number_format_i18n( isset( $ins['feedback']['down'] ) ? $ins['feedback']['down'] : 0 ) ); ?></span>
			<span class="nafas-insight__label">💬 <?php esc_html_e( 'بازخورد پاسخ‌ها', 'nafas-chatbot' ); ?></span>
		</div>
		<?php $csat = isset( $ins['csat'] ) ? $ins['csat'] : array( 'avg' => 0, 'count' => 0 ); ?>
		<div class="nafas-insight">
			<span class="nafas-insight__num">⭐ <?php echo esc_html( $csat['count'] > 0 ? number_format_i18n( $csat['avg'], 1 ) : '—' ); ?></span>
			<span class="nafas-insight__label"><?php esc_html_e( 'رضایت گفتگو', 'nafas-chatbot' ); ?><?php echo $csat['count'] > 0 ? ' (' . esc_html( number_format_i18n( $csat['count'] ) ) . ' ' . esc_html__( 'رأی', 'nafas-chatbot' ) . ')' : ''; ?></span>
		</div>
		<a class="nafas-insight nafas-insight--red" href="<?php echo esc_url( admin_url( 'admin.php?page=nafas-chatbot-submissions&type=' . rawurlencode( 'گزارش عوارض دارویی' ) ) ); ?>">
			<span class="nafas-insight__num"><?php echo esc_html( number_format_i18n( isset( $ins['serious'] ) ? $ins['serious'] : 0 ) ); ?></span>
			<span class="nafas-insight__label">🚨 <?php esc_html_e( 'عوارض جدی', 'nafas-chatbot' ); ?></span>
		</a>
	</div>

	<div class="nafas-dash-cols">
		<!-- محبوب‌ترین محصولات -->
		<div class="nafas-card">
			<h3 class="nafas-card__title"><span class="dashicons dashicons-star-filled"></span> <?php esc_html_e( 'محبوب‌ترین محصولات', 'nafas-chatbot' ); ?></h3>
			<?php if ( empty( $popular ) ) : ?>
				<p class="nafas-card__empty"><?php esc_html_e( 'هنوز داده‌ای ثبت نشده است.', 'nafas-chatbot' ); ?></p>
			<?php else : ?>
				<ul class="nafas-bars">
					<?php foreach ( $popular as $name => $c ) : ?>
						<?php $pct = $popular_max > 0 ? round( ( $c / $popular_max ) * 100 ) : 0; ?>
						<li class="nafas-bar">
							<div class="nafas-bar__head">
								<span class="nafas-bar__name"><?php echo esc_html( $name ); ?></span>
								<span class="nafas-bar__val"><?php echo esc_html( number_format_i18n( $c ) ); ?></span>
							</div>
							<div class="nafas-bar__track"><span class="nafas-bar__fill" style="width: <?php echo esc_attr( $pct ); ?>%"></span></div>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<!-- روند گفتگوها -->
		<div class="nafas-card">
			<h3 class="nafas-card__title"><span class="dashicons dashicons-chart-bar"></span> <?php esc_html_e( 'روند گفتگوها (۱۴ روز اخیر)', 'nafas-chatbot' ); ?></h3>
			<?php if ( 0 === $trend_max ) : ?>
				<p class="nafas-card__empty"><?php esc_html_e( 'در ۱۴ روز اخیر گفتگویی ثبت نشده است.', 'nafas-chatbot' ); ?></p>
			<?php else : ?>
				<div class="nafas-trend" role="img" aria-label="<?php esc_attr_e( 'نمودار روند گفتگوها', 'nafas-chatbot' ); ?>">
					<?php foreach ( $trend as $d => $v ) : ?>
						<?php $h = $trend_max > 0 ? max( 4, round( ( $v / $trend_max ) * 100 ) ) : 4; ?>
						<div class="nafas-trend__col" title="<?php echo esc_attr( $d . ' — ' . $v ); ?>">
							<div class="nafas-trend__bar" style="height: <?php echo esc_attr( $h ); ?>%"></div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- آخرین درخواست‌ها -->
	<div class="nafas-card">
		<h3 class="nafas-card__title">
			<span class="dashicons dashicons-clock"></span> <?php esc_html_e( 'آخرین درخواست‌ها', 'nafas-chatbot' ); ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=nafas-chatbot-submissions' ) ); ?>" class="nafas-card__link"><?php esc_html_e( 'مشاهده همه ←', 'nafas-chatbot' ); ?></a>
		</h3>
		<?php if ( empty( $recent ) ) : ?>
			<p class="nafas-card__empty"><?php esc_html_e( 'هنوز درخواستی ثبت نشده است.', 'nafas-chatbot' ); ?></p>
		<?php else : ?>
			<table class="wp-list-table widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'نوع', 'nafas-chatbot' ); ?></th>
						<th><?php esc_html_e( 'نام', 'nafas-chatbot' ); ?></th>
						<th><?php esc_html_e( 'تلفن', 'nafas-chatbot' ); ?></th>
						<th><?php esc_html_e( 'محصول', 'nafas-chatbot' ); ?></th>
						<th><?php esc_html_e( 'وضعیت', 'nafas-chatbot' ); ?></th>
						<th><?php esc_html_e( 'تاریخ', 'nafas-chatbot' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $recent as $row ) : ?>
						<?php $is_adr = ( false !== mb_strpos( $row->type, 'عوارض' ) ); ?>
						<tr>
							<td><span class="nafas-badge <?php echo $is_adr ? 'nafas-badge--red' : 'nafas-badge--purple'; ?>"><?php echo esc_html( $row->type ); ?></span></td>
							<td><strong><?php echo esc_html( $row->name ); ?></strong></td>
							<td dir="ltr"><?php echo esc_html( $row->phone ); ?></td>
							<td><?php echo $row->product ? esc_html( $row->product ) : '—'; ?></td>
							<td><?php echo esc_html( isset( $status_labels[ $row->status ] ) ? $status_labels[ $row->status ] : $row->status ); ?></td>
							<td><?php echo esc_html( $row->created_at ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>

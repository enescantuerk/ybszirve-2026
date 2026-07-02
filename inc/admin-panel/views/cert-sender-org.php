<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Yetkisiz işlem.' );

$queue = get_option( YBS_ORG_QUEUE_OPT, [] );
if ( ! is_array( $queue ) ) $queue = [];
$state = ybs_cert_sender_get_state( 'org' );
$log   = get_option( YBS_ORG_LOG_OPT, [] );
if ( ! is_array( $log ) ) $log = [];
$hour_count = count( array_filter( $log, function( $t ) { return (int) $t > ( time() - HOUR_IN_SECONDS ); } ) );

$pending = 0; $retry = 0; $sent = 0; $failed = 0;
foreach ( $queue as $q ) {
	$st = isset( $q['status'] ) ? $q['status'] : 'pending';
	if ( $st === 'pending' ) $pending++;
	elseif ( $st === 'retry' ) $retry++;
	elseif ( $st === 'sent' ) $sent++;
	elseif ( $st === 'failed' ) $failed++;
}

$to_send = array_values( array_filter( $queue, function( $r ) {
	$st = isset( $r['status'] ) ? $r['status'] : 'pending';
	return in_array( $st, [ 'pending', 'retry' ], true );
} ) );
$sent_rows = array_values( array_filter( $queue, function( $r ) {
	return isset( $r['status'] ) && $r['status'] === 'sent';
} ) );
?>
<div class="wrap">
	<h1 class="wp-heading-inline">Ekip Sertifika Gönder</h1>
	<hr class="wp-header-end">

	<?php if ( isset( $_GET['ybs_notice'] ) ) : ?>
		<div class="notice notice-info is-dismissible"><p><?php echo esc_html( wp_unslash( $_GET['ybs_notice'] ) ); ?></p></div>
	<?php endif; ?>

	<p>Organizasyon ekibi — kaynak liste: <code>assets/img/Organizasyon/organizasyon.csv</code><br>
	Sertifika yolu: <code>/assets/img/Organizasyon/isim.jpg</code> (ek dosya bulunursa mail ekinde gönderilir, bulunmazsa mail içinde görsel/link verilir).<br>
	E-posta metni katılımcı sertifikasından farklıdır: organizasyondaki emeğiniz için teşekkür eder ve sertifikayı iletir.</p>

	<div style="display:flex; gap:8px; flex-wrap:wrap; margin:12px 0 16px;">
		<form method="post"><?php wp_nonce_field( 'ybs_cert_sender_action' ); ?><input type="hidden" name="ybs_cert_profile" value="org"><input type="hidden" name="ybs_cert_action" value="prepare"><button class="button button-primary">CSV'den Kuyruk Oluştur</button></form>
		<form method="post"><?php wp_nonce_field( 'ybs_cert_sender_action' ); ?><input type="hidden" name="ybs_cert_profile" value="org"><input type="hidden" name="ybs_cert_action" value="start"><button class="button" style="background:#16a34a;color:#fff;border-color:#15803d;">Gönderimi Başlat</button></form>
		<form method="post"><?php wp_nonce_field( 'ybs_cert_sender_action' ); ?><input type="hidden" name="ybs_cert_profile" value="org"><input type="hidden" name="ybs_cert_action" value="stop"><button class="button" style="background:#dc2626;color:#fff;border-color:#b91c1c;">Durdur</button></form>
		<form method="post"><?php wp_nonce_field( 'ybs_cert_sender_action' ); ?><input type="hidden" name="ybs_cert_profile" value="org"><input type="hidden" name="ybs_cert_action" value="run_now"><button class="button">Şimdi 5 Adet Gönder</button></form>
		<form method="post"><?php wp_nonce_field( 'ybs_cert_sender_action' ); ?><input type="hidden" name="ybs_cert_profile" value="org"><input type="hidden" name="ybs_cert_action" value="send_test_enes"><button class="button" style="background:#2563eb;color:#fff;border-color:#1d4ed8;">ENES CANTÜRK Test Mail</button></form>
		<form method="post"><?php wp_nonce_field( 'ybs_cert_sender_action' ); ?><input type="hidden" name="ybs_cert_profile" value="org"><input type="hidden" name="ybs_cert_action" value="requeue_sent_all"><button class="button" style="background:#7c3aed;color:#fff;border-color:#6d28d9;">Gönderilenleri Tekrar Kuyruğa Al</button></form>
	</div>

	<div style="background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:12px;max-width:820px;">
		<strong>Durum:</strong> <?php echo (int) $state['running'] ? '<span style="color:#16a34a;">Çalışıyor</span>' : '<span style="color:#6b7280;">Durdu</span>'; ?><br>
		<strong>Toplam:</strong> <?php echo (int) $state['total']; ?> |
		<strong>Gönderildi:</strong> <?php echo (int) $sent; ?> |
		<strong>Bekleyen:</strong> <?php echo (int) $pending; ?> |
		<strong>Tekrar:</strong> <?php echo (int) $retry; ?> |
		<strong>Hata:</strong> <?php echo (int) $failed; ?><br>
		<strong>Son 1 saatte deneme (ekip kuyruğu):</strong> <?php echo (int) $hour_count; ?> / 150
		<p style="margin:8px 0 0;color:#6b7280;">Tarayıcı kapansa da WordPress cron tetiklendikçe gönderim devam eder (siteye trafik geldikçe çalışır). Katılımcı kuyruğu ayrı sayılmaz; her iki kuyruk için saatlik limit ayrıdır.</p>
	</div>

	<h2 style="margin-top:18px;">Gönderilecek Liste (ilk 300)</h2>
	<p style="margin-top:0;color:#6b7280;">Dosya eşleşmesi varsa “Aç” ile sertifika görselini kontrol edebilirsin.</p>
	<table class="wp-list-table widefat striped">
		<thead>
			<tr>
				<th>Ad Soyad</th>
				<th>E-posta</th>
				<th>Durum</th>
				<th>Eşleşme</th>
				<th>Dosya</th>
				<th>Hata</th>
			</tr>
		</thead>
		<tbody>
		<?php if ( empty( $to_send ) ) : ?>
			<tr><td colspan="6">Kuyruk boş.</td></tr>
		<?php else : ?>
			<?php foreach ( array_slice( $to_send, 0, 300 ) as $row ) : ?>
				<?php
				$img_url = ! empty( $row['img_url'] ) ? (string) $row['img_url'] : '';
				$img_name = $img_url ? basename( wp_parse_url( $img_url, PHP_URL_PATH ) ) : '—';
				?>
				<tr>
					<td><?php echo esc_html( $row['name'] ?? '' ); ?></td>
					<td><?php echo esc_html( $row['email'] ?? '' ); ?></td>
					<td><?php echo esc_html( $row['status'] ?? 'pending' ); ?></td>
					<td>
						<?php if ( $img_url ) : ?>
							<span style="color:#16a34a;font-weight:600;">Eşleşti</span>
						<?php else : ?>
							<span style="color:#dc2626;font-weight:600;">Eşleşmedi</span>
						<?php endif; ?>
					</td>
					<td>
						<?php if ( $img_url ) : ?>
							<a href="<?php echo esc_url( $img_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $img_name ); ?> (Aç)</a>
						<?php else : ?>
							—
						<?php endif; ?>
					</td>
					<td><?php echo esc_html( $row['last_error'] ?? '' ); ?></td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>

	<h2 style="margin-top:22px;">Gönderilenler</h2>
	<p style="margin-top:0;color:#6b7280;">Kimlere gönderildiği burada kalıcı kuyruk kaydından izlenir.</p>
	<table class="wp-list-table widefat striped">
		<thead>
			<tr>
				<th>Ad Soyad</th>
				<th>E-posta</th>
				<th>Gönderim Zamanı</th>
				<th>Dosya</th>
				<th>İşlem</th>
			</tr>
		</thead>
		<tbody>
		<?php if ( empty( $sent_rows ) ) : ?>
			<tr><td colspan="5">Henüz gönderilen kayıt yok.</td></tr>
		<?php else : ?>
			<?php foreach ( array_slice( $sent_rows, 0, 500 ) as $row ) : ?>
				<?php $img_url = ! empty( $row['img_url'] ) ? (string) $row['img_url'] : ''; ?>
				<tr>
					<td><?php echo esc_html( $row['name'] ?? '' ); ?></td>
					<td><?php echo esc_html( $row['email'] ?? '' ); ?></td>
					<td><?php echo esc_html( ! empty( $row['sent_at'] ) ? $row['sent_at'] : '—' ); ?></td>
					<td>
						<?php if ( $img_url ) : ?>
							<a href="<?php echo esc_url( $img_url ); ?>" target="_blank" rel="noopener">Aç</a>
						<?php else : ?>
							—
						<?php endif; ?>
					</td>
					<td>
						<form method="post" style="margin:0;">
							<?php wp_nonce_field( 'ybs_cert_sender_action' ); ?>
							<input type="hidden" name="ybs_cert_profile" value="org">
							<input type="hidden" name="ybs_cert_action" value="resend_one">
							<input type="hidden" name="resend_email" value="<?php echo esc_attr( $row['email'] ?? '' ); ?>">
							<button class="button button-small">Tekrar Gönder</button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
</div>

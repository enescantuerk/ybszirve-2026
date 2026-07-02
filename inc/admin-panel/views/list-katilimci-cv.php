<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'Bu sayfaya erişim yetkiniz yok.', 'duybs' ) );
}

$q = new WP_Query(
	array(
		'post_type'      => 'ybs_katilimci_cv',
		'post_status'    => 'publish',
		'posts_per_page' => 200,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

$org_users = get_users(
	array(
		'role'   => 'topluluk_uyesi',
		'number' => -1,
		'orderby' => 'display_name',
		'order'   => 'ASC',
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key'     => 'ybs_cv_dosyasi',
				'value'   => '',
				'compare' => '!=',
			),
			array(
				'relation' => 'OR',
				array(
					'key'     => 'ybs_status',
					'value'   => 'aktif',
					'compare' => '=',
				),
				array(
					'key'     => 'ybs_status',
					'compare' => 'NOT EXISTS',
				),
			),
		),
	)
);
?>
<div class="wrap ybs-wrap">
	<div class="ybs-header">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Katılımcı CVleri', 'duybs' ); ?></h1>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ybs-katilimci-cv&export_all_cvs_zip=1' ) ); ?>" class="page-title-action" style="background:#2563eb; color:#fff; border-color:#1d4ed8; margin-left:8px;">
			<?php esc_html_e( '📦 Tüm CVleri ZIP İndir', 'duybs' ); ?>
		</a>
		<p class="description" style="margin-top:8px;">
			<?php esc_html_e( 'QR ile açılan sayfadan yüklenen PDF dosyaları burada listelenir. Dosyalar', 'duybs' ); ?>
			<code>wp-content/uploads/<?php echo esc_html( YBS_CV_UPLOAD_SUBDIR ); ?></code>
			<?php esc_html_e( 'klasöründe tutulur. Katılımcılar CV güncellemesi için kendi şifrelerini kullanır; şifresi olmayan çok eski kayıtlar yalnızca sizin müdahalenizle güvenli şekilde eşleştirilebilir.', 'duybs' ); ?>
		</p>
		<hr class="wp-header-end">
	</div>

	<?php if ( ! $q->have_posts() ) : ?>
		<p><?php esc_html_e( 'Henüz yüklenmiş CV yok.', 'duybs' ); ?></p>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th scope="col" style="width:18%;"><?php esc_html_e( 'Ad', 'duybs' ); ?></th>
					<th scope="col" style="width:18%;"><?php esc_html_e( 'Soyad', 'duybs' ); ?></th>
					<th scope="col" style="width:18%;"><?php esc_html_e( 'Telefon', 'duybs' ); ?></th>
					<th scope="col" style="width:22%;"><?php esc_html_e( 'Dosya', 'duybs' ); ?></th>
					<th scope="col" style="width:24%;"><?php esc_html_e( 'Yüklenme', 'duybs' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				while ( $q->have_posts() ) :
					$q->the_post();
					$pid   = get_the_ID();
					$ad    = get_post_meta( $pid, '_ybs_cv_ad', true );
					$soyad = get_post_meta( $pid, '_ybs_cv_soyad', true );
					$tel   = get_post_meta( $pid, '_ybs_cv_telefon', true );
					$furl  = get_post_meta( $pid, '_ybs_cv_file_url', true );
					$fn    = get_post_meta( $pid, '_ybs_cv_filename', true );
					$when  = get_post_meta( $pid, '_ybs_cv_uploaded', true );
					?>
					<tr>
						<td><?php echo esc_html( $ad ); ?></td>
						<td><?php echo esc_html( $soyad ); ?></td>
						<td><?php echo esc_html( $tel ); ?></td>
						<td>
							<?php if ( $furl ) : ?>
								<a href="<?php echo esc_url( $furl ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $fn ? $fn : basename( $furl ) ); ?></a>
							<?php else : ?>
								—
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $when ? mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $when ) : get_the_date() ); ?></td>
					</tr>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</tbody>
		</table>
	<?php endif; ?>

	<hr style="margin:28px 0 16px;">
	<h2 style="margin:0 0 10px;"><?php esc_html_e( 'Organizasyon Ekibi CVleri', 'duybs' ); ?></h2>
	<p class="description" style="margin-top:0; margin-bottom:14px;">
		<?php esc_html_e( 'Topluluk üyesi rolündeki aktif ekip üyelerinin profilindeki CV bağlantıları listelenir.', 'duybs' ); ?>
	</p>
	<?php if ( empty( $org_users ) ) : ?>
		<p><?php esc_html_e( 'Organizasyon ekibi için yüklenmiş CV bulunamadı.', 'duybs' ); ?></p>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th scope="col" style="width:28%;"><?php esc_html_e( 'Ad Soyad', 'duybs' ); ?></th>
					<th scope="col" style="width:28%;"><?php esc_html_e( 'E-Posta', 'duybs' ); ?></th>
					<th scope="col" style="width:20%;"><?php esc_html_e( 'Departman', 'duybs' ); ?></th>
					<th scope="col" style="width:24%;"><?php esc_html_e( 'CV Dosyası', 'duybs' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $org_users as $u ) : ?>
					<?php
					$cv_url = (string) get_user_meta( $u->ID, 'ybs_cv_dosyasi', true );
					$dept   = (string) get_user_meta( $u->ID, 'ybs_departman', true );
					?>
					<tr>
						<td><?php echo esc_html( $u->display_name ); ?></td>
						<td><?php echo esc_html( $u->user_email ); ?></td>
						<td><?php echo esc_html( $dept !== '' ? $dept : '—' ); ?></td>
						<td>
							<?php if ( $cv_url !== '' ) : ?>
								<a href="<?php echo esc_url( $cv_url ); ?>" target="_blank" rel="noopener noreferrer">
									<?php echo esc_html( basename( wp_parse_url( $cv_url, PHP_URL_PATH ) ) ); ?>
								</a>
							<?php else : ?>
								—
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

<?php
/**
 * Template Name: Katılımcı CV Yükleme (QR)
 *
 * Salonda QR ile açılacak sayfa: ad, soyad, telefon, PDF CV.
 *
 * @package duybs
 */

get_header();
?>
<style>
	header, footer { display: none !important; }
</style>

<div class="ybs-cv-page">
	<div class="ybs-cv-card">
		<div class="ybs-cv-head">
			<h1><?php esc_html_e( 'CV Yükleme', 'duybs' ); ?></h1>
			<p><?php esc_html_e( 'Bilgilerinizi girin ve özgeçmişinizi PDF olarak yükleyin.', 'duybs' ); ?></p>
		</div>

		<div id="ybs-cv-success" class="ybs-cv-success" style="display:none;" role="status">
			<div class="ybs-cv-success-icon" aria-hidden="true">✓</div>
			<h2 id="ybs-cv-success-title"><?php esc_html_e( 'Teşekkürler', 'duybs' ); ?></h2>
			<p id="ybs-cv-success-msg"></p>
		</div>

		<form id="ybs-cv-form" method="post" enctype="multipart/form-data" novalidate>
			<?php wp_nonce_field( 'ybs_katilimci_cv_submit', 'security' ); ?>
			<input type="hidden" name="action" value="ybs_katilimci_cv_upload">

			<p class="ybs-cv-honeypot" aria-hidden="true">
				<label for="ybs_cv_company"><?php esc_html_e( 'Şirket', 'duybs' ); ?></label>
				<input type="text" name="ybs_cv_company" id="ybs_cv_company" value="" tabindex="-1" autocomplete="off">
			</p>

			<div class="ybs-cv-row">
				<div class="ybs-cv-field">
					<label for="ybs_cv_ad"><?php esc_html_e( 'Ad', 'duybs' ); ?> <span class="req">*</span></label>
					<input type="text" name="ad" id="ybs_cv_ad" required maxlength="80" autocomplete="given-name" placeholder="<?php esc_attr_e( 'Adınız', 'duybs' ); ?>">
				</div>
				<div class="ybs-cv-field">
					<label for="ybs_cv_soyad"><?php esc_html_e( 'Soyad', 'duybs' ); ?> <span class="req">*</span></label>
					<input type="text" name="soyad" id="ybs_cv_soyad" required maxlength="80" autocomplete="family-name" placeholder="<?php esc_attr_e( 'Soyadınız', 'duybs' ); ?>">
				</div>
			</div>

			<div class="ybs-cv-field">
				<label for="ybs_cv_tel"><?php esc_html_e( 'Cep telefonu', 'duybs' ); ?> <span class="req">*</span></label>
				<input type="tel" name="telefon" id="ybs_cv_tel" required inputmode="numeric" autocomplete="tel" placeholder="05XX XXX XX XX">
				<small class="ybs-cv-hint"><?php esc_html_e( 'CV güncellemesi yalnızca sizin belirlediğiniz güncelleme şifresi ile yapılabilir.', 'duybs' ); ?></small>
			</div>

			<div class="ybs-cv-field">
				<label for="ybs_cv_pin"><?php esc_html_e( 'CV güncelleme şifresi', 'duybs' ); ?> <span class="req">*</span></label>
				<input type="password" name="guncelleme_sifre" id="ybs_cv_pin" required minlength="8" maxlength="128" autocomplete="new-password" placeholder="<?php esc_attr_e( 'En az 8 karakter', 'duybs' ); ?>">
				<small class="ybs-cv-hint"><?php esc_html_e( 'İlk yüklemede belirleyin; sonradan aynı numara ile yeni PDF yüklerken aynı şifreyi girmeniz gerekir.', 'duybs' ); ?></small>
			</div>

			<div class="ybs-cv-field ybs-cv-replace-row" id="ybs_cv_replace_wrap">
				<label class="ybs-cv-check">
					<input type="checkbox" name="ybs_cv_replace" id="ybs_cv_replace" value="1">
					<span><?php esc_html_e( 'Bu telefonla daha önce yükledim; mevcut CV kaydımı güncelliyorum', 'duybs' ); ?></span>
				</label>
			</div>

			<div class="ybs-cv-field">
				<label for="ybs_cv_pdf"><?php esc_html_e( 'CV (yalnızca PDF)', 'duybs' ); ?> <span class="req">*</span></label>
				<input type="file" name="cv_pdf" id="ybs_cv_pdf" accept="application/pdf,.pdf" required>
				<small class="ybs-cv-hint"><?php esc_html_e( 'En fazla 5 MB.', 'duybs' ); ?></small>
			</div>

			<button type="submit" class="ybs-cv-submit" id="ybs_cv_btn"><?php esc_html_e( 'CV Gönder', 'duybs' ); ?></button>
			<div id="ybs_cv_response" class="ybs-cv-response" role="alert"></div>
		</form>
	</div>
</div>

<style>
	.ybs-cv-page { min-height: 100vh; background: linear-gradient(160deg, #0f172a 0%, #1e293b 45%, #0c4a6e 100%); padding: 32px 16px; font-family: system-ui, -apple-system, sans-serif; box-sizing: border-box; }
	.ybs-cv-card { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,.25); overflow: hidden; }
	.ybs-cv-head { background: #0f172a; color: #fff; padding: 28px 24px; text-align: center; }
	.ybs-cv-head h1 { margin: 0; font-size: 1.35rem; font-weight: 800; letter-spacing: .02em; }
	.ybs-cv-head p { margin: 10px 0 0; opacity: .85; font-size: .9rem; line-height: 1.45; }
	#ybs-cv-form { padding: 28px 24px 32px; }
	.ybs-cv-honeypot { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }
	.ybs-cv-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
	@media (max-width: 520px) { .ybs-cv-row { grid-template-columns: 1fr; } }
	.ybs-cv-field { margin-bottom: 18px; }
	.ybs-cv-field label { display: block; font-weight: 600; font-size: .78rem; text-transform: uppercase; letter-spacing: .06em; color: #475569; margin-bottom: 6px; }
	.req { color: #e11d48; }
	.ybs-cv-field input[type="text"], .ybs-cv-field input[type="tel"], .ybs-cv-field input[type="password"], .ybs-cv-field input[type="file"] {
		width: 100%; box-sizing: border-box; padding: 12px 14px; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 1rem; background: #f8fafc; color: #0f172a;
	}
	.ybs-cv-replace-row { margin-bottom: 14px; padding: 12px 14px; background: #f1f5f9; border-radius: 10px; border: 1px solid #e2e8f0; }
	.ybs-cv-check { display: flex; align-items: flex-start; gap: 10px; cursor: pointer; font-size: .875rem; color: #334155; font-weight: 500; text-transform: none; letter-spacing: normal; margin: 0; }
	.ybs-cv-check input { width: auto; margin-top: 3px; flex-shrink: 0; }
	.ybs-cv-replace-row.is-highlight { outline: 2px solid #0284c7; outline-offset: 2px; }
	.ybs-cv-field input:focus { outline: none; border-color: #0284c7; background: #fff; box-shadow: 0 0 0 3px rgba(2,132,199,.12); }
	.ybs-cv-hint { display: block; margin-top: 6px; font-size: .75rem; color: #64748b; line-height: 1.4; }
	.ybs-cv-submit {
		width: 100%; margin-top: 8px; padding: 16px; border: none; border-radius: 10px; background: #0284c7; color: #fff;
		font-size: 1rem; font-weight: 700; cursor: pointer; transition: background .2s, transform .15s;
	}
	.ybs-cv-submit:hover { background: #0369a1; }
	.ybs-cv-submit:disabled { background: #94a3b8; cursor: not-allowed; transform: none; }
	.ybs-cv-response { margin-top: 16px; padding: 12px 14px; border-radius: 10px; font-size: .9rem; font-weight: 600; display: none; }
	.ybs-cv-response.is-error { display: block; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
	.ybs-cv-response.is-ok { display: block; background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
	.ybs-cv-success { padding: 48px 24px; text-align: center; }
	.ybs-cv-success-icon { width: 72px; height: 72px; margin: 0 auto 16px; background: #d1fae5; color: #059669; border-radius: 50%; font-size: 2.5rem; line-height: 72px; font-weight: 800; }
	.ybs-cv-success h2 { margin: 0 0 10px; font-size: 1.25rem; color: #0f172a; }
	.ybs-cv-success p { margin: 0; color: #475569; line-height: 1.5; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
	var form = document.getElementById('ybs-cv-form');
	var btn = document.getElementById('ybs_cv_btn');
	var resp = document.getElementById('ybs_cv_response');
	var replaceWrap = document.getElementById('ybs_cv_replace_wrap');
	var pinInput = document.getElementById('ybs_cv_pin');
	var successBox = document.getElementById('ybs-cv-success');
	var successMsg = document.getElementById('ybs-cv-success-msg');
	var pdfInput = document.getElementById('ybs_cv_pdf');
	var ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;

	function showErr(msg) {
		resp.className = 'ybs-cv-response is-error';
		resp.textContent = msg;
		resp.style.display = 'block';
	}
	function clearResp() {
		resp.className = 'ybs-cv-response';
		resp.textContent = '';
		resp.style.display = 'none';
	}

	function submitForm() {
		clearResp();
		if (replaceWrap) replaceWrap.classList.remove('is-highlight');

		var pw = pinInput ? pinInput.value : '';
		if (!pw || pw.length < 8) {
			showErr(<?php echo wp_json_encode( __( 'Güncelleme şifresi en az 8 karakter olmalıdır.', 'duybs' ) ); ?>);
			if (pinInput) pinInput.focus();
			return;
		}

		if (pdfInput.files.length) {
			var sz = pdfInput.files[0].size;
			if (sz > 5 * 1024 * 1024) {
				showErr('Dosya en fazla 5 MB olabilir.');
				return;
			}
			var name = pdfInput.files[0].name.toLowerCase();
			if (!name.endsWith('.pdf')) {
				showErr('Yalnızca PDF seçebilirsiniz.');
				return;
			}
		}

		btn.disabled = true;
		btn.textContent = <?php echo wp_json_encode( __( 'Gönderiliyor…', 'duybs' ) ); ?>;

		var fd = new FormData(form);
		fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
			.then(function(r) { return r.json(); })
			.then(function(res) {
				if (res.success) {
					form.style.display = 'none';
					successBox.style.display = 'block';
					successMsg.textContent = typeof res.data === 'string' ? res.data : '';
					window.scrollTo({ top: 0, behavior: 'smooth' });
					return;
				}
				var d = res.data || {};
				var msg = d.message || <?php echo wp_json_encode( __( 'Bir hata oluştu.', 'duybs' ) ); ?>;
				if (d.code === 'duplicate') {
					btn.disabled = false;
					btn.textContent = <?php echo wp_json_encode( __( 'CV Gönder', 'duybs' ) ); ?>;
					showErr(msg);
					if (replaceWrap) {
						replaceWrap.classList.add('is-highlight');
						replaceWrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
					}
					return;
				}
				showErr(msg);
				btn.disabled = false;
				btn.textContent = <?php echo wp_json_encode( __( 'CV Gönder', 'duybs' ) ); ?>;
			})
			.catch(function() {
				showErr(<?php echo wp_json_encode( __( 'Bağlantı hatası. Tekrar deneyin.', 'duybs' ) ); ?>);
				btn.disabled = false;
				btn.textContent = <?php echo wp_json_encode( __( 'CV Gönder', 'duybs' ) ); ?>;
			});
	}

	if (form) {
		form.addEventListener('submit', function(e) {
			e.preventDefault();
			submitForm();
		});
	}
});
</script>

<?php
get_footer();

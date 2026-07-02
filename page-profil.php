<?php
/* Template Name: Giriş ve Profil */
get_header();

$is_logged_in = is_user_logged_in();
$depts = get_posts(['post_type'=>'departman', 'numberposts'=>-1]);

// Sayfa durumunu kontrol et
$action = isset($_GET['action']) ? $_GET['action'] : '';
?>

<div class="ybs-profile-wrapper">
    <div class="ybs-profile-container">

        <?php if ( ! $is_logged_in ) : ?>
            
            <?php if ( $action === 'lostpassword' ) : ?>
                <div class="auth-box">
                    <div class="auth-header">
                        <h2>Şifremi Unuttum</h2>
                        <p>Kayıtlı e-posta adresinizi girin, size bir sıfırlama bağlantısı gönderelim.</p>
                    </div>

                    <div id="lost-msg" style="display:none; margin-bottom:20px; padding:12px; border-radius:6px; font-weight:bold; text-align:center;"></div>

                    <form id="ybs-lost-form" class="ybs-form">
                        <div class="form-group">
                            <label>E-Posta Adresi</label>
                            <input type="email" id="lost_email" class="form-control" required>
                        </div>
                        <button type="submit" id="btn-lost" class="btn-primary" style="margin-bottom:15px;">Sıfırlama Linki Gönder</button>
                        <div style="text-align:center;">
                            <a href="<?php echo site_url('/profil/'); ?>" class="forgot-link">Giriş Ekranına Dön</a>
                        </div>
                    </form>
                </div>
                <script>
                document.getElementById('ybs-lost-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const btn = document.getElementById('btn-lost');
                    const msg = document.getElementById('lost-msg');
                    btn.disabled = true; btn.innerText = 'Gönderiliyor...';
                    
                    const fd = new URLSearchParams();
                    fd.append('action', 'ybs_ajax_lost_password');
                    fd.append('security', '<?php echo wp_create_nonce("ybs_profile_nonce"); ?>');
                    fd.append('user_login', document.getElementById('lost_email').value);

                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: fd, headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
                    .then(r => r.json()).then(res => {
                        msg.style.display = 'block';
                        if(res.success) { msg.style.backgroundColor = '#d1fae5'; msg.style.color = '#065f46'; msg.innerText = res.data; document.getElementById('ybs-lost-form').reset(); } 
                        else { msg.style.backgroundColor = '#fee2e2'; msg.style.color = '#991b1b'; msg.innerText = res.data; btn.disabled = false; btn.innerText = 'Sıfırlama Linki Gönder'; }
                    });
                });
                </script>

            <?php elseif ( $action === 'rp' && isset($_GET['key']) && isset($_GET['login']) ) : ?>
                <div class="auth-box">
                    <div class="auth-header">
                        <h2>Yeni Şifre Belirle</h2>
                        <p>Lütfen hesabınız için yeni ve güvenli bir şifre girin.</p>
                    </div>

                    <div id="reset-msg" style="display:none; margin-bottom:20px; padding:12px; border-radius:6px; font-weight:bold; text-align:center;"></div>

                    <form id="ybs-reset-form" class="ybs-form">
                        <input type="hidden" id="reset_key" value="<?php echo esc_attr($_GET['key']); ?>">
                        <input type="hidden" id="reset_login" value="<?php echo esc_attr($_GET['login']); ?>">
                        <div class="form-group">
                            <label>Yeni Şifre</label>
                            <input type="password" id="pass1" class="form-control" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label>Yeni Şifre (Tekrar)</label>
                            <input type="password" id="pass2" class="form-control" required minlength="6">
                        </div>
                        <button type="submit" id="btn-reset" class="btn-primary" style="margin-bottom:15px;">Şifreyi Güncelle</button>
                    </form>
                </div>
                <script>
                document.getElementById('ybs-reset-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const btn = document.getElementById('btn-reset');
                    const msg = document.getElementById('reset-msg');
                    btn.disabled = true; btn.innerText = 'Güncelleniyor...';
                    
                    const fd = new URLSearchParams();
                    fd.append('action', 'ybs_ajax_reset_password');
                    fd.append('security', '<?php echo wp_create_nonce("ybs_profile_nonce"); ?>');
                    fd.append('key', document.getElementById('reset_key').value);
                    fd.append('login', document.getElementById('reset_login').value);
                    fd.append('pass1', document.getElementById('pass1').value);
                    fd.append('pass2', document.getElementById('pass2').value);

                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: fd, headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
                    .then(r => r.json()).then(res => {
                        msg.style.display = 'block';
                        if(res.success) { 
                            msg.style.backgroundColor = '#d1fae5'; msg.style.color = '#065f46'; msg.innerText = res.data; 
                            btn.style.display = 'none'; // Başarılıysa butonu gizle
                            setTimeout(() => { window.location.href = '<?php echo site_url('/profil/'); ?>'; }, 2000); // 2 sn sonra girişe at
                        } 
                        else { msg.style.backgroundColor = '#fee2e2'; msg.style.color = '#991b1b'; msg.innerText = res.data; btn.disabled = false; btn.innerText = 'Şifreyi Güncelle'; }
                    });
                });
                </script>

            <?php else : ?>
                <div class="auth-box">
                    <div class="auth-header">
                        <h2>Giriş Yap</h2>
                        <p>Profilinize erişmek için lütfen giriş yapın.</p>
                    </div>

                    <?php 
                    if(isset($_GET['login']) && $_GET['login'] == 'failed') {
                        echo '<div class="alert alert-danger" style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:6px; font-weight:bold; margin-bottom:20px; text-align:center;">Hatalı e-posta veya şifre girdiniz.</div>';
                    }
                    ?>

                    <form action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" method="post" class="ybs-form">
                        <div class="form-group">
                            <label for="user_login">E-Posta Adresi</label>
                            <input type="text" name="log" id="user_login" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="user_pass">Şifre</label>
                            <input type="password" name="pwd" id="user_pass" class="form-control" required>
                        </div>
                        
                        <div class="form-options">
                            <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox" name="rememberme" value="forever"> Beni Hatırla</label>
                            <a href="<?php echo site_url('/profil/?action=lostpassword'); ?>" class="forgot-link">Şifremi Unuttum</a>
                        </div>

                        <input type="hidden" name="redirect_to" value="<?php echo esc_url(get_permalink()); ?>">
                        <button type="submit" name="wp-submit" class="btn-primary">Giriş Yap</button>
                    </form>
                </div>
            <?php endif; ?>

        <?php else : ?>

            <?php 
            $user = wp_get_current_user();
            
            // Tüm Metaları Çek
            $tc = get_user_meta($user->ID, 'ybs_tc_kimlik', true);
            $dogum = get_user_meta($user->ID, 'ybs_dogum_tarihi', true);
            $student_no = get_user_meta($user->ID, 'ybs_student_no', true);
            $phone = get_user_meta($user->ID, 'ybs_telefon', true);
            $sehir = get_user_meta($user->ID, 'ybs_sehir', true);
            $linkedin = get_user_meta($user->ID, 'ybs_linkedin', true);
            $beden = get_user_meta($user->ID, 'ybs_beden', true);
            
            $dept = get_user_meta($user->ID, 'ybs_departman', true);
            $duty = get_user_meta($user->ID, 'ybs_gorev_tanimi', true);
            
            $acil_kisi = get_user_meta($user->ID, 'ybs_acil_kisi', true);
            $acil_tel = get_user_meta($user->ID, 'ybs_acil_telefon', true);
            $acil_yak = get_user_meta($user->ID, 'ybs_acil_yakinlik', true);
            
            $blood = get_user_meta($user->ID, 'ybs_kan_grubu', true);
            $diet = get_user_meta($user->ID, 'ybs_beslenme', true);
            $health = get_user_meta($user->ID, 'ybs_health_notes', true);

            $img_url = get_user_meta($user->ID, 'ybs_fotograf', true) ?: 'https://www.gravatar.com/avatar/'.md5($user->user_email).'?s=150&d=mp';
            $cv_url = get_user_meta($user->ID, 'ybs_cv_dosyasi', true);
            ?>

            <div class="profile-box">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <img src="<?php echo esc_url($img_url); ?>" alt="Profil Fotoğrafı">
                    </div>
                    <div class="profile-title">
                        <h2>Merhaba, <?php echo esc_html($user->first_name ?: $user->display_name); ?>!</h2>
                        <p>Tüm bilgilerinizi ve kariyer dosyalarınızı aşağıdan güncelleyebilirsiniz.</p>
                    </div>
                    <div class="profile-actions">
                        <a href="<?php echo wp_logout_url(get_permalink()); ?>" class="btn-logout">Çıkış Yap</a>
                    </div>
                </div>

                <div id="profile-message" style="display:none; margin-bottom:20px; padding:15px; border-radius:8px; font-weight:bold; text-align:center;"></div>

                <form id="ybs-profile-form" class="ybs-form" enctype="multipart/form-data">
                    
                    <h3 class="section-title">Kişisel Bilgiler</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Adınız</label>
                            <input type="text" name="first_name" class="form-control" value="<?php echo esc_attr($user->first_name); ?>">
                        </div>
                        <div class="form-group">
                            <label>Soyadınız</label>
                            <input type="text" name="last_name" class="form-control" value="<?php echo esc_attr($user->last_name); ?>">
                        </div>
                        <div class="form-group">
                            <label>E-Posta (Değiştirilemez)</label>
                            <input type="email" class="form-control" value="<?php echo esc_attr($user->user_email); ?>" disabled style="background:#f3f4f6; color:#9ca3af;">
                        </div>
                        <div class="form-group">
                            <label>T.C. Kimlik Numarası</label>
                            <input type="text" name="ybs_tc_kimlik" class="form-control" maxlength="11" value="<?php echo esc_attr($tc); ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        <div class="form-group">
                            <label>Doğum Tarihi</label>
                            <input type="date" name="ybs_dogum_tarihi" class="form-control" value="<?php echo esc_attr($dogum); ?>">
                        </div>
                        <div class="form-group">
                            <label>Öğrenci Numarası</label>
                            <input type="text" name="ybs_student_no" class="form-control" maxlength="9" value="<?php echo esc_attr($student_no); ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        <div class="form-group">
                            <label>Telefon Numarası</label>
                            <input type="text" name="ybs_telefon" class="form-control" value="<?php echo esc_attr($phone); ?>">
                        </div>
                        <div class="form-group">
                            <label>İkamet Şehri</label>
                            <input type="text" name="ybs_sehir" class="form-control" value="<?php echo esc_attr($sehir); ?>">
                        </div>
                        <div class="form-group">
                            <label>Tişört Bedeni</label>
                            <select name="ybs_beden" class="form-control">
                                <option value="">Seçiniz</option>
                                <option value="XS" <?php selected($beden, 'XS'); ?>>XS</option>
                                <option value="S" <?php selected($beden, 'S'); ?>>S</option>
                                <option value="M" <?php selected($beden, 'M'); ?>>M</option>
                                <option value="L" <?php selected($beden, 'L'); ?>>L</option>
                                <option value="XL" <?php selected($beden, 'XL'); ?>>XL</option>
                                <option value="XXL" <?php selected($beden, 'XXL'); ?>>XXL</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>LinkedIn Profil Linki</label>
                            <input type="url" name="ybs_linkedin" class="form-control" value="<?php echo esc_attr($linkedin); ?>">
                        </div>
                    </div>

                    <h3 class="section-title">Organizasyon</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Departman</label>
                            <select name="ybs_departman" class="form-control">
                                <option value="">Seçiniz...</option>
                                <?php foreach($depts as $d): ?>
                                    <option value="<?php echo esc_attr($d->post_title); ?>" <?php selected($dept, $d->post_title); ?>><?php echo $d->post_title; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Görev Tanımı</label>
                            <input type="text" name="ybs_gorev_tanimi" class="form-control" value="<?php echo esc_attr($duty); ?>">
                        </div>
                    </div>

                    <h3 class="section-title">Acil Durum Bilgileri</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Ulaşılacak Kişi</label>
                            <input type="text" name="ybs_acil_kisi" class="form-control" value="<?php echo esc_attr($acil_kisi); ?>">
                        </div>
                        <div class="form-group">
                            <label>Acil Durum Telefonu</label>
                            <input type="text" name="ybs_acil_telefon" class="form-control" value="<?php echo esc_attr($acil_tel); ?>">
                        </div>
                        <div class="form-group">
                            <label>Yakınlık Derecesi</label>
                            <input type="text" name="ybs_acil_yakinlik" class="form-control" value="<?php echo esc_attr($acil_yak); ?>">
                        </div>
                    </div>

                    <h3 class="section-title">Sağlık Bilgileri</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Kan Grubu</label>
                            <select name="blood_type" class="form-control">
                                <option value="">Bilinmiyor</option>
                                <option value="0 Rh+" <?php selected($blood, '0 Rh+'); ?>>0 Rh+</option>
                                <option value="0 Rh-" <?php selected($blood, '0 Rh-'); ?>>0 Rh-</option>
                                <option value="A Rh+" <?php selected($blood, 'A Rh+'); ?>>A Rh+</option>
                                <option value="A Rh-" <?php selected($blood, 'A Rh-'); ?>>A Rh-</option>
                                <option value="B Rh+" <?php selected($blood, 'B Rh+'); ?>>B Rh+</option>
                                <option value="B Rh-" <?php selected($blood, 'B Rh-'); ?>>B Rh-</option>
                                <option value="AB Rh+" <?php selected($blood, 'AB Rh+'); ?>>AB Rh+</option>
                                <option value="AB Rh-" <?php selected($blood, 'AB Rh-'); ?>>AB Rh-</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Özel Beslenme Durumu</label>
                            <select name="ybs_beslenme" class="form-control">
                                <option value="Yok" <?php selected($diet, 'Yok'); ?>>Yok</option>
                                <option value="Vejetaryen" <?php selected($diet, 'Vejetaryen'); ?>>Vejetaryen</option>
                                <option value="Vegan" <?php selected($diet, 'Vegan'); ?>>Vegan</option>
                                <option value="Glutensiz" <?php selected($diet, 'Glutensiz'); ?>>Glutensiz</option>
                            </select>
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Alerjen / Sürekli İlaç / Kronik Rahatsızlık</label>
                            <input type="text" name="health_notes" class="form-control" value="<?php echo esc_attr($health); ?>">
                        </div>
                    </div>

                    <h3 class="section-title">Kariyer Dosyaları</h3>
                    <div class="file-upload-box">
                        <div class="form-group">
                            <label>Yeni Profil Fotoğrafı Yükle (Zorunlu Değil)</label>
                            <input type="file" name="profile_photo" accept="image/jpeg, image/png, image/heic, image/heif, .heic, .heif">
                            <small style="color:#64748b; font-size:12px;">Sadece değiştirmek isterseniz seçin.</small>
                        </div>
                        <div class="form-group" style="margin-top:15px;">
                            <label>Yeni CV Yükle (Zorunlu Değil, PDF)</label>
                            <input type="file" name="cv_file" accept=".pdf">
                            <small style="color:#64748b; font-size:12px;">Sadece değiştirmek isterseniz seçin. <?php if($cv_url) echo '<a href="'.$cv_url.'" target="_blank" style="color:#0284c7;">Mevcut CV\'yi Görüntüle</a>'; ?></small>
                        </div>
                    </div>

                    <div style="margin-top: 30px; text-align: right;">
                        <button type="submit" id="btn-save-profile" class="btn-primary">Tüm Bilgilerimi Kaydet</button>
                    </div>
                </form>
            </div>

            <script>
            document.getElementById('ybs-profile-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = document.getElementById('btn-save-profile');
                const msg = document.getElementById('profile-message');
                
                btn.disabled = true;
                btn.innerText = 'Kaydediliyor, Lütfen Bekleyin...';
                msg.style.display = 'none';
                
                const fd = new FormData(this);
                fd.append('action', 'ybs_update_full_profile_ajax');
                fd.append('security', '<?php echo wp_create_nonce("ybs_profile_nonce"); ?>');

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: fd })
                .then(r => r.json()).then(res => {
                    msg.style.display = 'block';
                    if(res.success) {
                        msg.style.backgroundColor = '#ecfdf5'; msg.style.color = '#065f46'; msg.style.border = '1px solid #10b981';
                        msg.innerText = 'Tüm bilgileriniz ve dosyalarınız başarıyla güncellendi!';
                    } else {
                        msg.style.backgroundColor = '#fef2f2'; msg.style.color = '#991b1b'; msg.style.border = '1px solid #ef4444';
                        msg.innerHTML = 'Hata oluştu:<br>' + res.data;
                    }
                    btn.disabled = false; btn.innerText = 'Tüm Bilgilerimi Kaydet';
                    setTimeout(() => { msg.style.display = 'none'; }, 6000);
                }).catch(err => {
                    msg.style.display = 'block'; msg.style.backgroundColor = '#fef2f2'; msg.style.color = '#991b1b';
                    msg.innerText = 'Sunucuyla bağlantı kurulamadı veya dosya boyutu çok büyük.';
                    btn.disabled = false; btn.innerText = 'Tüm Bilgilerimi Kaydet';
                });
            });
            </script>

        <?php endif; ?>

    </div>
</div>

<style>
    .ybs-profile-wrapper { background-color: #f1f5f9; min-height: 80vh; padding: 100px 20px 60px 20px; font-family: -apple-system, sans-serif; display: flex; justify-content: center; align-items: flex-start;}
    .ybs-profile-container { width: 100%; max-width: 850px; }
    
    .auth-box, .profile-box { background: #fff; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); padding: 40px; border: 1px solid #e2e8f0; }
    .auth-box { max-width: 450px; margin: 0 auto; }
    
    .auth-header, .profile-header { margin-bottom: 30px; }
    .auth-header h2 { margin: 0 0 10px 0; font-size: 24px; color: #0f172a; font-weight: 800; }
    .auth-header p { margin: 0; color: #64748b; font-size: 14px; }

    .profile-header { display: flex; align-items: center; gap: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 30px; }
    .profile-avatar img { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid #f1f5f9; box-shadow:0 4px 6px rgba(0,0,0,0.05); }
    .profile-title { flex: 1; }
    .profile-title h2 { margin: 0 0 5px 0; font-size: 24px; color: #0f172a; font-weight: 800; }
    .profile-title p { margin: 0; color: #64748b; font-size: 14px; }
    .btn-logout { background: #fee2e2; color: #b91c1c; text-decoration: none; padding: 10px 18px; border-radius: 6px; font-weight: 700; font-size: 13px; transition: 0.2s; border: 1px solid #fecaca; display: inline-block; }
    .btn-logout:hover { background: #fecaca; }

    .section-title { font-size: 18px; color: #0284c7; margin: 35px 0 20px 0; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
    
    .ybs-form .form-group { margin-bottom: 20px; }
    .ybs-form label { display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 8px; text-transform:uppercase; letter-spacing:0.5px;}
    .ybs-form .form-control { width: 100%; padding: 14px 15px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; transition: all 0.2s; background:#f8fafc; color:#0f172a; }
    .ybs-form .form-control:focus { outline: none; border-color: #0284c7; background: #fff; box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1); }
    
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 20px; }
    
    .file-upload-box { background: #f8fafc; padding: 20px; border-radius: 8px; border: 2px dashed #94a3b8; }
    .file-upload-box input[type="file"] { width:100%; padding:10px; background:#fff; border:1px solid #cbd5e1; border-radius:6px; cursor:pointer;}

    .form-options { display: flex; justify-content: space-between; align-items: center; font-size: 13px; margin-bottom: 25px; }
    .forgot-link { color: #0284c7; text-decoration: none; font-weight: 600; }
    
    .btn-primary { width: 100%; padding: 16px; background: #0ea5e9; color: #fff; border: none; border-radius: 8px; font-weight: 800; font-size: 15px; text-transform:uppercase; letter-spacing:1px; cursor: pointer; transition: 0.2s; }
    .btn-primary:hover { background: #0284c7; transform:translateY(-2px); box-shadow:0 4px 12px rgba(2,132,199,0.3); }
    .btn-primary:disabled { background: #94a3b8; cursor: not-allowed; transform:none; box-shadow:none;}

    @media (max-width: 600px) {
        .form-grid { grid-template-columns: 1fr; gap: 0; }
        .profile-header { flex-direction: column; text-align: center; }
        .auth-box, .profile-box { padding: 25px 20px; }
    }
</style>

<?php get_footer(); ?>
<?php
/* Template Name: Üye Kayıt Formu */
get_header(); 

// Departmanları Çek
$depts = get_posts(['post_type'=>'departman', 'numberposts'=>-1]);
?>
<style>
    header, footer{ display: none !important; }
</style>

<div class="registration-page-wrapper">
    <div class="reg-container">
        
        <div class="reg-header">
            <h1>Topluluk Üyesi Bilgi Formu</h1>
            <p>Topluluk üyeliğinizi başlatmak veya güncellemek için formu eksiksiz doldurun.</p>
        </div>

        <div id="success-screen" style="display: none; padding: 60px 30px; text-align: center;">
            <div style="font-size: 80px; margin-bottom: 20px;">🎉</div>
            <h2 style="color: #0f172a; margin-bottom: 15px; font-size: 28px; font-weight: 800;">Başvurunuz Alındı!</h2>
            <p id="success-message" style="color: #475569; font-size: 16px; line-height: 1.6; max-width: 500px; margin: 0 auto 30px auto;"></p>
            <a href="<?php echo site_url(); ?>" style="display: inline-block; background: #0ea5e9; color: #fff; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: bold; transition: all 0.2s;">Ana Sayfaya Dön</a>
        </div>

        <form id="frontend-register-form" method="POST" enctype="multipart/form-data">
            <?php wp_nonce_field('ybs_ajax_nonce', 'security'); ?>
            <input type="hidden" name="action" value="ybs_frontend_register_ajax">

            <div class="form-section">
                <h3>Kişisel Bilgiler</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Ad Soyad <span class="req">*</span></label>
                        <input type="text" name="fullname" required placeholder="Adınız ve Soyadınız">
                    </div>
                    <div class="form-group">
                        <label>E-Posta <span class="req">*</span></label>
                        <input type="email" name="email" required placeholder="ornek@okul.edu.tr">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>T.C. Kimlik Numarası <span class="req">*</span></label>
                        <input type="text" name="ybs_tc_kimlik" required maxlength="11" pattern="\d{11}" placeholder="11 Haneli" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        <small class="form-note">Sadece organizasyon yönetimi için kullanılır.</small>
                    </div>
                    <div class="form-group">
                        <label>Doğum Tarihi <span class="req">*</span></label>
                        <input type="date" name="ybs_dogum_tarihi" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Öğrenci Numarası (9 Hane)</label>
                        <input type="text" name="student_no" maxlength="9" pattern="\d{9}" placeholder="Örn: 202612345" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div class="form-group">
                        <label>Telefon <span class="req">*</span></label>
                        <input type="text" name="phone" required placeholder="05XX...">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>İkamet Şehri <span class="req">*</span></label>
                        <input type="text" name="ybs_sehir" required placeholder="Örn: Düzce">
                    </div>
                    <div class="form-group">
                        <label>LinkedIn Profil Linki <span class="req">*</span></label>
                        <input type="url" name="linkedin" required placeholder="https://linkedin.com/in/...">
                    </div>
                </div>

                <div class="form-group">
                    <label>Tişört Bedeni <span class="req">*</span></label>
                    <select name="ybs_beden" required>
                        <option value="">Seçiniz...</option>
                        <option value="XS">XS</option>
                        <option value="S">S</option>
                        <option value="M">M</option>
                        <option value="L">L</option>
                        <option value="XL">XL</option>
                        <option value="XXL">XXL</option>
                    </select>
                </div>
            </div>

            <div class="form-section">
                <h3>Organizasyon</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Mevcut Departmanınız <span class="req">*</span></label>
                        <select name="department_name" required>
                            <option value="">Seçiniz...</option>
                            <?php foreach($depts as $dept): ?>
                                <option value="<?php echo esc_attr($dept->post_title); ?>"><?php echo $dept->post_title; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Görev Tanımınız <span class="req">*</span></label>
                        <input type="text" name="duty_title" required placeholder="Örn: İçerik Üreticisi, Koordinatör vb.">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>Acil Durum Bilgileri</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Ulaşılacak Kişi (Ad Soyad) <span class="req">*</span></label>
                        <input type="text" name="ybs_acil_kisi" required>
                    </div>
                    <div class="form-group">
                        <label>Acil Durum Telefonu <span class="req">*</span></label>
                        <input type="text" name="ybs_acil_telefon" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Yakınlık Derecesi</label>
                    <input type="text" name="ybs_acil_yakinlik" placeholder="Örn: Annem">
                </div>
            </div>

            <div class="form-section">
                <h3>Sağlık Bilgileri</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Kan Grubu</label>
                        <select name="blood_type">
                            <option value="">Bilinmiyor</option>
                            <option value="0 Rh+">0 Rh+</option><option value="0 Rh-">0 Rh-</option>
                            <option value="A Rh+">A Rh+</option><option value="A Rh-">A Rh-</option>
                            <option value="B Rh+">B Rh+</option><option value="B Rh-">B Rh-</option>
                            <option value="AB Rh+">AB Rh+</option><option value="AB Rh-">AB Rh-</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Özel Beslenme Durumu</label>
                        <select name="ybs_beslenme">
                            <option value="Yok">Yok</option>
                            <option value="Vejetaryen">Vejetaryen</option>
                            <option value="Vegan">Vegan</option>
                            <option value="Glutensiz">Glutensiz</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Alerjen / Sürekli İlaç / Kronik Rahatsızlık</label>
                    <input type="text" name="health_notes" placeholder="Varsa lütfen belirtiniz">
                </div>
            </div>

            <div class="form-section">
                <h3>Kariyer Dosyaları</h3>
                <div class="file-upload-box">
                    <div class="form-group">
                        <label>Profil Fotoğrafı (Zorunlu) <span class="req">*</span></label>
                        <input type="file" name="profile_photo" id="profile_photo" accept="image/jpeg, image/png" required>
                        <small class="form-note">Yaka kartı basımı ve web sitesi için net bir fotoğraf yükleyin (Maks 2MB).</small>
                    </div>
                    <div class="form-group" style="margin-top:15px;">
                        <label>CV / Özgeçmiş (Zorunlu PDF) <span class="req">*</span></label>
                        <input type="file" name="cv_file" id="cv_file" accept=".pdf" required>
                        <small class="form-note">Şirketlere sunulmak üzere detaylı CV'nizi yükleyin (Sadece PDF, Maks 5MB).</small>
                    </div>
                </div>
            </div>

            <div class="form-section kvkk-section">
                <strong>KVKK ve Açık Rıza Beyanı</strong>
                <p>Fotoğrafımın, özgeçmişimin ve CV'min web sitenizde yayımlanarak şirketler tarafından daha görünür ve erişilebilir hale getirilmesini, bu kapsamda paylaştığım kişisel verilerin yalnızca bu amaç doğrultusunda işlenmesini kabul ediyor; verdiğim bilgilerin doğru olduğunu beyan ederim.</p>
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <input type="checkbox" name="ybs_kvkk_onay" value="1" required style="width:auto; transform:scale(1.2);">
                    <span style="font-weight:bold;">Yukarıdaki şartları okudum ve kabul ediyorum. <span class="req">*</span></span>
                </label>
            </div>

            <div class="form-footer">
                <button type="submit" id="btn-submit-reg" class="submit-btn">Kaydı Tamamla</button>
            </div>
            
            <div id="reg-response"></div>

        </form>
    </div>
</div>

<style>
    .registration-page-wrapper { background: #f1f5f9; padding: 60px 20px; min-height: 100vh; font-family: -apple-system, sans-serif; }
    .reg-container { max-width: 750px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); overflow: hidden; }
    
    .reg-header { background: #0f172a; color: #fff; padding: 40px 30px; text-align: center; }
    .reg-header h1 { margin: 0; font-size: 26px; font-weight: 800; color: #fff; letter-spacing: 0.5px;}
    .reg-header p { margin: 10px 0 0 0; opacity: 0.8; font-size: 15px; }

    #frontend-register-form { padding: 40px; }
    
    .form-section { margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 25px; }
    .form-section:last-child { border-bottom: none; }
    .form-section h3 { font-size: 18px; color: #0284c7; margin-bottom: 20px; font-weight: 700; display: flex; align-items: center; gap: 8px; margin-top:0;}
    
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media(max-width:600px) { .form-row { grid-template-columns: 1fr; } }
    
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #334155; font-size: 13px; text-transform:uppercase; letter-spacing:0.5px;}
    .req { color: #e11d48; }
    .form-note { display: block; margin-top: 4px; color: #64748b; font-size: 12px; }
    
    input[type="text"], input[type="email"], input[type="url"], input[type="date"], select {
        width: 100%; padding: 13px 15px; border: 1.5px solid #cbd5e1; border-radius: 8px; 
        font-size: 15px; transition: all 0.2s; background: #f8fafc; color:#0f172a; box-sizing:border-box;
    }
    input:focus, select:focus { border-color: #0284c7; background: #fff; outline: none; box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1); }
    
    .file-upload-box { background: #f8fafc; padding: 20px; border-radius: 8px; border: 2px dashed #94a3b8; }
    .file-upload-box input[type="file"] { width:100%; padding:10px; background:#fff; border:1px solid #cbd5e1; border-radius:6px; cursor:pointer;}

    .kvkk-section { background:#f0fdfa; padding:20px; border-radius:8px; border:1px solid #ccfbf1; font-size:13px; color:#166534; line-height:1.6;}
    .kvkk-section strong { display:block; margin-bottom:5px; font-size:14px; color:#14532d;}

    .submit-btn { 
        width: 100%; background: #0ea5e9; color: #fff; border: none; padding: 18px; 
        font-size: 16px; font-weight: 700; border-radius: 8px; cursor: pointer; transition: all 0.2s;
        text-transform:uppercase; letter-spacing:1px; margin-top:10px;
    }
    .submit-btn:hover { background: #0284c7; transform: translateY(-2px); box-shadow:0 4px 12px rgba(2,132,199,0.3);}
    .submit-btn:disabled { background: #94a3b8; cursor: not-allowed; transform: none; box-shadow:none;}

    .notice-box { margin-top: 20px; padding: 15px; border-radius: 8px; text-align: center; font-weight: 600; font-size:14px; line-height: 1.5;}
    .notice-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('frontend-register-form');
    const btn = document.getElementById('btn-submit-reg');
    const responseDiv = document.getElementById('reg-response');
    const successScreen = document.getElementById('success-screen');
    const successMessage = document.getElementById('success-message');

    // Dosya inputları
    const photoInput = document.getElementById('profile_photo');
    const cvInput = document.getElementById('cv_file');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); 
            responseDiv.innerHTML = '';

            // --- 1. DOSYA BOYUTU KONTROLÜ (Client-Side Validation) ---
            let hasError = false;
            let errorMsg = '';

            // Fotoğraf Kontrolü (Maks 2MB = 2 * 1024 * 1024 byte)
            if (photoInput.files.length > 0) {
                const photoSizeMB = photoInput.files[0].size / (1024 * 1024);
                if (photoSizeMB > 2) {
                    hasError = true;
                    errorMsg += '📸 Seçtiğiniz profil fotoğrafı çok büyük (' + photoSizeMB.toFixed(2) + ' MB). Maksimum 2 MB olmalıdır.<br>';
                }
            }

            // CV Kontrolü (Maks 5MB = 5 * 1024 * 1024 byte)
            if (cvInput.files.length > 0) {
                const cvSizeMB = cvInput.files[0].size / (1024 * 1024);
                if (cvSizeMB > 5) {
                    hasError = true;
                    errorMsg += '📄 Seçtiğiniz CV dosyası çok büyük (' + cvSizeMB.toFixed(2) + ' MB). Maksimum 5 MB olmalıdır.<br>';
                }
            }

            // Eğer hata varsa formu gönderme ve uyarı ver
            if (hasError) {
                responseDiv.innerHTML = '<div class="notice-box notice-error">' + errorMsg + 'Lütfen dosya boyutlarını küçültüp tekrar deneyin.</div>';
                window.scrollTo({ top: responseDiv.offsetTop - 50, behavior: 'smooth' });
                return; // İşlemi burada kes
            }
            // ---------------------------------------------------------

            // Butonu kilitle ve durumu değiştir
            btn.disabled = true;
            btn.innerText = 'Bilgileriniz Kaydediliyor, Lütfen Bekleyin...';

            const formData = new FormData(form);

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(res => {
                if(res.success) {
                    // BAŞARILI DURUM: Formu gizle, başarı ekranını aç, yukarı kaydır
                    form.style.display = 'none';
                    successScreen.style.display = 'block';
                    successMessage.innerText = res.data;
                    window.scrollTo({ top: 0, behavior: 'smooth' }); 
                } else {
                    // HATA DURUMU: Form açık kalır, hata mesajı basılır
                    responseDiv.innerHTML = '<div class="notice-box notice-error">⚠️ HATA: <br>' + res.data + '</div>';
                    btn.disabled = false;
                    btn.innerText = 'Tekrar Dene';
                }
            })
            .catch(error => {
                // SUNUCU KOPMASI DURUMU
                responseDiv.innerHTML = '<div class="notice-box notice-error">❌ Sunucu ile bağlantı kesildi. İnternet bağlantınızı kontrol edip tekrar deneyin.</div>';
                btn.disabled = false;
                btn.innerText = 'Tekrar Dene';
            });
        });
    }
});
</script>

<?php get_footer(); ?>
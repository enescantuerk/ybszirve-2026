<?php
/**
 * DÜYBS - Ekip Üyeleri Özel Profil Alanları
 * Bu dosya WordPress kullanıcı profili sayfasına özel alanları ve durum yönetimini ekler.
 */

// 1. Gerekli JS/Medya Kütüphanesini Çağır (Fotoğraf ve CV yüklemek için)
add_action('admin_enqueue_scripts', 'ybs_enqueue_media_uploader_for_users');
function ybs_enqueue_media_uploader_for_users($hook) {
    if ($hook == 'profile.php' || $hook == 'user-edit.php') {
        wp_enqueue_media();
    }
}

// 2. Özel Alanları Kullanıcı Profilinde Göster
add_action('show_user_profile', 'ybs_ekip_custom_user_fields');
add_action('edit_user_profile', 'ybs_ekip_custom_user_fields');

function ybs_ekip_custom_user_fields($user) {
    // Mevcut verileri çek
    $status = get_user_meta($user->ID, 'ybs_status', true);
    if(empty($status)) $status = 'aktif'; // Eski üyeler veya durumu olmayanlar aktif sayılır

    $tc_kimlik = get_user_meta($user->ID, 'ybs_tc_kimlik', true);
    $student_no = get_user_meta($user->ID, 'ybs_student_no', true);
    $dogum_tarihi = get_user_meta($user->ID, 'ybs_dogum_tarihi', true);
    $telefon = get_user_meta($user->ID, 'ybs_telefon', true);
    $linkedin = get_user_meta($user->ID, 'ybs_linkedin', true);
    $sehir = get_user_meta($user->ID, 'ybs_sehir', true);
    $fotograf = get_user_meta($user->ID, 'ybs_fotograf', true);
    $cv_dosyasi = get_user_meta($user->ID, 'ybs_cv_dosyasi', true);
    $beden = get_user_meta($user->ID, 'ybs_beden', true);

    $departman = get_user_meta($user->ID, 'ybs_departman', true);
    $gorev_tanimi = get_user_meta($user->ID, 'ybs_gorev_tanimi', true);
    $sorumlu_alan = get_user_meta($user->ID, 'ybs_sorumlu_alan', true);
    $tecrube = get_user_meta($user->ID, 'ybs_tecrube', true);

    $acil_kisi = get_user_meta($user->ID, 'ybs_acil_kisi', true);
    $acil_yakinlik = get_user_meta($user->ID, 'ybs_acil_yakinlik', true);
    $acil_telefon = get_user_meta($user->ID, 'ybs_acil_telefon', true);

    $kan_grubu = get_user_meta($user->ID, 'ybs_kan_grubu', true);
    $ilac_durumu = get_user_meta($user->ID, 'ybs_ilac_durumu', true);
    $ilac_detay = get_user_meta($user->ID, 'ybs_ilac_detay', true);
    $kronik = get_user_meta($user->ID, 'ybs_kronik', true);
    $alerjen = get_user_meta($user->ID, 'ybs_alerjen', true);
    $beslenme = get_user_meta($user->ID, 'ybs_beslenme', true);

    $kvkk = get_user_meta($user->ID, 'ybs_kvkk_onay', true);

    ?>
    <style>
        .ybs-section-title { background: #111827; color: #fff; padding: 10px 15px; border-radius: 4px; margin-top: 40px; margin-bottom: 10px; font-weight: bold; }
        .ybs-info-text { font-size: 13px; color: #6b7280; margin-top: 5px; font-style: italic; }
        .ybs-kvkk-box { background: #f9fafb; border-left: 4px solid #3b82f6; padding: 15px; margin-top: 10px; font-size: 13px; color: #374151; }
        .ybs-status-box { background: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; margin-bottom: 20px; font-size: 14px; }
    </style>

    <h2 class="ybs-section-title" style="background:#f59e0b; color:#000;">SİSTEM DURUMU</h2>
    <div class="ybs-status-box">
        <label for="ybs_status" style="font-weight:bold; margin-right:10px;">Üyenin Durumu:</label>
        <select name="ybs_status" id="ybs_status" style="padding:5px 15px;">
            <option value="aktif" <?php selected($status, 'aktif'); ?>>Aktif Üye</option>
            <option value="beklemede" <?php selected($status, 'beklemede'); ?>>Beklemede (Yeni Başvuru)</option>
            <option value="pasif" <?php selected($status, 'pasif'); ?>>Pasif Üye</option>
        </select>
        <p class="description" style="margin-top:5px;">Ön yüzden (siteden) yapılan yeni başvurular otomatik olarak <strong>Beklemede</strong> statüsünde düşer. Bu alandan veya üye listesinden onaylayabilirsiniz.</p>
    </div>

    <h2 class="ybs-section-title">🔹 BÖLÜM 1: KİŞİSEL BİLGİLER</h2>
    <p class="ybs-info-text">Not: İsim, Soyisim ve E-Posta adresleri sayfanın en üstündeki standart WordPress alanlarından düzenlenmektedir.</p>
    <table class="form-table">
        <tr>
            <th><label for="ybs_tc_kimlik">T.C. Kimlik Numarası *</label></th>
            <td>
                <input type="text" name="ybs_tc_kimlik" id="ybs_tc_kimlik" value="<?php echo esc_attr($tc_kimlik); ?>" class="regular-text" required maxlength="11">
                <p class="description">Sadece organizasyon ve acil durum yönetimi için kullanılacaktır.</p>
            </td>
        </tr>
        <tr>
            <th><label for="ybs_student_no">Öğrenci Numarası</label></th>
            <td><input type="text" name="ybs_student_no" id="ybs_student_no" value="<?php echo esc_attr($student_no); ?>" class="regular-text" maxlength="9"></td>
        </tr>
        <tr>
            <th><label for="ybs_dogum_tarihi">Doğum Tarihi *</label></th>
            <td><input type="date" name="ybs_dogum_tarihi" id="ybs_dogum_tarihi" value="<?php echo esc_attr($dogum_tarihi); ?>" class="regular-text" required></td>
        </tr>
        <tr>
            <th><label for="ybs_telefon">Telefon Numarası *</label></th>
            <td><input type="tel" name="ybs_telefon" id="ybs_telefon" value="<?php echo esc_attr($telefon); ?>" class="regular-text" required></td>
        </tr>
        <tr>
            <th><label for="ybs_sehir">İkamet Şehri *</label></th>
            <td><input type="text" name="ybs_sehir" id="ybs_sehir" value="<?php echo esc_attr($sehir); ?>" class="regular-text" required></td>
        </tr>
        <tr>
            <th><label for="ybs_linkedin">LinkedIn Profil Adresi *</label></th>
            <td><input type="url" name="ybs_linkedin" id="ybs_linkedin" value="<?php echo esc_url($linkedin); ?>" class="regular-text" required placeholder="https://linkedin.com/in/..."></td>
        </tr>
        <tr>
            <th><label for="ybs_fotograf">1 Adet Güncel Fotoğraf *</label></th>
            <td>
                <input type="url" name="ybs_fotograf" id="ybs_fotograf" value="<?php echo esc_url($fotograf); ?>" class="regular-text">
                <button type="button" class="button ybs-media-upload" data-target="ybs_fotograf">Görsel Seç / Yükle</button>
                <?php if($fotograf): ?><br><img src="<?php echo esc_url($fotograf); ?>" style="max-width: 100px; height: auto; margin-top: 10px; border-radius:6px;"><?php endif; ?>
            </td>
        </tr>
        <tr>
            <th><label for="ybs_cv_dosyasi">CV Yükleyiniz *</label></th>
            <td>
                <input type="url" name="ybs_cv_dosyasi" id="ybs_cv_dosyasi" value="<?php echo esc_url($cv_dosyasi); ?>" class="regular-text">
                <button type="button" class="button ybs-media-upload" data-target="ybs_cv_dosyasi">PDF Seç / Yükle</button>
                <?php if($cv_dosyasi): ?><a href="<?php echo esc_url($cv_dosyasi); ?>" target="_blank" style="margin-left:10px;">Mevcut CV'yi Görüntüle</a><?php endif; ?>
            </td>
        </tr>
        <tr>
            <th><label for="ybs_beden">Tişört Bedeni *</label></th>
            <td>
                <select name="ybs_beden" id="ybs_beden" required>
                    <option value="">Seçiniz...</option>
                    <?php 
                    $bedenler = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
                    foreach($bedenler as $b) {
                        $selected = ($beden == $b) ? 'selected' : '';
                        echo "<option value='{$b}' {$selected}>{$b}</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
    </table>

    <h2 class="ybs-section-title">🔹 BÖLÜM 2: ORGANİZASYON BİLGİLERİ</h2>
    <table class="form-table">
        <tr>
            <th><label for="ybs_departman">Departmanınız *</label></th>
            <td><input type="text" name="ybs_departman" id="ybs_departman" value="<?php echo esc_attr($departman); ?>" class="regular-text" required></td>
        </tr>
        <tr>
            <th><label for="ybs_gorev_tanimi">Görev Tanımınız *</label></th>
            <td><input type="text" name="ybs_gorev_tanimi" id="ybs_gorev_tanimi" value="<?php echo esc_attr($gorev_tanimi); ?>" class="regular-text" required></td>
        </tr>
        <tr>
            <th><label for="ybs_sorumlu_alan">Sorumlu Olduğunuz Alan</label></th>
            <td><input type="text" name="ybs_sorumlu_alan" id="ybs_sorumlu_alan" value="<?php echo esc_attr($sorumlu_alan); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="ybs_tecrube">Daha önce benzer tecrübeniz var mı?</label></th>
            <td>
                <select name="ybs_tecrube" id="ybs_tecrube">
                    <option value="">Seçiniz...</option>
                    <option value="Evet" <?php selected($tecrube, 'Evet'); ?>>Evet</option>
                    <option value="Hayır" <?php selected($tecrube, 'Hayır'); ?>>Hayır</option>
                </select>
            </td>
        </tr>
    </table>

    <h2 class="ybs-section-title">🔹 BÖLÜM 3: ACİL DURUM BİLGİLERİ</h2>
    <table class="form-table">
        <tr>
            <th><label for="ybs_acil_kisi">Acil Ulaşılacak Kişi (Ad-Soyad) *</label></th>
            <td><input type="text" name="ybs_acil_kisi" id="ybs_acil_kisi" value="<?php echo esc_attr($acil_kisi); ?>" class="regular-text" required></td>
        </tr>
        <tr>
            <th><label for="ybs_acil_yakinlik">Yakınlık Derecesi</label></th>
            <td><input type="text" name="ybs_acil_yakinlik" id="ybs_acil_yakinlik" value="<?php echo esc_attr($acil_yakinlik); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="ybs_acil_telefon">Acil Durum İletişim Numarası *</label></th>
            <td><input type="tel" name="ybs_acil_telefon" id="ybs_acil_telefon" value="<?php echo esc_attr($acil_telefon); ?>" class="regular-text" required></td>
        </tr>
    </table>

    <h2 class="ybs-section-title">🔹 BÖLÜM 4: SAĞLIK BİLGİLERİ</h2>
    <table class="form-table">
        <tr>
            <th><label for="ybs_kan_grubu">Kan Grubunuz</label></th>
            <td>
                <select name="ybs_kan_grubu" id="ybs_kan_grubu">
                    <option value="">Seçiniz...</option>
                    <?php 
                    $kanlar = ['A Rh+', 'A Rh-', 'B Rh+', 'B Rh-', 'AB Rh+', 'AB Rh-', '0 Rh+', '0 Rh-', 'Bilmiyorum'];
                    foreach($kanlar as $k) {
                        $selected = ($kan_grubu == $k) ? 'selected' : '';
                        echo "<option value='{$k}' {$selected}>{$k}</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="ybs_ilac_durumu">Sürekli Kullandığınız İlaç Var Mı?</label></th>
            <td>
                <select name="ybs_ilac_durumu" id="ybs_ilac_durumu">
                    <option value="Hayır" <?php selected($ilac_durumu, 'Hayır'); ?>>Hayır</option>
                    <option value="Evet" <?php selected($ilac_durumu, 'Evet'); ?>>Evet (Aşağıda Belirtin)</option>
                </select>
                <br>
                <textarea name="ybs_ilac_detay" rows="2" class="regular-text" placeholder="Varsa ilaç isimleri..." style="margin-top:5px;"><?php echo esc_textarea($ilac_detay); ?></textarea>
            </td>
        </tr>
        <tr>
            <th><label for="ybs_kronik">Bilinen Kronik Rahatsızlığınız Var Mı?</label></th>
            <td><input type="text" name="ybs_kronik" id="ybs_kronik" value="<?php echo esc_attr($kronik); ?>" class="regular-text" placeholder="Yoksa 'Hayır' yazınız."></td>
        </tr>
        <tr>
            <th><label for="ybs_alerjen">Alerjen Bilginiz Var Mı? (Gıda/İlaç vb.)</label></th>
            <td><textarea name="ybs_alerjen" rows="2" class="regular-text"><?php echo esc_textarea($alerjen); ?></textarea></td>
        </tr>
        <tr>
            <th><label for="ybs_beslenme">Özel Beslenme Durumu</label></th>
            <td>
                <select name="ybs_beslenme" id="ybs_beslenme">
                    <option value="Yok" <?php selected($beslenme, 'Yok'); ?>>Yok (Standart)</option>
                    <option value="Vejetaryen" <?php selected($beslenme, 'Vejetaryen'); ?>>Vejetaryen</option>
                    <option value="Vegan" <?php selected($beslenme, 'Vegan'); ?>>Vegan</option>
                    <option value="Glutensiz" <?php selected($beslenme, 'Glutensiz'); ?>>Glutensiz</option>
                    <option value="Diğer" <?php selected($beslenme, 'Diğer'); ?>>Diğer</option>
                </select>
            </td>
        </tr>
    </table>

    <h2 class="ybs-section-title">🔹 BÖLÜM 6: KVKK VE BEYAN</h2>
    <div class="ybs-kvkk-box">
        <strong>Açık Rıza Metni:</strong><br>
        LinkedIn profil bağlantınız, güncel fotoğrafınız ve özgeçmişiniz; web sitemizde yayımlanarak şirketler tarafından daha kolay görüntülenebilmeniz, profesyonel görünürlüğünüzün artması ve kariyer fırsatlarına erişiminizin desteklenmesi amacıyla talep edilmektedir.<br><br>
        Fotoğrafımın, özgeçmişimin ve CV'min web sitenizde yayımlanarak şirketler tarafından daha görünür ve erişilebilir hale getirilmesini, bu kapsamda paylaştığım kişisel verilerin yalnızca bu amaç doğrultusunda işlenmesini kabul ediyor; fotoğraf ve video çekimlerinde yer almayı onayladığımı ve verdiğim bilgilerin doğru olduğunu beyan ederim.
    </div>
    <table class="form-table">
        <tr>
            <th><label for="ybs_kvkk_onay">Onay Durumu *</label></th>
            <td>
                <label>
                    <input type="checkbox" name="ybs_kvkk_onay" id="ybs_kvkk_onay" value="1" <?php checked($kvkk, 1); ?> required>
                    <strong>Yukarıdaki metni okudum ve onaylıyorum.</strong>
                </label>
            </td>
        </tr>
    </table>

    <script>
    jQuery(document).ready(function($){
        var mediaUploader;
        $('.ybs-media-upload').click(function(e) {
            e.preventDefault();
            var targetInput = $('#' + $(this).data('target'));
            
            if (mediaUploader) { mediaUploader.open(); return; }
            
            mediaUploader = wp.media.frames.file_frame = wp.media({
                title: 'Dosya Seç',
                button: { text: 'Bu Dosyayı Kullan' },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                targetInput.val(attachment.url);
            });
            
            mediaUploader.open();
        });
    });
    </script>
    <?php
}

// 3. Özel Alanları Veritabanına Kaydet
add_action('personal_options_update', 'ybs_ekip_save_custom_user_fields');
add_action('edit_user_profile_update', 'ybs_ekip_save_custom_user_fields');

function ybs_ekip_save_custom_user_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) { return false; }

    // Dizi (Array) halinde alınacak verilerin listesi (Status ve Öğrenci No eklendi)
    $fields = [
        'ybs_status', 'ybs_student_no', 'ybs_tc_kimlik', 'ybs_dogum_tarihi', 
        'ybs_telefon', 'ybs_linkedin', 'ybs_sehir', 'ybs_fotograf', 
        'ybs_cv_dosyasi', 'ybs_beden', 'ybs_departman', 'ybs_gorev_tanimi', 
        'ybs_sorumlu_alan', 'ybs_tecrube', 'ybs_acil_kisi', 'ybs_acil_yakinlik', 
        'ybs_acil_telefon', 'ybs_kan_grubu', 'ybs_ilac_durumu', 'ybs_ilac_detay', 
        'ybs_kronik', 'ybs_alerjen', 'ybs_beslenme'
    ];

    // Text verilerini döngü ile kaydet
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
        }
    }

    // KVKK Checkbox (İşaretlenmemişse 0 kaydet)
    $kvkk = isset($_POST['ybs_kvkk_onay']) ? 1 : 0;
    update_user_meta($user_id, 'ybs_kvkk_onay', $kvkk);
}
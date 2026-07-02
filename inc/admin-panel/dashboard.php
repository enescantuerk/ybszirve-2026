<div class="wrap">
    <h1 class="wp-heading-inline">Topluluk Yönetim Paneli</h1>
    <hr class="wp-header-end">

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 30px;">
        
        <div class="card" style="padding: 20px; border-left: 4px solid #00B5AD;">
            <h2 style="margin-top:0; font-size: 1.2em; color: #64748b;">Toplam Üye</h2>
            <p style="font-size: 2.5em; font-weight: bold; margin: 10px 0; color: #002855;">
                <?php $result = count_users(); echo $result['avail_roles']['topluluk_uyesi'] ?? 0; ?>
            </p>
            <button id="btn-open-member-modal" class="button button-primary">Hızlı Üye Ekle +</button>
        </div>

        <div class="card" style="padding: 20px; border-left: 4px solid #6f42c1;">
            <h2 style="margin-top:0; font-size: 1.2em; color: #64748b;">Departmanlar</h2>
            <p style="font-size: 2.5em; font-weight: bold; margin: 10px 0; color: #002855;">
                <?php echo wp_count_posts('departman')->publish; ?>
            </p>
            <button id="btn-open-dept-modal" class="button button-primary">Departman Ekle +</button>
        </div>

        <div class="card" style="padding: 20px; border-left: 4px solid #eab308;">
            <h2 style="margin-top:0; font-size: 1.2em; color: #64748b;">Yüklü Dosyalar</h2>
            <p style="font-size: 2.5em; font-weight: bold; margin: 10px 0; color: #002855;">
                <?php echo wp_count_posts('attachment')->inherit; ?>
            </p>
            <a href="upload.php" class="button button-primary">Medya Kütüphanesi</a>
        </div>
    </div>

    <h2 style="margin-top: 40px;">Departman Durumları</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr><th>Departman Adı</th><th>Üye Sayısı</th><th>İşlemler</th></tr>
        </thead>
        <tbody>
            <?php
            $depts = get_posts(array('post_type'=>'departman', 'numberposts'=>-1));
            if($depts): foreach($depts as $dept):
                // Yeni user-fields sisteminde departman ismini tutuyoruz, ID'yi değil.
                // Bu yüzden isimle arama yapıyoruz.
                $count = count(get_users(array('meta_key' => 'ybs_departman', 'meta_value' => $dept->post_title)));
            ?>
            <tr>
                <td><strong><?php echo $dept->post_title; ?></strong></td>
                <td><span class="badge" style="background: #e5e7eb; padding: 3px 8px; border-radius: 10px; font-weight:bold;"><?php echo $count; ?> Üye</span></td>
                <td><a href="<?php echo get_edit_post_link($dept->ID); ?>">Detay</a></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div id="ybs-member-modal" class="ybs-modal-overlay">
    <div class="ybs-modal-content large-modal">
        <div class="ybs-modal-header">
            <h3>Hızlı Üye Kaydı</h3>
            <span class="ybs-close-modal">&times;</span>
        </div>
        
        <div class="ybs-modal-body">
            <p style="font-size:13px; color:#666; margin-top:0;">Bu ekrandan sadece temel kayıt açılır. TC Kimlik, CV, Fotoğraf gibi detaylı bilgileri üyenin kendisi sisteme girip Profilim sekmesinden doldurmalıdır.</p>
            
            <form id="form-add-member" enctype="multipart/form-data">
                <input type="hidden" name="action" value="ybs_add_member_ajax">
                <?php wp_nonce_field('ybs_ajax_nonce', 'security'); ?>
                
                <div class="modal-grid">
                    
                    <div class="grid-col">
                        <h4 class="section-title">👤 Temel Bilgiler</h4>
                        
                        <div class="form-group">
                            <label>Ad Soyad *</label>
                            <input type="text" name="fullname" required class="widefat">
                        </div>
                        <div class="form-group">
                            <label>E-Posta *</label>
                            <input type="email" name="email" required class="widefat">
                        </div>
                        <div class="form-group">
                            <label>Telefon</label>
                            <input type="text" name="phone" class="widefat" placeholder="05XX...">
                        </div>
                    </div>

                    <div class="grid-col">
                        <h4 class="section-title">🚀 Organizasyon Görevi</h4>
                        
                        <div class="form-group">
                            <label>Departman</label>
                            <select name="department_name" class="widefat">
                                <option value="">-- Seçiniz --</option>
                                <?php foreach($depts as $dept): ?><option value="<?php echo esc_attr($dept->post_title); ?>"><?php echo $dept->post_title; ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Görev Tanımı</label>
                            <input type="text" name="duty_title" class="widefat" placeholder="Örn: İçerik Üreticisi">
                        </div>

                        <div class="form-group checkbox-group" style="margin-top:15px; border-top:1px solid #eee; padding-top:15px;">
                            <label><input type="checkbox" name="ybs_is_asil" value="1"> Asil Üye (Oylama Hakkı)</label>
                        </div>
                    </div>
                </div>

                <div class="form-footer">
                    <button type="submit" class="button button-primary button-large">Hesabı Oluştur</button>
                </div>
            </form>
            <div id="member-response" style="margin-top:10px;"></div>
        </div>
    </div>
</div>

<div id="ybs-dept-modal" class="ybs-modal-overlay">
    <div class="ybs-modal-content">
        <div class="ybs-modal-header">
            <h3>Yeni Departman</h3>
            <span class="ybs-close-modal">&times;</span>
        </div>
        <div class="ybs-modal-body">
            <form id="form-add-dept">
                <input type="hidden" name="action" value="ybs_add_dept_ajax">
                <?php wp_nonce_field('ybs_ajax_nonce', 'security'); ?>
                <div class="form-group">
                    <label>Departman Adı</label>
                    <input type="text" name="dept_name" required class="widefat">
                </div>
                <div class="form-group">
                    <label>Açıklama</label>
                    <textarea name="dept_desc" class="widefat" rows="2"></textarea>
                </div>
                <div class="form-footer">
                    <button type="submit" class="button button-primary">Oluştur</button>
                </div>
            </form>
            <div id="dept-response" style="margin-top:10px;"></div>
        </div>
    </div>
</div>

<style>
    .ybs-modal-overlay { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(2px); }
    .ybs-modal-content { background-color: #fff; margin: 5% auto; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 400px; animation: slideDown 0.3s ease; overflow:hidden; }
    .ybs-modal-content.large-modal { width: 750px; max-width: 95%; }
    .ybs-modal-header { padding: 15px 20px; background: #f8f9fa; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
    .ybs-modal-header h3 { margin: 0; font-size: 1.1em; color: #002855; }
    .ybs-close-modal { cursor: pointer; font-size: 24px; font-weight: bold; color: #888; }
    .ybs-modal-body { padding: 20px; }
    
    .modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
    @media(max-width:768px) { .modal-grid { grid-template-columns: 1fr; } }
    
    .section-title { font-size: 1.1em; color: #00B5AD; border-bottom: 2px solid #f0f0f1; padding-bottom: 5px; margin-bottom: 15px; margin-top:0; font-weight: 600; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 13px; }
    .checkbox-group label { display: inline-block; margin-right: 15px; font-weight: normal; }
    .form-footer { margin-top: 10px; text-align: right; border-top: 1px solid #eee; padding-top: 15px; }
    
    @keyframes slideDown { from {transform: translateY(-30px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
</style>

<script>
jQuery(document).ready(function($) {
    $('#btn-open-member-modal').click(function(e) { e.preventDefault(); $('#ybs-member-modal').fadeIn(200); });
    $('#btn-open-dept-modal').click(function(e) { e.preventDefault(); $('#ybs-dept-modal').fadeIn(200); });
    $('.ybs-close-modal').click(function() { $('.ybs-modal-overlay').fadeOut(200); });
    $(window).click(function(e) { if ($(e.target).hasClass('ybs-modal-overlay')) { $('.ybs-modal-overlay').fadeOut(200); } });

    // ÜYE EKLEME (AJAX - FormData)
    $('#form-add-member').submit(function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button');
        var responseDiv = $('#member-response');
        var formData = new FormData(this);

        btn.prop('disabled', true).text('Kaydediliyor...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false, contentType: false,
            success: function(res) {
                if(res.success) {
                    responseDiv.html('<div class="notice notice-success inline"><p>'+res.data+'</p></div>');
                    form[0].reset();
                    setTimeout(function(){ location.reload(); }, 1500);
                } else {
                    responseDiv.html('<div class="notice notice-error inline"><p>'+res.data+'</p></div>');
                }
                btn.prop('disabled', false).text('Hesabı Oluştur');
            }
        });
    });

    // DEPARTMAN EKLEME (AJAX)
    $('#form-add-dept').submit(function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button');
        var responseDiv = $('#dept-response');
        btn.prop('disabled', true).text('İşleniyor...');
        $.post(ajaxurl, form.serialize(), function(res) {
            if(res.success) {
                responseDiv.html('<div class="notice notice-success inline"><p>'+res.data+'</p></div>');
                form[0].reset();
                setTimeout(function(){ location.reload(); }, 1000);
            } else {
                responseDiv.html('<div class="notice notice-error inline"><p>'+res.data+'</p></div>');
            }
            btn.prop('disabled', false).text('Oluştur');
        });
    });
});
</script>
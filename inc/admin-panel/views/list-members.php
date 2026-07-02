<div class="wrap ybs-wrap">
    <div class="ybs-header">
        <h1 class="wp-heading-inline">Üye Yönetimi</h1>
        <button id="btn-open-member-modal" class="page-title-action">Hızlı Üye Ekle</button>
        <a href="<?php echo admin_url('admin.php?page=ybs-uyeler&export_org_team=1'); ?>" class="page-title-action" style="background:#2563eb; color:#fff; border-color:#1d4ed8;">📥 Organizasyon Ekibi Excel</a>
        <hr class="wp-header-end">
    </div>

    <div class="ybs-filter-bar">
        <div class="ybs-search-group">
            <span class="dashicons dashicons-search"></span>
            <input type="text" id="filter-search" placeholder="İsim, E-posta, Telefon..." class="regular-text">
        </div>
        
        <select id="filter-dept">
            <option value="">Tüm Departmanlar</option>
            <?php 
            $depts = get_posts(['post_type'=>'departman', 'numberposts'=>-1]);
            // FİLTRE DÜZELTİLDİ: Artık ID yerine isme göre arıyor (Yeni sisteme uygun)
            foreach($depts as $dept) echo "<option value='" . esc_attr($dept->post_title) . "'>{$dept->post_title}</option>"; 
            ?>
        </select>

        <select id="filter-status">
            <option value="">Tüm Statüler</option>
            <option value="asil">Asil Üye</option>
            <option value="yk">Yönetim Kurulu</option>
        </select>
        
        <span id="loading-indicator" class="ybs-loading" style="display:none; color:#666;">Yükleniyor...</span>
    </div>

    <div id="ybs-member-grid-container" class="ybs-grid">
        </div>
</div>

<div id="ybs-member-modal" class="ybs-modal-overlay">
    <div class="ybs-modal-box">
        <div class="ybs-modal-header">
            <h2 id="modal-title">Hızlı Üye Kaydı</h2>
            <button type="button" class="ybs-close-modal"><span class="dashicons dashicons-no-alt"></span></button>
        </div>
        
        <div class="ybs-modal-body">
            <p style="font-size:13px; color:#666; margin-top:0;">Bu ekrandan sadece temel kayıt açılır. TC Kimlik, CV, Fotoğraf gibi detaylı bilgileri üyenin kendisi sisteme girip Profilim sekmesinden doldurmalıdır.</p>
            
            <form id="form-member">
                <input type="hidden" name="action" id="form-action" value="ybs_add_member_ajax">
                <?php wp_nonce_field('ybs_ajax_nonce', 'security'); ?>
                
                <div class="ybs-form-layout">
                    <div class="ybs-col">
                        <h3 class="ybs-section-title">👤 Temel Bilgiler</h3>
                        
                        <label>Ad Soyad <span class="req">*</span></label>
                        <input type="text" name="fullname" required class="widefat">

                        <label>E-Posta <span class="req">*</span></label>
                        <input type="email" name="email" required class="widefat">

                        <label>Telefon</label>
                        <input type="text" name="phone" class="widefat" placeholder="05XX...">
                    </div>

                    <div class="ybs-col">
                        <h3 class="ybs-section-title">🚀 Organizasyon Görevi</h3>
                        
                        <label>Departman</label>
                        <select name="department_name" class="widefat">
                            <option value="">Seçiniz</option>
                            <?php foreach($depts as $dept): ?><option value="<?php echo esc_attr($dept->post_title); ?>"><?php echo $dept->post_title; ?></option><?php endforeach; ?>
                        </select>

                        <label>Görev Tanımı</label>
                        <input type="text" name="duty_title" class="widefat" placeholder="Örn: İçerik Üreticisi">

                        <div class="ybs-checkboxes" style="margin-top:15px; border-top:1px solid #eee; padding-top:15px;">
                            <label><input type="checkbox" name="ybs_is_asil" value="1"> Asil Üye (Oylama Hakkı)</label>
                        </div>
                    </div>
                </div>

                <div class="ybs-modal-footer" style="margin-top:20px; text-align:right;">
                    <button type="submit" class="button button-primary button-large" id="btn-save">Hesabı Oluştur</button>
                </div>
            </form>
            <div id="form-response" style="margin-top:10px;"></div>
        </div>
    </div>
</div>

<div id="member-detail-modal" class="ybs-modal-overlay">
    <div class="ybs-modal-box medium-box">
        <div class="ybs-modal-header">
            <h2>Üye Detayı</h2>
            <button type="button" class="ybs-close-detail"><span class="dashicons dashicons-no-alt"></span></button>
        </div>
        <div class="ybs-modal-body" id="detail-content-area">
            </div>
    </div>
</div>

<style>
    /* GENEL DÜZEN */
    .ybs-wrap { max-width: 100%; margin-top: 20px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    .ybs-header { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
    
    /* TOOLBAR */
    .ybs-filter-bar { background: #fff; border: 1px solid #c3c4c7; padding: 15px; border-radius: 6px; display: flex; gap: 15px; align-items: center; margin-bottom: 25px; flex-wrap: wrap; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .ybs-search-group { position: relative; flex: 1; min-width: 250px; }
    .ybs-search-group input { width: 100%; padding-left: 32px !important; border-radius: 4px; }
    .ybs-search-group span { position: absolute; top: 50%; left: 8px; transform: translateY(-50%); color: #646970; }
    .ybs-filter-bar select { border-radius: 4px; padding: 0 30px 0 10px; }

    /* GRID SİSTEMİ */
    .ybs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }

    /* KART TASARIMI */
    .ybs-card { background: #fff; border: 1px solid #c3c4c7; border-radius: 8px; display: flex; flex-direction: column; transition: all 0.2s ease; position: relative; box-shadow: 0 2px 5px rgba(0,0,0,0.05); overflow: hidden; }
    .ybs-card:hover { border-color: #2271b1; transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
    .yc-header { background: #f0f0f1; height: 80px; position: relative; border-bottom: 1px solid #e5e5e5; margin-bottom: 40px; }
    .yc-avatar { position: absolute; bottom: -35px; left: 50%; transform: translateX(-50%); width: 70px; height: 70px; border-radius: 50%; background: #fff; padding: 4px; border: 1px solid #c3c4c7; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .yc-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block; }
    .yc-badges { position: absolute; top: 10px; right: 10px; display: flex; gap: 5px; }
    .y-badge { font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
    .y-badge.asil { background: #007cba; color: #fff; }
    .y-badge.yk { background: #f0b849; color: #1d2327; }
    .yc-body { padding: 0 20px 20px 20px; text-align: center; flex: 1; }
    .yc-name { margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1d2327; }
    .yc-dept { display: inline-block; font-size: 12px; color: #2271b1; background: #f0f6fc; padding: 4px 10px; border-radius: 12px; font-weight: 600; margin-bottom: 15px; }
    .yc-contact { border-top: 1px solid #f0f0f1; padding-top: 12px; display: flex; flex-direction: column; gap: 8px; }
    .yc-contact-item { font-size: 13px; color: #50575e; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .yc-wa-icon { color: #25D366; text-decoration: none; transition: transform 0.2s; display: flex; align-items: center; }
    .yc-wa-icon:hover { transform: scale(1.2); }
    .yc-footer { background: #f6f7f7; border-top: 1px solid #f0f0f1; padding: 12px 15px; display: flex; gap: 10px; justify-content: center; }
    .yc-footer .button { flex: 1; justify-content: center; text-align: center; }

    /* MODAL DÜZENİ */
    .ybs-modal-overlay { z-index: 100000; background: rgba(0,0,0,0.6); backdrop-filter: blur(2px); display:none; position:fixed; top:0; left:0; width:100%; height:100%; align-items:center; justify-content:center; }
    .ybs-modal-box { background:#fff; width: 750px; max-width:95%; max-height:90vh; display:flex; flex-direction:column; box-shadow:0 15px 40px rgba(0,0,0,0.2); border-radius:8px; animation: slideDown 0.3s ease; }
    .ybs-modal-box.medium-box { width: 500px; }
    .ybs-modal-header { padding:15px 25px; border-bottom:1px solid #e5e5e5; display:flex; justify-content:space-between; align-items:center; background:#f8f9fa; border-radius: 8px 8px 0 0; }
    .ybs-modal-header h2 { margin: 0; font-size: 18px; color: #1d2327; }
    .ybs-close-modal, .ybs-close-detail { background:none; border:none; cursor: pointer; color: #646970; display:flex; align-items:center; padding:0; transition: color 0.2s; }
    .ybs-close-modal:hover, .ybs-close-detail:hover { color: #d63638; }
    .ybs-modal-body { padding:25px; overflow-y:auto; }
    .ybs-form-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
    .ybs-section-title { font-size: 14px; color: #007cba; border-bottom: 2px solid #f0f0f1; padding-bottom: 8px; margin-top:0; margin-bottom: 15px; font-weight: 600; text-transform: uppercase; }
    .ybs-col label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #3c434a; }
    .ybs-col input, .ybs-col select { margin-bottom: 15px; }
    .req { color: #d63638; }

    @media (max-width: 768px) {
        .ybs-form-layout { grid-template-columns: 1fr; gap: 15px; }
        .ybs-header { flex-direction: column; align-items: flex-start; }
    }
    @keyframes slideDown { from {transform: translateY(-20px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
</style>

<script>
jQuery(document).ready(function($) {
    
    // --- LİSTELEME VE FİLTRELEME ---
    function loadMembers() {
        $('#loading-indicator').show();
        $.post(ajaxurl, {
            action: 'ybs_filter_members_ajax',
            search: $('#filter-search').val(),
            dept: $('#filter-dept').val(),
            status: $('#filter-status').val(),
            security: '<?php echo wp_create_nonce("ybs_ajax_nonce"); ?>'
        }, function(res) {
            $('#ybs-member-grid-container').html(res.data);
            $('#loading-indicator').hide();
        });
    }
    
    // Sayfa açıldığında ilk yükleme
    loadMembers();
    
    // Arama ve Select kutularında değişiklik olunca anında filtrele
    $('#filter-search').on('keyup', function() {
        clearTimeout($.data(this, 'timer'));
        var wait = setTimeout(loadMembers, 400); // Yazarken sunucuyu yormamak için gecikme
        $(this).data('timer', wait);
    });
    $('#filter-dept, #filter-status').on('change', loadMembers);


    // --- MODAL İŞLEMLERİ ---
    function openModal(id) { $(id).css('display', 'flex').hide().fadeIn(200); }
    function closeModal() { $('.ybs-modal-overlay').fadeOut(200); }

    $('#btn-open-member-modal').click(function(e) {
        e.preventDefault();
        $('#form-member')[0].reset();
        $('#form-response').empty();
        openModal('#ybs-member-modal');
    });

    $('.ybs-close-modal, .ybs-close-detail').click(closeModal);
    $(window).click(function(e) { if ($(e.target).hasClass('ybs-modal-overlay')) closeModal(); });


    // --- YENİ ÜYE KAYDETME (AJAX) ---
    $('#form-member').submit(function(e) {
        e.preventDefault();
        var btn = $('#btn-save');
        var responseDiv = $('#form-response');
        
        btn.prop('disabled', true).text('İşleniyor...');
        
        $.ajax({
            url: ajaxurl, 
            type: 'POST', 
            data: new FormData(this),
            processData: false, 
            contentType: false,
            success: function(res) {
                if(res.success) {
                    responseDiv.html('<div class="notice notice-success inline" style="margin:0;"><p>'+res.data+'</p></div>');
                    loadMembers(); // Listeyi yenile
                    setTimeout(function() { 
                        closeModal(); 
                        $('#form-member')[0].reset();
                        responseDiv.empty(); 
                    }, 2000);
                } else {
                    responseDiv.html('<div class="notice notice-error inline" style="margin:0;"><p>'+res.data+'</p></div>');
                }
                btn.prop('disabled', false).text('Hesabı Oluştur');
            }
        });
    });


    // --- ÜYE DETAYINI GÖRÜNTÜLEME (POPUP İÇİNDE) ---
    $(document).on('click', '.btn-view-member', function(e) {
        e.preventDefault();
        var uid = $(this).data('id');
        $('#detail-content-area').html('<p style="text-align:center; padding:30px; color:#666;">Bilgiler çekiliyor...</p>');
        openModal('#member-detail-modal');
        
        $.post(ajaxurl, {
            action: 'ybs_get_member_detail_ajax',
            user_id: uid,
            security: '<?php echo wp_create_nonce("ybs_ajax_nonce"); ?>'
        }, function(res) {
            if(res.success) {
                $('#detail-content-area').html(res.data);
            } else {
                $('#detail-content-area').html('<p style="color:red; text-align:center;">Detaylar yüklenemedi.</p>');
            }
        });
    });
	
    $(document).on('click', '.btn-status-change', function(e) {
        e.preventDefault();
        var button = $(this);
        var userId = button.data('id');
        var newStatus = button.data('to');
        var originalText = button.text();
        button.text('İşleniyor...').prop('disabled', true);
        $.post(ajaxurl, {
            action: 'ybs_change_status_ajax',
            security: '<?php echo wp_create_nonce("ybs_ajax_nonce"); ?>',
            user_id: userId,
            new_status: newStatus
        }, function(response) {
            if (response.success) { location.reload(); }
            else { alert('Hata: ' + response.data); button.text(originalText).prop('disabled', false); }
        });
    });

});

</script>
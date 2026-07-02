<div class="wrap ybs-wrap">
    
    <div class="ybs-header">
        <h1 class="wp-heading-inline">Dosya Merkezi</h1>
        <div class="header-actions">
            <button id="btn-create-folder" class="ybs-btn ybs-btn-outline"><span class="dashicons dashicons-category"></span> Yeni Klasör</button>
            <button id="btn-upload-file" class="ybs-btn ybs-btn-primary"><span class="dashicons dashicons-upload"></span> Dosya Yükle</button>
        </div>
        <hr class="wp-header-end">
    </div>

    <div class="ybs-drive-toolbar">
        <div class="breadcrumb">
            <button id="btn-home" class="crumb-btn"><span class="dashicons dashicons-admin-home"></span> Ana Dizin</button>
            <span class="sep">/</span>
            <span id="current-path-display"></span>
        </div>
        <div id="loading-drive" style="display:none; color:#666; font-size:12px;">İşleniyor...</div>
    </div>

    <div id="ybs-drive-grid" class="ybs-drive-grid"></div>

    <input type="hidden" id="current-path" value="">
    <input type="file" id="hidden-file-input" style="display:none;" multiple>
</div>

<style>
    .ybs-wrap { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    .ybs-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .ybs-drive-toolbar { background: #fff; border: 1px solid #c3c4c7; padding: 10px 15px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .breadcrumb { display: flex; align-items: center; gap: 5px; font-size: 14px; }
    .crumb-btn { background: none; border: none; cursor: pointer; color: #2271b1; font-weight: 600; }
    .crumb-btn:hover { text-decoration: underline; }
    
    .ybs-drive-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 15px; }
    
    .drive-item { background: #fff; border: 1px solid #c3c4c7; border-radius: 6px; padding: 15px 10px; text-align: center; cursor: pointer; position: relative; height: 140px; display: flex; flex-direction: column; justify-content: center; align-items: center; transition: 0.2s; }
    .drive-item:hover { border-color: #2271b1; background: #f0f6fc; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    
    .d-icon { font-size: 48px; width: 48px; height: 48px; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; }
    .d-icon.folder { color: #f0b849; }
    .d-icon.file { color: #646970; }
    .d-icon.img { color: #b339b3; }
    .d-icon.doc { color: #2271b1; }
    .d-icon.pdf { color: #d63638; }

    .d-name { font-size: 12px; font-weight: 500; color: #1d2327; word-break: break-all; line-height: 1.3; overflow: hidden; max-height: 32px; }

    /* Silme Butonu */
    .d-delete { position: absolute; top: 5px; right: 5px; width: 20px; height: 20px; border-radius: 50%; background: #fff; border: 1px solid #d63638; color: #d63638; display: none; align-items: center; justify-content: center; font-size: 16px; z-index: 10; }
    .drive-item:hover .d-delete { display: flex; }
    .d-delete:hover { background: #d63638; color: #fff; }

    /* Görünürlük Butonu */
    .d-visibility { position: absolute; top: 5px; left: 5px; width: 24px; height: 24px; border-radius: 50%; background: #f0f0f1; border: 1px solid #ccc; color: #666; display: flex; align-items: center; justify-content: center; font-size: 14px; z-index: 10; }
    .d-visibility:hover { transform: scale(1.1); }
    .d-visibility.public { background: #d1fae5; border-color: #34d399; color: #059669; }

    .ybs-btn { padding: 8px 15px; border-radius: 4px; border: 1px solid transparent; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; font-weight: 600; font-size: 13px; }
    .ybs-btn-primary { background: #2271b1; color: #fff; }
    .ybs-btn-outline { border-color: #2271b1; color: #2271b1; background: #fff; }
</style>

<script>
jQuery(document).ready(function($) {
    
    // --- LİSTELE ---
    function loadDrive(path = '') {
        $('#loading-drive').show();
        $('#ybs-drive-grid').css('opacity', '0.6');

        $.post(ajaxurl, {
            action: 'ybs_drive_list_ajax',
            path: path,
            security: '<?php echo wp_create_nonce("ybs_ajax_nonce"); ?>' // Ajax.php ile aynı nonce
        }, function(res) {
            if(res.success) {
                $('#ybs-drive-grid').html(res.data.html);
                $('#current-path').val(res.data.current_path);
                $('#current-path-display').text(res.data.current_path);
            } else {
                alert('Hata: ' + res.data);
            }
            $('#loading-drive').hide();
            $('#ybs-drive-grid').css('opacity', '1');
        });
    }

    loadDrive(); // Başlat

    // --- NAVIGASYON ---
    $('#btn-home').click(function() { loadDrive(''); });
    
    $(document).on('click', '.drive-folder', function(e) {
        if($(e.target).closest('.d-delete, .d-visibility').length) return; // Butonlara basarsa girme
        var current = $('#current-path').val();
        var name = $(this).data('name');
        var newPath = current ? current + '/' + name : name;
        loadDrive(newPath);
    });

    $(document).on('click', '.drive-file', function(e) {
        if($(e.target).closest('.d-delete').length) return;
        window.open($(this).data('url'), '_blank');
    });

    // --- YENİ KLASÖR ---
    $('#btn-create-folder').click(function() {
        var name = prompt("Klasör Adı:");
        if(name) {
            $.post(ajaxurl, {
                action: 'ybs_drive_mkdir_ajax',
                path: $('#current-path').val(),
                name: name,
                security: '<?php echo wp_create_nonce("ybs_ajax_nonce"); ?>'
            }, function(res) {
                if(res.success) loadDrive($('#current-path').val());
                else alert(res.data);
            });
        }
    });

    // --- DOSYA YÜKLEME ---
    $('#btn-upload-file').click(function() { $('#hidden-file-input').click(); });

    $('#hidden-file-input').change(function() {
        if(this.files.length > 0) {
            var fd = new FormData();
            fd.append('action', 'ybs_drive_upload_ajax');
            fd.append('path', $('#current-path').val());
            fd.append('security', '<?php echo wp_create_nonce("ybs_ajax_nonce"); ?>');
            
            for(var i=0; i<this.files.length; i++) fd.append('files[]', this.files[i]);

            $('#loading-drive').text('Yükleniyor...').show();
            
            $.ajax({
                url: ajaxurl, type: 'POST', data: fd, processData: false, contentType: false,
                success: function(res) {
                    if(res.success) loadDrive($('#current-path').val());
                    else alert(res.data);
                    $('#loading-drive').text('İşleniyor...');
                }
            });
        }
    });

    // --- SİLME ---
    $(document).on('click', '.d-delete', function(e) {
        e.stopPropagation();
        if(confirm("Silmek istediğinize emin misiniz?")) {
            $.post(ajaxurl, {
                action: 'ybs_drive_delete_ajax',
                path: $('#current-path').val(),
                name: $(this).data('name'),
                type: $(this).data('type'),
                security: '<?php echo wp_create_nonce("ybs_ajax_nonce"); ?>'
            }, function(res) {
                if(res.success) loadDrive($('#current-path').val());
                else alert(res.data);
            });
        }
    });

    // --- GÖRÜNÜRLÜK (KİLİT) ---
    $(document).on('click', '.d-visibility', function(e) {
        e.stopPropagation();
        var btn = $(this);
        $.post(ajaxurl, {
            action: 'ybs_drive_toggle_visibility_ajax',
            path: btn.data('path'),
            security: '<?php echo wp_create_nonce("ybs_ajax_nonce"); ?>'
        }, function(res) {
            loadDrive($('#current-path').val());
        });
    });

});
</script>
<?php
/* Template Name: Bilet Sonuç Sayfası */

// Görevli ekranında yanlışlıkla yakınlaştırmayı engeller
function ybs_dogrula_viewport() {
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';
}
add_action('wp_head', 'ybs_dogrula_viewport', 1);

get_header(); 

global $wpdb;

// Veritabanı tablo adınız
$table_name = $wpdb->prefix . 'ybs_reservations';


$token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';

// --- BİLET SORGULAMA İŞLEMİ ---
$is_valid = false;
$user_name = '';
$seats = [];

if (!empty($token)) {
    // Token veritabanında var mı diye bakıyoruz
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE bilet_token = %s", $token));
    
    if ($results && count($results) > 0) {
        $is_valid = true;
        $user_name = $results[0]->user_name; 
        
        // Kişinin seçtiği tüm koltukları al
        foreach ($results as $row) {
            $seats[] = $row->seat_id; 
        }
    }
}
?>

<style>
    /* Temanın menü ve footer kısımlarını görevli ekranında gizliyoruz */
    header, footer, .site-header, .site-footer, #wpadminbar { display: none !important; } 
    html, body { 
        margin: 0 !important; 
        padding: 0 !important; 
        height: 100%; 
        background-color: #F0F2F5; 
    }

    .result-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 20px;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .header-logo {
        font-size: 1.2rem;
        font-weight: 900;
        color: #002855;
        margin-bottom: 25px;
        letter-spacing: -0.5px;
        text-align: center;
    }

    .result-card {
        width: 100%;
        max-width: 400px;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 40, 85, 0.1);
        text-align: center;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        padding-bottom: 25px;
    }

    .card-header {
        padding: 40px 20px 30px;
        color: #fff;
    }

    /* Durum Renkleri */
    .status-green .card-header { background: linear-gradient(135deg, #10b981, #059669); } 
    .status-red .card-header { background: linear-gradient(135deg, #ef4444, #dc2626); }   

    .status-icon {
        font-size: 65px;
        line-height: 1;
        margin-bottom: 15px;
        filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));
    }

    .status-title {
        font-size: 24px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0;
        color: #ffffff;
    }

    .card-body { padding: 30px 25px 30px; }

    .info-row {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 15px;
        text-align: left;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .info-row:last-child {
        margin-bottom: 0;
    }

    .info-label {
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 19px;
        font-weight: 800;
        color: #0f172a;
    }

    .seats-highlight {
        color: #00B5AD;
        font-size: 24px;
    }
    
    .error-desc {
        font-size: 15px;
        color: #475569;
        margin-bottom: 20px;
        font-weight: 600;
        line-height: 1.5;
    }
</style>

<div class="result-wrapper">
    <div class="header-logo">10. ULUSAL YBS ÖĞRENCİ ZİRVESİ</div>

    <?php if (empty($token)): ?>
        <div class="result-card" style="padding: 50px 20px;">
            <div style="font-size: 50px; margin-bottom: 15px;">🔍</div>
            <h3 style="color: #002855; margin:0 0 10px;">Bağlantı Bekleniyor</h3>
            <p style="color: #64748b; font-size: 15px; margin:0;">Lütfen QR okuyucu uygulamanızdan bir bilet taratın.</p>
        </div>

    <?php elseif (!$is_valid): ?>
        <div class="result-card status-red">
            <div class="card-header">
                <div class="status-icon">❌</div>
                <h2 class="status-title">GEÇERSİZ BİLET</h2>
            </div>
            <div class="card-body">
                <div class="error-desc" style="color: #dc2626;">Sistemde bu koda ait bir kayıt bulunamadı!</div>
                <div style="padding: 15px; background: #f8fafc; border-radius: 8px; font-family:monospace; font-size:12px; color:#94a3b8; word-break:break-all; border: 1px solid #e2e8f0;">
                    Okunan Kod:<br><strong style="color:#64748b; font-size:14px;"><?php echo esc_html($token); ?></strong>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="result-card status-green">
            <div class="card-header">
                <div class="status-icon">✅</div>
                <h2 class="status-title">GEÇERLİ BİLET</h2>
            </div>
            <div class="card-body">
                <div class="info-row" style="background: #f0fdf4; border-color: #bbf7d0;">
                    <span class="info-label" style="color: #166534;">Kayıtlı Kişi</span>
                    <div class="info-value" style="color: #14532d;"><?php echo esc_html($user_name); ?></div>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Satın Alınan Koltuklar (<?php echo count($seats); ?> Adet)</span>
                    <div class="info-value seats-highlight"><?php echo esc_html(implode(', ', $seats)); ?></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
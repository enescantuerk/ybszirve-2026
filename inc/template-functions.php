<?php
/**
 * YBS Zirvesi 2026 - Tema Fonksiyonları
 * @package YBS_Zirvesi_2026
 */

/**
 * 1. VERİTABANI KURULUM VE GÜNCELLEME (OTOMATİK)
 */
function ybs_setup_database() {
    global $wpdb;

    // Aynı istek içinde birden fazla çalışmasını engelle
    static $ran = false;
    if ($ran) return;
    $ran = true;

    $table_name = $wpdb->prefix . 'ybs_reservations';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        seat_id varchar(20) NOT NULL,
        archived_from_seat varchar(20) DEFAULT NULL,
        archived_at datetime DEFAULT NULL,
        user_name varchar(100) NOT NULL,
        user_email varchar(100) NOT NULL,
        user_phone varchar(20) NOT NULL,
        category varchar(50) DEFAULT 'standard',
        note varchar(255) DEFAULT '',
        color varchar(20) DEFAULT '#e74c3c',
        status varchar(50) DEFAULT 'approved',
        bilet_token varchar(50) DEFAULT '',
        certificate_code varchar(50) DEFAULT '',
        cert_emailed tinyint(1) DEFAULT 0,
        is_checked_in tinyint(1) DEFAULT 0,
        kvkk_sponsor_izin tinyint(1) DEFAULT 0,
        reservation_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY seat_id (seat_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta( $sql );

    // Mevcut kurulumlarda arşiv sütunları (kulüp koltuğunu boşalt, satır kalsın)
    $col = $wpdb->get_results( "SHOW COLUMNS FROM `{$table_name}` LIKE 'archived_from_seat'" );
    if ( empty( $col ) ) {
        $wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN archived_from_seat varchar(20) DEFAULT NULL AFTER seat_id" );
        $wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN archived_at datetime DEFAULT NULL AFTER archived_from_seat" );
    }

    // seat_id unique index varsa kaldır (Overbook için gerekli)
    $indices = $wpdb->get_results("SHOW INDEX FROM $table_name WHERE Key_name = 'seat_id' AND Non_unique = 0");
    if (!empty($indices)) {
        $wpdb->query("ALTER TABLE $table_name DROP INDEX seat_id");
        $wpdb->query("ALTER TABLE $table_name ADD INDEX seat_id (seat_id)");
    }

    // certificate_code unique index varsa kaldır (boş değerler çakışmasın)
    $cert_indices = $wpdb->get_results("SHOW INDEX FROM $table_name WHERE Key_name = 'certificate_code' AND Non_unique = 0");
    if (!empty($cert_indices)) {
        $wpdb->query("ALTER TABLE $table_name DROP INDEX certificate_code");
    }
}
add_action('after_switch_theme', 'ybs_setup_database');
add_action('admin_init', 'ybs_setup_database');
/**
 * 2. STANDART TEMA FONKSİYONLARI (Body Class vb.)
 */
function duybs_body_classes( $classes ) {
    if ( ! is_singular() ) { $classes[] = 'hfeed'; }
    if ( ! is_active_sidebar( 'sidebar-1' ) ) { $classes[] = 'no-sidebar'; }
    return $classes;
}
add_filter( 'body_class', 'duybs_body_classes' );


/**
 * YBS Zirvesi Program Akışı Shortcode
 * Kullanım: [ybs_program]
 */
function ybs_program_shortcode() {
    
    // --------------------------------------------------------
    // 1. PROGRAM VERİLERİ (Manuel ve Hesaplı)
    // --------------------------------------------------------
    $schedule = [
    'day1' => [
        'label' => '28 Mart Cumartesi', 
        'active' => false,
        'items' => [
            
             ['time' => '08.45', 'title' => 'Kayıt Başlangıç', 'desc' => '', 'type' => 'break', 'speaker' => null],
            
             ['time' => '09.00', 'title' => 'Basın Karşılama', 'desc' => '', 'type' => 'break', 'speaker' => null],
            
             ['time' => '09.15', 'title' => 'Kapı Açılış', 'desc' => '', 'type' => 'break', 'speaker' => null],
            
            ['time' => '10.00 - 10:30', 'title' => 'Açılış Konuşmaları', 'desc' => 'Rektörlük temsilcileri ve akademisyenler zirveyi protokol konuşmalarıyla açıyor.', 'type' => 'normal', 'speaker' => null, 'speakers' => [
                ['name' => 'Prof. Enver Bozdemir', 'title' => 'İşletme Fakültesi Dekanı', 'avatar' => ''],
                ['name' => 'Prof. Dr. Vahap Tecim', 'title' => 'YBS Enstitüsü Başkanı', 'avatar' => ''],
                ['name' => 'Prof. Dr. İzzet Gökhan Özbilgin', 'title' => 'Kamu Bilişim Derneği Başkanı', 'avatar' => ''],
            ]],
            
            // --- SABAH ---
            ['time' => '10:30 - 11:00', 'title' => 'Bir Yazılımcının Yapay Zeka Çağında Yol Haritası', 'desc' => 'Yapay zekanın değiştirdiği dünyada yazılımcılar için pratik bir kariyer rehberi.', 'type' => 'normal', 'speaker' => ['name' => 'Murat Yücedağ', 'title' => 'Yazılım Mühendisi / Eğitmen', 'avatar' => 'https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/Murat-Yucedag.jpg']],
            
            ['time' => '11:00 - 11:20', 'title' => 'Sponsor Konuşmaları', 'desc' => 'Sponsor konuşmaları sırasıyla gerçekleştirilecektir.', 'type' => 'normal', 'speaker' => null, 'speakers' => [
                ['name' => 'Ahmet Veli', 'title' => 'Bilsoft Yazılım A.Ş. Yönetim Kurulu Başkanı', 'avatar' => 'https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/WhatsApp-Image-2026-03-25-at-10.21.01.jpeg'],
            ]],
            
            // KAHVE ARASI (2 Sponsor)
            ['time' => '11:20 - 11:40', 'title' => 'Ara', 'desc' => '', 'type' => 'break', 'sponsor' => 'Kahve Sponsorları:', 'logo' => ['https://2026.ybszirve.org.tr/dosyalar/colombia.png', 'https://2026.ybszirve.org.tr/dosyalar/coffestudy.png'], 'speaker' => null],

            ['time' => '11:40 - 12:10', 'title' => 'İş Hayatına Hazırlık Rehberi: İhtiyaç Duyulan 7 Kritik Yetkinlik', 'desc' => 'İş dünyasında öne çıkmak için sahip olunması gereken 7 temel yetkinlik.', 'type' => 'normal', 'speaker' => ['name' => 'Cenk ŞEN', 'title' => 'MİPMAP CEO', 'avatar' => 'https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/Cenk-Sen.jpg']],
            

            ['time' => '12:10 - 12:40', 'title' => 'Türkiye`nin Regülasyon Teknolojileri (RegTech) Dünü, Bugünü, Yarını', 'desc' => 'Türkiye\'de RegTech\'in geçmişi, bugünkü durumu ve geleceğe dair öngörüler.', 'type' => 'normal', 'speaker' => ['name' => 'Yüksel SAMAST', 'title' => 'VERİON Teknoloji CEO', 'avatar' => 'https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/IMG-20260317-WA0009-764x1024.jpg']],
            
            ['time' => '12:40 - 13:10', 'title' => 'Hep Yaşın 19 - Z Kuşağının Yeniden İnşası', 'desc' => 'Z kuşağının kimliği, değerleri ve geleceği yeniden şekillendirme potansiyeli.', 'type' => 'normal', 'speaker' => ['name' => 'Aleyna Öztürk GÜLCÜ - İzzet GÜLCÜ', 'title' => 'Enwa Dil Akademi & Vocaprof Kurucu Ortakları', 'avatar' => ['https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/aleyna-ozturk-gulcujpg.jpg', 'https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/izzet-gulcu.jpeg']]],
            

            // --- ÖĞLE ARASI ---
            ['time' => '13:10 - 14:30', 'title' => 'Öğle Arası', 'desc' => '', 'type' => 'lunch', 'speaker' => null],

            // --- ÖĞLEDEN SONRA ---
            ['time' => '14:30 - 15:00', 'title' => 'Finansın Yeni Rolü: Geleceği Okuyan ve Görünür Olan Profesyoneller', 'desc' => 'Değişen iş dünyasında finans profesyonellerinin üstlendiği yeni stratejik rol.', 'type' => 'normal', 'speaker' => ['name' => 'Esra Demirci KARALAR', 'title' => 'Finans Yöneticisi', 'avatar' => 'https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/Esra-Demirci-Karalar.jpg']],
            

            ['time' => '15:00 - 15:30', 'title' => 'Saha Satış Teknolojileri ve Dijital Dönüşüm', 'desc' => 'Sahada satışı dönüştüren teknolojiler ve dijitalleşme sürecindeki fırsatlar.', 'type' => 'normal', 'speaker' => ['name' => 'Emrah TAYLAN', 'title' => 'OCTAPULL CEO', 'avatar' => 'https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/Emrah-Taylan.jpg']], 
             
             // KAHVE ARASI (2 Sponsor)
             ['time' => '15:30 - 15:50', 'title' => 'Ara', 'desc' => '', 'type' => 'break', 'sponsor' => 'Kahve Sponsorları:', 'logo' => ['https://2026.ybszirve.org.tr/dosyalar/colombia.png', 'https://2026.ybszirve.org.tr/dosyalar/coffestudy.png'], 'speaker' => null],
             
             ['time' => '15:50 - 16:20', 'title' => 'Kilden, Bloklara: Blokzincirle Yaşam, Ekonomi, Kariyer', 'desc' => 'Blokzincir teknolojisinin günlük yaşama, ekonomiye ve kariyer fırsatlarına etkisi.', 'type' => 'normal', 'speaker' => ['name' => 'Nehir Kalaycıoğlu', 'title' => 'Bybit TR Genel Müdürü', 'avatar' => ['https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/nehir-kalaycioglu.jpg']]],
            
            ['time' => '16:20 - 16:50', 'title' => 'Üretmeye Bahane Yok. Sahne senin!', 'desc' => 'Girişimci ruhu, yaratıcılık ve kendi sahnenizi kurmanın yolları.', 'type' => 'normal', 'speaker' => ['name' => 'Güney Sevindik', 'title' => 'Etkin Kampüs Kurucu', 'avatar' => ['https://2026.ybszirve.org.tr/dosyalar/Guney-fotograf.jpg.jpeg']]],
            

            
            ['time' => '20:00 - 23:00', 'title' => 'PARTİ', 'desc' => 'Neris Kır Bahçesi parti programı', 'type' => 'normal'], 
            
        ]
    ],

    'day2' => [
        'label' => '29 Mart Pazar',
        'active' => true,
        'items' => [
            // --- SABAH ---
            ['time' => '10:00 - 10:30', 'title' => 'Aranan mı olacaksın, arayan mı?', 'desc' => 'Kariyer bir yolculuksa… rotanı kim çiziyor?', 'type' => 'normal', 'speaker' => ['name' => 'Hakan ACAR', 'title' => 'Group CEO', 'avatar' => 'https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/WhatsApp-Image-2026-03-18-at-21.48.00.png']],
            
            ['time' => '10:30 - 11:00', 'title' => 'Yapay Zeka Çağında İŞ DÜNYASINDA BAŞARILI OLMAK', 'desc' => 'Yapay zekanın iş dünyasını nasıl dönüştürdüğü ve başarı için gereken yeni beceriler.', 'type' => 'normal', 'speaker' => ['name' => 'İnan ACILIOĞLU', 'title' => 'OpM-D Danışmanlık Kurucusu, Konuşmacı, Yazar', 'avatar' => 'https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/Inan-Acilioglu.jpg']],
            
            // KAHVE ARASI (2 Sponsor)
            ['time' => '11:00 - 11:20', 'title' => 'Ara', 'desc' => '', 'type' => 'break', 'sponsor' => 'Kahve Sponsorları:', 'logo' => ['https://2026.ybszirve.org.tr/dosyalar/colombia.png', 'https://2026.ybszirve.org.tr/dosyalar/coffestudy.png'], 'speaker' => null],

            ['time' => '11:20 - 11:50', 'title' => 'Yapay Zeka Çağında Liderlik Ve Kariyer', 'desc' => 'Yapay zekanın liderlik anlayışını ve kariyer yollarını nasıl yeniden tanımladığı.', 'type' => 'normal', 'speaker' => ['name' => 'Cem TOKBAY', 'title' => 'Yönetim ve Teknoloji Stratejisti, Konuşmacı, Yazar, LinkedIn İçerik Üreticisi ve Dünyada En Etkili 20.Kişi', 'avatar' => 'https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/Cem-Tokbay.jpg']],

            ['time' => '11:50 - 12:20', 'title' => 'Çatalın Solundan', 'desc' => 'Teknoloji, medya ve iş dünyasının kesişiminden ilham veren bakış açıları.', 'type' => 'normal', 'speaker' => ['name' => 'Murat GÖÇE', 'title' => 'Network Builder & BThaber Başkanı', 'avatar' => 'https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/Murat-Goce.jpg']],

            // --- ÖĞLE ARASI ---
            ['time' => '12:20 - 13:50', 'title' => 'Öğle Arası', 'desc' => '', 'type' => 'lunch', 'speaker' => null],

            // --- ÖĞLEDEN SONRA ---

            ['time' => '13:50 - 14:20', 'title' => 'Büyüyünce Agent Analyst Olacağım!', 'desc' => 'Yapay zeka ajanları çağında veri analistliğinin geleceği ve yeni kariyer fırsatları.', 'type' => 'normal', 'speaker' => ['name' => 'İlhami DEMİRCİ', 'title' => 'Ensight Academy Lead', 'avatar' => 'https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/Ilhami-Demirci.jpg']],

            // KAHVE ARASI (2 Sponsor)
            ['time' => '14:20 - 14:40', 'title' => 'Ara', 'desc' => '', 'type' => 'break', 'sponsor' => 'Kahve Sponsorları:', 'logo' => ['https://2026.ybszirve.org.tr/dosyalar/colombia.png', 'https://2026.ybszirve.org.tr/dosyalar/coffestudy.png'], 'speaker' => null],

            ['time' => '14:40 - 15:10', 'title' => 'Yapay Zekâ Çağında Ticari Büyüme: İş Geliştirmede Yeni Oyun', 'desc' => 'Yapay zekayı iş geliştirme stratejilerine entegre ederek ticari büyümeyi hızlandırmak.', 'type' => 'normal', 'speaker' => ['name' => 'Gökmen KUVVET', 'title' => 'Kıdemli İş Geliştirme Danışmanı', 'avatar' => 'https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/Gokmen-Kuvvet.jpg']],

            ['time' => '15:10 - 15:40', 'title' => 'Prompt`tan Dünyaya: AI Kaldıracıyla Tek Kişilik Dev Ordu Olmak', 'desc' => 'Yapay zeka araçlarıyla tek başına büyük işler başarmanın yolları ve pratik örnekler.', 'type' => 'normal', 'speaker' => ['name' => 'Selçuk Mustafa YILDIRIM', 'title' => 'Engineering Manager', 'avatar' => 'https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/WhatsApp-Image-2026-03-18-at-18.39.07-e1773865698887.png']],
         
            
            ['time' => '15:40 - 16:00', 'title' => 'Çekilişler ve Kapanış', 'desc' => '', 'type' => 'break', 'speaker' => null]
        ]
    ]
];


    // --------------------------------------------------------
    // 2. HTML ÇIKTISI
    // --------------------------------------------------------
    ob_start(); 
    ?>
    
    <section class="program-container">
        <div class="program-header">
            <h2>Program</h2>
        </div>
        
        <div class="program-card">
            
            <div class="tabs">
                <?php foreach ($schedule as $key => $day): ?>
                    <button class="tab-button <?php echo $day['active'] ? 'active' : ''; ?>" onclick="openProgramTab(event, '<?php echo $key; ?>')">
                        <?php echo $day['label']; ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <?php foreach ($schedule as $key => $day): ?>
                <div id="prog-<?php echo $key; ?>" class="tab-content <?php echo $day['active'] ? 'active' : ''; ?>">
                    <ul class="event-list">
                        
                        <?php 
                        $ev_idx = 0;
                        foreach ($day['items'] as $item): 
                            $ev_idx++;
                            $titleClass = 'event-title';
                            if($item['type'] !== 'normal') $titleClass .= ' break';
                            $hasMultiSpeakers = !empty($item['speakers']) && is_array($item['speakers']);
                            $hasSingleSpeaker = $item['type'] === 'normal' && !empty($item['speaker']);
                            $item_id = 'ev-' . esc_attr($key) . '-' . $ev_idx;
                        ?>
                            <li class="event-item<?php echo $hasMultiSpeakers ? ' event-expandable' : ''; ?>"
                                <?php if($hasMultiSpeakers): ?>onclick="toggleEventDetail('<?php echo $item_id; ?>')" style="cursor:pointer;"<?php endif; ?>>
                                <div class="event-time">
                                    <span class="time-text"><?php echo $item['time']; ?></span>
                                </div>
                                
                                <div class="event-content" style="padding: 10px 0 10px 20px; flex:1;">
                                    
                                    <div class="event-title-wrapper" style="display:flex; align-items:center; flex-wrap:wrap; gap:10px; margin:0; padding:0; min-height: 35px;">
                                        
                                        <span class="<?php echo $titleClass; ?>" style="margin:0; padding:0; line-height:1.2;">
                                            <?php if($item['type'] == 'lunch') echo '🍽️ '; ?>
                                            <?php echo $item['title']; ?>
                                        </span>

                                        <?php if($hasMultiSpeakers): ?>
                                            <span class="event-expand-icon" id="icon-<?php echo $item_id; ?>" style="font-size:1.1rem; color:#9ca3af; transition:transform 0.25s ease; margin-left:auto; flex-shrink:0; user-select:none; padding: 2px 6px; line-height:1;">▾</span>
                                        <?php endif; ?>
                                        
                                        <?php if(!empty($item['logo'])): ?>
                                            <div class="sponsor-part" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; flex-shrink:0; margin-top:2px;">
                                                <span class="ikram-label" style="font-size: 0.75rem; color: #6b7280; font-weight:600; line-height:1; margin:0; white-space: nowrap;">
                                                    <?php echo isset($item['sponsor']) ? $item['sponsor'] : ''; ?>
                                                </span>
                                                <div class="sponsor-logo-wrapper" style="display:flex; align-items:center; justify-content:flex-start; gap:8px; height:auto; min-height:30px;">
                                                    <?php 
                                                    if(is_array($item['logo'])) {
                                                        foreach($item['logo'] as $logo_url) {
                                                            echo '<img src="' . $logo_url . '" alt="Sponsor" class="sponsor-logo" style="height:30px; width:auto; max-width:90px; object-fit:contain; display:block; margin:0; flex-shrink:0;">';
                                                        }
                                                    } else {
                                                        echo '<img src="' . $item['logo'] . '" alt="Sponsor" class="sponsor-logo" style="height:30px; width:auto; max-width:90px; object-fit:contain; display:block; margin:0; flex-shrink:0;">';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                    </div>

                                    <?php if(!empty($item['desc'])): ?>
                                        <p class="event-desc" style="margin:4px 0 0 0; padding:0; line-height:1.2;"><?php echo $item['desc']; ?></p>
                                    <?php endif; ?>

                                    <?php if($hasMultiSpeakers): ?>
                                        <!-- Özet: konuşmacı sayısı -->
                                        <div class="speakers-preview" style="margin: 8px 0 0 0;">
                                            <span style="font-size:0.8rem; color:#6b7280; font-weight:500;"><?php echo count($item['speakers']); ?> konuşmacı &middot; <span style="color:#0ea5e9;">detay için tıklayın</span></span>
                                        </div>

                                        <!-- Açılır konuşmacı listesi -->
                                        <div id="<?php echo $item_id; ?>" class="event-detail-panel" style="display:none; margin-top:12px; padding-top:12px; border-top:1px solid #f0f0f0;">
                                            <?php foreach($item['speakers'] as $si => $sp):
                                                $is_last = ($si === count($item['speakers']) - 1);
                                            ?>
                                                <div class="speaker-item" style="display:flex; align-items:center; gap:10px; <?php echo !$is_last ? 'margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid #f5f5f5;' : ''; ?>">
                                                    <div style="flex:1; line-height:1.3;">
                                                        <div style="font-weight:600; font-size:0.9rem; color:#111827;"><?php echo esc_html($sp['name']); ?></div>
                                                        <?php if(!empty($sp['title'])): ?>
                                                            <div style="font-size:0.78rem; color:#6b7280;"><?php echo esc_html($sp['title']); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                    <?php elseif($hasSingleSpeaker): ?>
                                        <div class="speakers-wrapper" style="margin: 8px 0 0 0 !important; padding: 0 !important;">
                                            <div class="speaker-item" style="display:flex; align-items:center; gap:8px; margin:0 !important; padding:0 !important;">
                                                
                                                <div class="speaker-avatars" style="display:flex; align-items:center;">
                                                    <?php 
                                                    $avatars = is_array($item['speaker']['avatar']) ? $item['speaker']['avatar'] : [$item['speaker']['avatar']];
                                                    
                                                    foreach($avatars as $index => $av): 
                                                        $marginLeft = ($index > 0) ? '-10px' : '0';
                                                        
                                                        if(!empty($av) && $av != '?'): ?>
                                                            <img src="<?php echo esc_url($av); ?>" style="width:28px; height:28px; border-radius:50%; object-fit:cover; border:2px solid #fff; margin:0; padding:0; margin-left:<?php echo $marginLeft; ?>;">
                                                        <?php else: ?>
                                                            <div style="background:#eee; display:flex; align-items:center; justify-content:center; font-size:10px; color:#999; width:28px; height:28px; border-radius:50%; border:2px solid #fff; margin:0; padding:0; margin-left:<?php echo $marginLeft; ?>;">?</div>
                                                        <?php endif; 
                                                    endforeach; 
                                                    ?>
                                                </div>

                                                <div class="speaker-info" style="display:flex; align-items:center; flex-wrap:wrap; gap:0 5px; margin:0 !important; padding:0 !important; line-height:1;">
                                                    <?php if(!empty($item['speaker']['name'])): ?>
                                                        <a href="https://2026.ybszirve.org.tr/konusmacilar/" style="font-weight:600; color:#111827; margin:0; padding:0; text-decoration:none; transition: color 0.2s ease;" onmouseover="this.style.color='#0ea5e9'" onmouseout="this.style.color='#111827'">
                                                            <?php echo $item['speaker']['name']; ?>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if(!empty($item['speaker']['title'])): ?>
                                                        <span class="speaker-title" style="font-size:0.85em; color:#6b7280; font-weight:500; margin:0; padding:0;">
                                                            <?php echo !empty($item['speaker']['name']) ? '- ' : ''; ?><?php echo $item['speaker']['title']; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>

                    </ul>
                </div>
            <?php endforeach; ?>

        </div>
    </section>

    <script>
    function openProgramTab(evt, dayKey) {
        var contents = document.querySelectorAll('.tab-content');
        for(var i=0; i<contents.length; i++) {
            contents[i].style.display = 'none';
            contents[i].classList.remove('active');
        }
        
        var buttons = document.querySelectorAll('.tab-button');
        for(var i=0; i<buttons.length; i++) {
            buttons[i].className = buttons[i].className.replace(" active", "");
        }
        
        var activeContent = document.getElementById('prog-' + dayKey);
        if(activeContent) {
            activeContent.style.display = 'block';
            activeContent.classList.add('active');
        }
        
        evt.currentTarget.className += " active";
    }

    function toggleEventDetail(itemId) {
        var panel = document.getElementById(itemId);
        var icon  = document.getElementById('icon-' + itemId);
        if (!panel) return;

        var isOpen = panel.style.display === 'block';

        if (isOpen) {
            panel.style.display = 'none';
            if (icon) icon.style.transform = 'rotate(0deg)';
        } else {
            panel.style.display = 'block';
            if (icon) icon.style.transform = 'rotate(180deg)';
        }
    }
    </script>

    <?php
    return ob_get_clean();
}
// Shortcode tanımlaması

add_shortcode('ybs_program', 'ybs_program_shortcode');

// =========================================================================
// SPONSORLAR SAYFASI KISA KODU: [ybs_sponsorlar]
// =========================================================================
add_shortcode('ybs_sponsorlar', 'ybs_sponsorlar_shortcode_func');
function ybs_sponsorlar_shortcode_func() {
    ob_start();
    ?>
    
    <section class="sp-tier-section" style="padding-top: 40px;">
        <div class="container">
            <div class="sp-tier-header">
                <h2>Altın Sponsorlar</h2>
                <div class="sp-line"></div>
            </div>
            
            <div class="sp-grid gold">
                <a href="https://www.bilsoft.com/" target="_blank" class="sp-card"><img src="https://2026.ybszirve.org.tr/dosyalar/bilsoft.png" alt="Bilsoft"></a>
                <a href="#" class="sp-card"><img src="https://placehold.co/300x120/ffffff/002855?text=ALTIN+SPONSOR" alt="Altın Sponsor"></a>
                <a href="#" class="sp-card"><img src="https://placehold.co/300x120/ffffff/002855?text=ALTIN+SPONSOR" alt="Altın Sponsor"></a>
            </div>
        </div>
    </section>

    <section class="sp-tier-section">
        <div class="container">
            <div class="sp-tier-header">
                <h2>Gümüş & Destek Sponsorları</h2>
                <div class="sp-line"></div>
            </div>
            
            <div class="sp-grid silver">
                <a href="https://www.akinziraat.com/" target="_blank" class="sp-card"><img src="https://2026.ybszirve.org.tr/dosyalar/akinziraat.png" alt="Akın Ziraat"></a>
                <a href="https://www.bybit.com/tr-TR/" target="_blank" class="sp-card"><img src="https://2026.ybszirve.org.tr/dosyalar/BybitTR.png" alt="Bybit TR"></a>
                <a href="https://www.fuska.com.tr/tr/bayiler" target="_blank" class="sp-card"><img src="https://2026.ybszirve.org.tr/dosyalar/fuska.png" alt="Gümüş Sponsor"></a>
    
            </div>
        </div>
    </section>

    <section class="sp-tier-section">
        <div class="container">
            <div class="sp-tier-header">
                <h2>Bronz Sponsorlar</h2>
                <div class="sp-line"></div>
            </div>
            
            <div class="sp-grid single-sponsor">
                <a href="https://edicate.tr/" target="_blank" class="sp-card">
                    <img src="https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/WhatsApp-Image-2026-03-18-at-18.16.45.jpeg" alt="Edicate">
                </a>
                <a href="https://www.bhpgroup.com.tr/" target="_blank" class="sp-card">
                    <img src="https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/493158c3208e2e60de3257e9641e880f518e07a4.png" alt="BHP Group">
                </a>
            </div>
        </div>
    </section>

    <section class="sp-tier-section">
        <div class="container">
            <div class="sp-tier-header">
                <h2>İkram Sponsorları</h2>
                <div class="sp-line"></div>
            </div>
            
            <div class="sp-grid media">
                <a href="https://www.colombiacoffee.com.tr/" target="_blank" class="sp-card">
                    <img src="https://2026.ybszirve.org.tr/dosyalar/colombia.png" alt="Colombia Coffee">
                </a>
                <a href="https://www.instagram.com/karamelimpastanesi/" target="_blank" class="sp-card">
                    <img src="https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/WhatsApp-Image-2026-03-19-at-00.22.42.jpeg" alt="Karamelim">
                </a>
            </div>
        </div>
    </section>

    <section class="sp-tier-section">
        <div class="container">
            <div class="sp-tier-header">
                <h2>Çekiliş Sponsoru</h2>
                <div class="sp-line"></div>
            </div>
            
            <div class="sp-grid single-sponsor">
                <a href="https://www.ykykultur.com.tr/" target="_blank" class="sp-card">
                    <img src="https://2026.ybszirve.org.tr/dosyalar/ykyayinlari.png" alt="Yapı Kredi Yayınları">
                </a>
                <a href="https://www.wpokulu.com/" target="_blank" class="sp-card">
                    <img src="https://2026.ybszirve.org.tr/dosyalar/wpokulu.png" alt="WP Okulu">
                </a>
            </div>
            
        </div>
    </section>

    <section class="sp-tier-section">
        <div class="container">
            <div class="sp-tier-header">
                <h2>Medya Partnerleri</h2>
                <div class="sp-line"></div>
            </div>
            
            <div class="sp-grid media">
                <a href="https://www.bthaber.com/" target="_blank" class="sp-card">
                    <img src="https://2026.ybszirve.org.tr/dosyalar/bthaber.png" alt="BTHaber">
                </a>
                <a href="https://www.bilimsenligi.com/" target="_blank" class="sp-card">
                    <img src="https://2026.ybszirve.org.tr/dosyalar/bilimsenligi.com_.png" alt="Bilim Şenliği">
                </a>
                <a href="https://muzikonair.com/" target="_blank" class="sp-card">
                    <img src="https://2026.ybszirve.org.tr/dosyalar/normal-muzik-onair.png" alt="Müzik Onair">
                </a>
                <a href="https://www.etkinkampus.com/" target="_blank" class="sp-card">
                    <img src="https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/etkinkampus-web-header.png" alt="Etkin Kampüs">
                </a>
            </div>
        </div>
    </section>

    <section class="sp-tier-section">
        <div class="container">
            <div class="sp-tier-header">
                <h2>Çekim Sponsoru</h2>
                <div class="sp-line"></div>
            </div>
            
            <div class="sp-grid single-sponsor">
                <a href="https://gulumsemedya.com/" target="_blank" class="sp-card">
                    <img src="https://2026.ybszirve.org.tr/wp-content/uploads/2026/03/gulumse-medya-logo-188x58-1.png" alt="Gülümse Medya" class="white-logo-fix">
                </a>
            </div>
        </div>
    </section>

    <section class="sp-cta-section">
        <div class="container">
            <div class="sp-cta-box">
                <div class="sp-cta-text">
                    <h3>Sponsorumuz Olun</h3>
                    <p>Markanızı yüzlerce üniversite öğrencisiyle buluşturmak ve etkinlikte yer almak için bizimle iletişime geçin.</p>
                </div>
                <div class="sp-cta-actions">
                    <a href="https://2026.ybszirve.org.tr/dosyalar/Kitapciklar/sponsorluk_kitapcigi.pdf" target="_blank" class="btn-sp-primary">Dosyayı İndir</a>
                    <a href="https://2026.ybszirve.org.tr/iletisim/" class="btn-sp-secondary">İletişime Geç</a>
                </div>
            </div>
        </div>
    </section>

    <style>
    /*--------------------------------------------------------------
    >>> SPONSORLAR SAYFASI STİLİ
    ----------------------------------------------------------------*/

    /* Bölüm Başlıkları */
    .sp-tier-section { padding: 30px 0; }
    .sp-tier-header { text-align: center; margin-bottom: 25px; }
    .sp-tier-header h2 { font-size: 1.8rem; color: #334155; font-weight: 700; margin-bottom: 10px; }
    .sp-line { width: 50px; height: 3px; background: #00B5AD; margin: 0 auto; }

    /* Grid Sistemi */
    .sp-grid {
        display: grid; gap: 20px; max-width: 1000px; margin: 0 auto;
    }

    /* Kademeli Boyutlandırma */
    .sp-grid.gold     { grid-template-columns: repeat(3, 1fr); }
    .sp-grid.silver   { grid-template-columns: repeat(4, 1fr); }

    /* Tekli ve Medya Sponsorları İçin Özel Yerleşim */
    .sp-grid.single-platinum { display: flex; justify-content: center; }
    .sp-grid.single-sponsor { display: flex; justify-content: center; flex-wrap: wrap; gap: 20px; }
    .sp-grid.media { display: flex; justify-content: center; flex-wrap: wrap; gap: 20px; }

    /* Logo Kartı Genel Ayarları */
    .sp-card {
        background: #ffffff; 
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.3s ease;
        text-decoration: none;
        width: 100%;
        box-sizing: border-box;
    }

    /* HER SEVİYE İÇİN SABİT YÜKSEKLİKLER VE GENİŞLİKLER */
    .single-platinum .sp-card { height: 160px; width: 400px; max-width: 100%; }
    .gold .sp-card     { height: 130px; }
    .silver .sp-card   { height: 110px; }
    .single-sponsor .sp-card { height: 120px; width: 260px; max-width: 100%; }
    .media .sp-card    { height: 100px; width: 220px; max-width: 100%; }

    .sp-card img {
        max-width: 90%; 
        max-height: 90%; 
        object-fit: contain;
        filter: grayscale(100%); opacity: 0.6; transition: all 0.4s ease;
    }

    /* Hover Efekti (Renkli) */
    .sp-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border-color: #00B5AD;
    }
    .sp-card:hover img {
        filter: grayscale(0%); opacity: 1; transform: scale(1.05);
    }
    
    /* BEYAZ LOGOLARI GÖRÜNÜR YAPMA (INVERT SİHRİ) */
    .sp-card img.white-logo-fix {
        filter: invert(1) grayscale(100%);
        opacity: 0.6;
    }
    .sp-card:hover img.white-logo-fix {
        filter: invert(1) grayscale(0%);
        opacity: 1;
        transform: scale(1.05);
    }

    /* CTA Bölümü */
    .sp-cta-section { padding: 60px 0 100px 0; }
    .sp-cta-box {
        background: #002855; border-radius: 16px; padding: 50px;
        display: flex; align-items: center; justify-content: space-between;
        color: #fff; max-width: 1000px; margin: 0 auto;
        background-image: radial-gradient(circle at top right, rgba(255,255,255,0.1), transparent 50%);
    }
    .sp-cta-text h3 { font-size: 1.8rem; margin: 0 0 10px 0; color: #fff; }
    .sp-cta-text p { color: rgba(255,255,255,0.8); margin: 0; max-width: 500px; }

    .sp-cta-actions { display: flex; gap: 15px; }
    .btn-sp-primary { 
        background: #00B5AD; color: #fff; padding: 12px 25px; border-radius: 8px; 
        text-decoration: none; font-weight: 700; transition: 0.2s; 
        white-space: nowrap;
    }
    .btn-sp-primary:hover { background: #fff; color: #002855; }

    .btn-sp-secondary {
        border: 1px solid rgba(255,255,255,0.3); color: #fff; padding: 12px 25px; 
        border-radius: 8px; text-decoration: none; font-weight: 700; transition: 0.2s; 
        white-space: nowrap;
    }
    .btn-sp-secondary:hover { background: #fff; color: #002855; }

    /* Responsive */
    @media(max-width: 900px) {
        .sp-grid.gold, .sp-grid.silver { grid-template-columns: repeat(2, 1fr); }
        
        .sp-cta-box { flex-direction: column; text-align: center; gap: 30px; }
        .sp-cta-actions { width: 100%; flex-direction: column; }
        .btn-sp-primary, .btn-sp-secondary { width: 100%; text-align: center; }
    }

    @media(max-width: 500px) {
        .sp-grid.gold, .sp-grid.silver { grid-template-columns: 1fr; }
    }

    .page-hero-modern{display: none !important}
    </style>
    <?php
    return ob_get_clean();
}


/**
 * 3. REZERVASYON SİSTEMİ (BACKEND)
 */

// Çoklu Satış (Overbook) Ayarlarını Getir
function get_multi_seats() {
    return get_option('ybs_multi_seats', []);
}

// *** EKSİK OLAN FONKSİYON BURADA ***
// Frontend'e koltukların durumunu gönderir
function get_reservation_status() {
    global $wpdb;
    $table = $wpdb->prefix . 'ybs_reservations';
    
    // Dolu fiziksel koltuklar (arşive taşınanlar salonu meşgul etmez)
    $booked = $wpdb->get_col( "SELECT DISTINCT seat_id FROM $table WHERE archived_from_seat IS NULL" );
    
    // Overbook açık olan koltukları çek
    $multi = get_multi_seats();
    
    return [
        'booked' => $booked,
        'multi'  => $multi
    ];
}

// Admin Haritası İçin Detaylı Veri
function get_admin_hall_data() {
    global $wpdb;
    $table = $wpdb->prefix . 'ybs_reservations';
    $results = $wpdb->get_results("SELECT * FROM $table ORDER BY reservation_date DESC", ARRAY_A);
    
    // Veriyi ID'ye göre grupla
    $grouped = [];
    foreach($results as $row) {
        $grouped[$row['seat_id']][] = $row;
    }
    
    // Admin haritasında renklendirme için en son kaydı baz alacağız
    $last_status = [];
    foreach($results as $row) {
        // En son eklenen (tarih sırasına göre geldiği için ilk bulduğumuz veya tersi)
        // Array'i ters çevirdiğimizde veya sonuncuyu aldığımızda güncel rengi buluruz.
        $last_status[$row['seat_id']] = $row;
    }

    return [
        'reservations' => $grouped,
        'last_status' => $last_status, // Harita renklendirmesi için
        'multi_seats' => get_multi_seats()
    ];
}

// Bu fonksiyon sadece varlık kontrolü için (Geriye uyumluluk)
function get_all_reservations_data() {
    $data = get_admin_hall_data();
    return $data['last_status'];
}

// --- AJAX: Rezervasyon Yap (Misafir) ---
add_action('wp_ajax_ybs_make_reservation', 'ybs_handle_reservation');
add_action('wp_ajax_nopriv_ybs_make_reservation', 'ybs_handle_reservation');

function ybs_handle_reservation() {
    global $wpdb;
    $table = $wpdb->prefix . 'ybs_reservations';

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $seats = isset($_POST['seats']) ? $_POST['seats'] : [];

    // --- 1. BOŞ ALAN KONTROLÜ ---
    if (empty($name) || empty($email) || empty($phone) || empty($seats)) {
        wp_send_json_error(['message' => 'Lütfen tüm alanları doldurun.']);
    }

    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Geçersiz bir e-posta adresi girdiniz.']);
    }

    // --- 2. TELEFON FORMATI KONTROLÜ ---
    // Sadece rakamları al (Kullanıcı boşluk veya tire koysa bile temizlenir)
    $clean_phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($clean_phone) !== 11 || substr($clean_phone, 0, 2) !== '05') {
        wp_send_json_error(['message' => 'Telefon numaranız 05 ile başlamalı ve 11 haneli olmalıdır. (Örn: 05551234567)']);
    }

    // --- 3. ÇİFT KAYIT (DUPLICATE) KONTROLÜ ---
    // Bu e-posta VEYA telefonla daha önce kayıt yapılmış mı kontrol et
    $duplicate_check = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM $table WHERE user_email = %s OR user_phone = %s LIMIT 1",
        $email, $clean_phone
    ));

    if ($duplicate_check) {
        wp_send_json_error(['message' => 'Bu e-posta adresi veya telefon numarası ile zaten kayıt oluşturulmuş. Her katılımcı sadece 1 adet bilet alabilir.']);
    }

    $multi_seats = function_exists('get_multi_seats') ? get_multi_seats() : get_option('ybs_multi_seats', []);
    if (!is_array($multi_seats)) $multi_seats = [];
    
    $error_seats = [];

    // --- 4. BENZERSİZ BİLET TOKENİ ---
    $bilet_token = md5(uniqid(mt_rand(), true));
    $sponsor_izin = isset($_POST['sponsor_izin']) && $_POST['sponsor_izin'] == '1' ? 1 : 0;

    // Tablo şemasının güncel olduğundan emin ol (AJAX isteklerinde admin_init çalışmaz)
    ybs_setup_database();

    // --- 5. RACE CONDITION ÖNLEME: Transaction ile atomik işlem ---
    $wpdb->query('START TRANSACTION');

    foreach ($seats as $seat_id) {
        $seat_id = sanitize_text_field($seat_id);
        $is_multi = in_array($seat_id, $multi_seats);

        // FOR UPDATE ile satırı kilitleyerek eş zamanlı çakışmayı önle
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE seat_id = %s FOR UPDATE",
            $seat_id
        ));
        
        // Kural: Çoklu değilse ve doluysa -> HATA
        if (!$is_multi && $count > 0) {
            $error_seats[] = $seat_id;
        } else {
            $result = $wpdb->insert($table, [
                'seat_id' => $seat_id,
                'user_name' => $name,
                'user_email' => $email,
                'user_phone' => $clean_phone,
                'category' => 'standard',
                'color' => '#ef4444',
                'status' => 'approved',
                'bilet_token' => $bilet_token,
                'is_checked_in' => 0,
                'kvkk_sponsor_izin' => $sponsor_izin,
                'reservation_date' => current_time('mysql')
            ]);

            if ($result === false) {
                $wpdb->query('ROLLBACK');
                wp_send_json_error(['message' => 'Kayıt sırasında bir veritabanı hatası oluştu. Lütfen tekrar deneyin. (' . $wpdb->last_error . ')']);
            }
        }
    }

    if (!empty($error_seats)) {
        $wpdb->query('ROLLBACK');
        wp_send_json_error(['message' => 'Üzgünüz, seçtiğiniz koltuklar az önce doldu: ' . implode(', ', $error_seats)]);
    } else {
        $wpdb->query('COMMIT');
        wp_send_json_success([
            'message' => 'Kayıt Başarılı!',
            'token' => $bilet_token
        ]);
    }
}
// --- AJAX: Admin İşlemleri ---

// 1. Çoklu Satış Aç/Kapa
add_action('wp_ajax_ybs_toggle_multi', 'ybs_toggle_multi');
function ybs_toggle_multi() {
    if (!current_user_can('manage_options')) wp_die();
    $seat_id = sanitize_text_field($_POST['seat_id']);
    $multi = get_multi_seats();
    
    if(in_array($seat_id, $multi)) {
        $multi = array_diff($multi, [$seat_id]);
    } else {
        $multi[] = $seat_id;
    }
    update_option('ybs_multi_seats', array_values($multi));
    wp_send_json_success();
}

// 3. Admin Manuel Ekleme — ybs_admin_manual_bulk_add_func'a yönlendirildi
add_action('wp_ajax_ybs_admin_manual_add', 'ybs_admin_manual_bulk_add_func');
add_action('wp_ajax_ybs_admin_bulk', 'ybs_admin_manual_bulk_add_func');

// =========================================================================
// 4. ADMIN PANELİ ARAYÜZÜ: HARİTA + TABLO + EXCEL
// =========================================================================

// 1. Menüyü Ekliyoruz
add_action('admin_menu', 'ybs_hall_admin_menu');
function ybs_hall_admin_menu() {
    add_menu_page('Salon Yönetimi', 'Salon Yönetimi', 'manage_options', 'ybs-hall-manager', 'ybs_render_hall_page', 'dashicons-groups', 6);
}

// 2. Excel (CSV) Çıktı İşlemini Yakalama (KVKK İzni ve Filtreli Çıktı Eklendi)
add_action('admin_init', 'ybs_export_reservations_excel');
function ybs_export_reservations_excel() {
    if (isset($_GET['page']) && $_GET['page'] == 'ybs-hall-manager' && isset($_GET['export']) && $_GET['export'] == 'excel') {
        if (!current_user_can('manage_options')) wp_die('Yetkisiz işlem.');

        global $wpdb;
        $t_res = $wpdb->prefix . 'ybs_reservations';
        $t_att = $wpdb->prefix . 'ybs_attendance';
        $t_ses = $wpdb->prefix . 'ybs_sessions';

        $total_sessions = $wpdb->get_var("SELECT COUNT(*) FROM $t_ses");
        if (empty($total_sessions) || $total_sessions == 0) $total_sessions = 1;

        // "Sadece İzin Verenler" filtresi var mı kontrol et
        $where_clause = "";
        $file_suffix = "Tum_Liste";
        if (isset($_GET['filter']) && $_GET['filter'] == 'sponsor_izin') {
            $where_clause = " WHERE r.kvkk_sponsor_izin = 1 ";
            $file_suffix = "Sponsor_Izinli_Liste";
        } elseif (isset($_GET['filter']) && $_GET['filter'] == 'club_dis_kayit') {
            $where_clause = " WHERE r.category = 'club'
                              AND r.user_email NOT LIKE '%@ybszirve.local'
                              AND r.user_email NOT LIKE '%@gorevli.temp' ";
            $file_suffix = "Club_Dis_Kayitlar";
        } elseif (isset($_GET['filter']) && $_GET['filter'] == 'bireysel_club_haric') {
            $where_clause = " WHERE r.category <> 'club'
                              AND r.user_email NOT LIKE '%@ybszirve.local'
                              AND r.user_email NOT LIKE '%@gorevli.temp' ";
            $file_suffix = "Bireysel_Club_Haric";
        }

        $results = $wpdb->get_results("
            SELECT r.*, 
                   (SELECT COUNT(DISTINCT session_id) FROM $t_att WHERE user_email = r.user_email) as session_count
            FROM $t_res r 
            $where_clause
            ORDER BY r.seat_id ASC
        ");

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=YBS_Zirve_' . $file_suffix . '_' . date('Ymd_Hi') . '.csv');

        $output = fopen('php://output', 'w');
        fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF))); 
        
        fputcsv($output, array('Koltuk', 'Ad Soyad', 'E-Posta', 'Telefon', 'Kategori', 'Not', 'Check-In', 'Katılım Yüzdesi', 'Sponsor İzni', 'Kayıt Tarihi'), ';');

        foreach ($results as $row) {
            $checkin_durumu = ($row->is_checked_in == 1) ? 'Girdi' : 'Gelmedi';
            $oturum_sayisi = intval($row->session_count);
            $yuzde = round(($oturum_sayisi / $total_sessions) * 100);
            $katilim_metni = '%' . $yuzde . ' (' . $oturum_sayisi . '/' . $total_sessions . ')';
            
            // İzin durumunu Excel için metne çevir
            $izin_durumu = (isset($row->kvkk_sponsor_izin) && $row->kvkk_sponsor_izin == 1) ? 'İzin Verdi' : 'İzin Vermedi';

            fputcsv($output, array(
                $row->seat_id,
                $row->user_name,
                $row->user_email,
                $row->user_phone,
                $row->category,
                $row->note,
                $checkin_durumu,
                $katilim_metni,
                $izin_durumu,
                $row->reservation_date
            ), ';');
        }

        fclose($output);
        exit;
    }
}

// 2b. Grup Listesi Excel Export
add_action('admin_init', 'ybs_export_groups_excel');
function ybs_export_groups_excel() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'ybs-hall-manager') return;
    if (!isset($_GET['export']) || $_GET['export'] !== 'groups') return;
    if (!current_user_can('manage_options')) wp_die('Yetkisiz işlem.');

    global $wpdb;
    $t_res = $wpdb->prefix . 'ybs_reservations';

    $where = "WHERE note LIKE '%üniversite%' OR note LIKE '%Üniversite%' OR note LIKE '%ÜNİVERSİTE%'";

    // Bölüm 1: Grup özeti (kontenjan)
    $groups = $wpdb->get_results("
        SELECT note as uni_name,
               COUNT(id) as total_seats,
               GROUP_CONCAT(seat_id ORDER BY seat_id ASC SEPARATOR ', ') as seats_list
        FROM $t_res
        $where
        GROUP BY note
        ORDER BY total_seats DESC
    ");

    // Bölüm 2: Kişi detayları
    $details = $wpdb->get_results("
        SELECT note as uni_name, seat_id, user_name, user_email, user_phone, reservation_date
        FROM $t_res
        $where
        ORDER BY note ASC, seat_id ASC
    ");

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=YBS_Zirve_Grup_Listesi_' . date('Ymd_Hi') . '.csv');

    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF");

    // --- BÖLÜM 1: GRUP ÖZET ---
    fputcsv($output, ['GRUP KONTENJAN ÖZETİ'], ';');
    fputcsv($output, ['Grup Adı', 'Verilen Kontenjan (Koltuk Sayısı)', 'Koltuk Numaraları'], ';');
    if (!empty($groups)) {
        foreach ($groups as $g) {
            fputcsv($output, [
                $g->uni_name,
                $g->total_seats,
                $g->seats_list
            ], ';');
        }
    }

    // Ayırıcı boş satır
    fputcsv($output, [], ';');
    fputcsv($output, [], ';');

    // --- BÖLÜM 2: KİŞİ DETAYLARI ---
    fputcsv($output, ['KİŞİ DETAY LİSTESİ'], ';');
    fputcsv($output, ['Grup Adı', 'Koltuk', 'Ad Soyad', 'E-Posta', 'Telefon', 'Kayıt Tarihi'], ';');
    if (!empty($details)) {
        foreach ($details as $row) {
            fputcsv($output, [
                $row->uni_name,
                $row->seat_id,
                $row->user_name,
                $row->user_email,
                $row->user_phone,
                date('d.m.Y', strtotime($row->reservation_date))
            ], ';');
        }
    }

    fclose($output);
    exit;
}

// 3. Admin Arayüzü (Harita ve Tablo)
function ybs_render_hall_page() {
    $data = get_admin_hall_data();
    $is_sold_out = get_option('ybs_system_sold_out', '0') == '1';
    
    global $wpdb;
    $t_res = $wpdb->prefix . 'ybs_reservations';
    $t_att = $wpdb->prefix . 'ybs_attendance';
    $t_ses = $wpdb->prefix . 'ybs_sessions';

    // --- ÜNİVERSİTE GRUPLARI SORGUSU (Açılır Popup İçin) ---
    $uni_groups = $wpdb->get_results("
        SELECT note as uni_name, 
               GROUP_CONCAT(seat_id ORDER BY seat_id ASC SEPARATOR ',') as seats_list, 
               COUNT(id) as total_seats 
        FROM $t_res 
        WHERE note LIKE '%üniversite%' 
           OR note LIKE '%Üniversite%' 
           OR note LIKE '%ÜNİVERSİTE%' 
        GROUP BY note 
        ORDER BY total_seats DESC
    ");
    ?>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <div class="wrap">
        <h1 class="wp-heading-inline">Salon Yönetimi & Katılımcı Listesi</h1>
        
        <div style="display:inline-block; margin-left:15px;">
            <a href="?page=ybs-hall-manager&export=excel" class="page-title-action" style="background:#f3f4f6; color:#374151; border-color:#d1d5db;">📄 Tüm Listeyi İndir</a>
            <a href="?page=ybs-hall-manager&export=excel&filter=sponsor_izin" class="page-title-action" style="background:#10b981; color:#fff; border-color:#059669;">🎁 Sponsor İçin (İzinli Listeyi İndir)</a>
            <a href="?page=ybs-hall-manager&export=excel&filter=club_dis_kayit" class="page-title-action" style="background:#2563eb; color:#fff; border-color:#1d4ed8;">🏫 Club Kayıtları (Sistem Hariç)</a>
            <a href="?page=ybs-hall-manager&export=excel&filter=bireysel_club_haric" class="page-title-action" style="background:#7c3aed; color:#fff; border-color:#6d28d9;">👤 Bireysel (Club Hariç)</a>
        </div>

        <hr class="wp-header-end">

        <div id="ybs-system-status-box" style="background:#fff; padding:15px; border-radius:8px; border:1px solid #ccd0d4; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div>
                <strong style="font-size:16px;">Rezervasyon Sistemi (Bilet Satışları)</strong><br>
                <span style="color:#666; font-size:13px;">Sistemi kapatırsanız ziyaretçiler "Biletler Tükendi (Sold Out)" ekranı görür. Bilet sorgulama ekranı çalışmaya devam eder. Adminler her zaman haritayı görebilir.</span>
            </div>
            <button id="btn-toggle-system" class="button" style="font-weight:bold; padding:5px 20px; font-size:15px; <?php echo $is_sold_out ? 'color:#b91c1c; border-color:#fecaca; background:#fef2f2;' : 'color:#10b981; border-color:#a7f3d0; background:#ecfdf5;'; ?>" onclick="toggleSystemStatus()">
                <?php echo $is_sold_out ? '🛑 ŞU AN KAPALI (Tıklayıp Açın)' : '✅ ŞU AN AÇIK (Tıklayıp Kapatın)'; ?>
            </button>
        </div>

        <script>
            function toggleSystemStatus() {
                const btn = document.getElementById('btn-toggle-system');
                btn.innerText = "Bekleyin...";
                btn.disabled = true;

                let fd = new FormData();
                fd.append('action', 'ybs_toggle_system_status');

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    if(res.success) { location.reload(); } 
                    else { alert("Hata oluştu!"); btn.disabled = false; }
                }).catch(err => { alert("Bağlantı hatası!"); btn.disabled = false; });
            }
        </script>
        
        <div id="ybs-manager-wrapper">
            <div class="ybs-map-container">
                <div class="map-controls" style="justify-content:space-between; background:#fff; border-bottom:1px solid #ddd;">
                    <div style="display:flex; gap:5px; align-items:center;">
                        <button id="zi" class="button">+</button>
                        <button id="zo" class="button">-</button>
                        <button class="button" onclick="clearHighlights()" style="margin-left:10px;">Filtreyi Temizle</button>
                    </div>
                    
                    <button class="button button-primary" onclick="downloadMapImage()" id="btn-download-map" style="display:flex; align-items:center; gap:5px; background:#2271b1; border-color:#135e96;">
                        <span class="dashicons dashicons-camera" style="margin-top:2px;"></span> Haritayı PNG İndir (Paylaş)
                    </button>
                </div>
                <div id="map-viewport" class="map-viewport">
                    <div id="seat-map" class="seat-map"></div>
                </div>
            </div>
            
            <div class="ybs-sidebar">
                <div class="sidebar-header"><h2 id="panel-title">Koltuk Detayı</h2></div>
                <div class="sidebar-content">
                    
                    <div id="view-empty" class="panel-view">
                        <p class="description">İşlem yapmak için haritadan bir koltuk seçin.</p>
                    </div>

                    <div id="view-seat" class="panel-view" style="display:none;">
                        <h3 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px;">
                            Koltuk: <span id="s-id" style="color:#d63638"></span>
                        </h3>
                        
                        <div class="overbook-box">
                            <label>
                                <input type="checkbox" id="chk-multi" onchange="toggleMulti()">
                                <strong>Çoklu Satışa Aç (Overbook)</strong>
                            </label>
                            <p>Seçilirse bu koltuk hiç dolmaz, herkes alabilir.</p>
                        </div>

                        <h4>Kayıtlar (<span id="res-count">0</span>)</h4>
                        <div id="res-list"></div>

                        <div class="admin-add-box">
                            <h4>Yeni Ekle</h4>
                            <div style="display:flex; gap:5px; margin-bottom:5px;">
                                <input type="color" id="add-color" value="#3b82f6" style="width:40px; height:35px; padding:0; border:none; cursor:pointer;">
                                <select id="add-cat" class="widefat" onchange="updateColorInput()">
                                    <option value="standard" data-color="#ef4444">Standart</option>
                                    <option value="protocol" data-color="#8b5cf6">Protokol</option>
                                    <option value="sponsor" data-color="#f59e0b">Sponsor</option>
                                    <option value="club" data-color="#3b82f6" selected>Kulüp</option>
                                    <option value="staff" data-color="#374151">Görevli</option>
                                </select>
                            </div>
                            <input type="text" id="add-name" class="widefat" placeholder="İsim (Opsiyonel)" style="margin-bottom:5px;">
                            <input type="text" id="add-note" class="widefat" placeholder="Not (Örn: YBS Kulübü)" style="margin-bottom:5px;">
                            <button class="button button-primary" style="width:100%;" onclick="addManual()">Kaydet</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="ybs-table-section" style="margin-top: 20px; background: #fff; border: 1px solid #ccd0d4; border-radius: 8px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 15px;">
                
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h2 style="margin: 0;">Tüm Kayıtlar (Tablo Görünümü)</h2>
                    <button type="button" class="button button-primary" onclick="openUniModal()">Grup Bilgileri</button>
                </div>
                
                <div>
                    <label for="table-filter" style="font-weight: bold; margin-right: 10px;">Kategori Filtrele:</label>
                    <select id="table-filter" onchange="filterTable()" style="min-width: 160px; padding: 5px; border-radius: 4px;">
                        <option value="all">Tümünü Göster</option>
                        <option value="standard">Standart</option>
                        <option value="protocol">Protokol</option>
                        <option value="sponsor">Sponsor</option>
                        <option value="club">Kulüp</option>
                        <option value="staff">Görevli</option>
                    </select>
                </div>
            </div>
            
            <div class="ybs-table-scroll">
            <table class="wp-list-table widefat fixed striped" style="margin-top: 0; min-width: 800px;">
                <thead>
                    <tr>
                        <th style="width:70px;">Koltuk</th>
                        <th>Ad Soyad</th>
                        <th>E-Posta</th>
                        <th>Telefon</th>
                        <th>Grup/Not</th>
                        <th style="width:70px; text-align:center;">İzin</th>
                        <th style="width:80px; text-align:center;">Check-in</th>
                        <th style="width:90px; text-align:center;">Katılım</th>
                        <th style="width:120px;">Tarih</th>
                        <th style="width:60px; text-align:center;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_sessions = $wpdb->get_var("SELECT COUNT(*) FROM $t_ses");
                    if (empty($total_sessions) || $total_sessions == 0) $total_sessions = 1;
                    
                    $all_records = $wpdb->get_results("
                        SELECT r.*, 
                               (SELECT COUNT(DISTINCT session_id) FROM $t_att WHERE user_email = r.user_email) as session_count
                        FROM $t_res r 
                        ORDER BY r.id DESC
                    ");

                    if(empty($all_records)): ?>
                        <tr><td colspan="10" style="text-align:center;">Sistemde henüz kayıt bulunmamaktadır.</td></tr>
                    <?php else: 
                        foreach($all_records as $row): 
                            $oturum_sayisi = intval($row->session_count);
                            $yuzde = round(($oturum_sayisi / $total_sessions) * 100);
                            
                            $bg_color = ($yuzde >= 50) ? '#ecfdf5' : '#f3f4f6';
                            $text_color = ($yuzde >= 50) ? '#10b981' : '#6b7280';
                        ?>
                        <tr class="ybs-table-row" data-category="<?php echo esc_attr($row->category); ?>">
                            <td style="font-weight:bold; font-size:14px; color:#2271b1;"><?php echo esc_html($row->seat_id); ?></td>
                            <td><strong><?php echo esc_html($row->user_name); ?></strong></td>
                            <td><?php echo esc_html($row->user_email); ?></td>
                            <td><?php echo esc_html($row->user_phone); ?></td>
                            <td>
                                <?php if($row->note): ?>
                                    <span style="background:<?php echo esc_attr($row->color); ?>20; color:<?php echo esc_attr($row->color); ?>; padding:3px 8px; border-radius:12px; font-size:12px; font-weight:bold;">
                                        <?php echo esc_html($row->note); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="background:#eee; padding:3px 8px; border-radius:12px; font-size:12px;">-</span>
                                <?php endif; ?>
                            </td>
                            
                            <td style="text-align:center;">
                                <?php if(isset($row->kvkk_sponsor_izin) && $row->kvkk_sponsor_izin == 1): ?>
                                    <span class="dashicons dashicons-yes-alt" style="color:#10b981;" title="İzin Verdi"></span>
                                <?php else: ?>
                                    <span class="dashicons dashicons-dismiss" style="color:#9ca3af;" title="İzin Vermedi"></span>
                                <?php endif; ?>
                            </td>

                            <td style="text-align:center;">
                                <?php if($row->is_checked_in == 1): ?>
                                    <span style="color:#10b981; font-size:16px;" title="Giriş Yaptı">✅</span>
                                <?php else: ?>
                                    <span style="color:#9ca3af; font-weight:bold;" title="Giriş Yapmadı">-</span>
                                <?php endif; ?>
                            </td>

                            <td style="text-align:center;">
                                <span style="background:<?php echo $bg_color; ?>; color:<?php echo $text_color; ?>; padding:4px 8px; border-radius:12px; font-size:11px; font-weight:bold; white-space:nowrap; border: 1px solid <?php echo $text_color; ?>40;">
                                    %<?php echo $yuzde; ?>
                                </span>
                            </td>

                            <td style="font-size:12px; color:#666;"><?php echo date('d.m.Y', strtotime($row->reservation_date)); ?></td>
                            <td style="text-align:center;">
                                <a href="javascript:void(0);" onclick="delRes(<?php echo $row->id; ?>)" style="color:#d63638; text-decoration:none; font-weight:bold;">Sil</a>
                            </td>
                        </tr>
                        <?php endforeach; 
                    endif; ?>
                </tbody>
            </table>
            </div><!-- /.ybs-table-scroll -->
        </div>

    </div>

    <div id="uni-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:999999; justify-content:center; align-items:center;">
        <div style="background:#fff; width:100%; max-width:600px; border-radius:12px; padding:25px; position:relative; max-height:85vh; overflow-y:auto; box-shadow:0 15px 40px rgba(0,0,0,0.2);">
            <button type="button" onclick="closeUniModal()" style="position:absolute; top:15px; right:15px; background:none; border:none; font-size:28px; cursor:pointer; color:#9ca3af; line-height:1;">&times;</button>
            
            <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px; border-bottom:1px solid #e5e7eb; padding-bottom:15px; margin-bottom:15px;">
                <h2 style="margin:0; font-size:20px;">Üniversite Grupları ve Koltukları</h2>
                <a href="?page=ybs-hall-manager&export=groups" class="button button-primary" style="display:inline-flex; align-items:center; gap:5px; flex-shrink:0;">
                    <span class="dashicons dashicons-download" style="margin-top:3px;"></span> Excel İndir
                </a>
            </div>
            <p style="color:#6b7280; font-size:13px; margin-bottom:20px;">
                Aşağıdaki listeden bir gruba tıklayarak koltuklarını görebilir ve <strong>"Haritada Göster"</strong> butonu ile o gruba ait koltukları haritada işaretleyebilirsiniz.
            </p>
            
            <?php if(empty($uni_groups)): ?>
                <div style="padding:20px; background:#f9fafb; border:1px dashed #d1d5db; border-radius:8px; text-align:center; color:#6b7280;">
                    Sistemde henüz bir üniversite grubu kaydı bulunamadı.
                </div>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <?php foreach($uni_groups as $ug): ?>
                        <details style="background:#f0f6fc; border:1px solid #c3c4c7; border-radius:8px; padding:12px; cursor:pointer;">
                            <summary style="font-weight:bold; font-size:15px; color:#1d2327; outline:none; display:flex; justify-content:space-between; align-items:center;">
                                <span><?php echo esc_html($ug->uni_name); ?></span>
                                <span style="background:#2271b1; color:#fff; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:bold;">
                                    <?php echo esc_html($ug->total_seats); ?> Koltuk
                                </span>
                            </summary>
                            <div style="margin-top:15px; padding-top:15px; border-top:1px solid #dcdcde; font-size:14px; color:#3c434a; line-height:1.7;">
                                <strong>Rezerve Edilen Koltuk Numaraları:</strong><br>
                                <span style="display:inline-block; margin-top:5px; margin-bottom:10px; background:#fff; padding:8px 12px; border-radius:6px; border:1px solid #e5e7eb; width:100%; box-sizing:border-box;">
                                    <?php echo esc_html($ug->seats_list); ?>
                                </span>
                                
                                <button type="button" class="button" style="width:100%; text-align:center; border-color:#2271b1; color:#2271b1; font-weight:bold;" onclick="highlightGroupSeats('<?php echo esc_js($ug->seats_list); ?>', '<?php echo esc_js($ug->uni_name); ?>')">
                                    📍 Bu Koltukları Haritada Göster
                                </button>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
        #ybs-manager-wrapper { display: flex; height: 600px; border: 1px solid #ccd0d4; background:#fff; margin-top:20px; border-radius:8px; overflow:hidden;}
        .ybs-map-container { flex: 1; position: relative; background:#f0f0f1; overflow:hidden; display:flex; flex-direction:column; border-right:1px solid #ddd; }
        .ybs-sidebar { width: 350px; display:flex; flex-direction:column; background:#fff; }
        .map-viewport { flex:1; overflow:hidden; display:flex; justify-content:center; align-items:center; cursor:grab; background-image: radial-gradient(#ccc 1px, transparent 1px); background-size: 20px 20px; }
        .map-controls { padding:10px; border-bottom:1px solid #ddd; background:#fff; display:flex; gap:5px; align-items:center; z-index:10; }
        
        .sidebar-header { padding:15px; background:#f9f9f9; border-bottom:1px solid #eee; }
        .sidebar-header h2 { margin:0; font-size:16px; }
        .sidebar-content { padding:20px; overflow-y:auto; flex:1; }

        .seat-map { display:flex; flex-direction:column; gap:6px; padding:100px; transition:transform 0.1s; }
        .row { display:flex; justify-content:center; align-items:flex-end; }
        .wing { display:flex; gap:4px; align-items:flex-end; }
        .wing.left { transform:rotate(-4deg) translateY(10px); margin-right:40px; }
        .wing.right { transform:rotate(4deg) translateY(10px); margin-left:40px; }
        .row-label { width:25px; text-align:center; font-weight:bold; color:#aaa; }
        .gap-large { width:300px; }

        .seat { width:26px; height:32px; background:#fff; border:1px solid #999; border-radius:4px; display:flex; align-items:center; justify-content:center; font-size:10px; cursor:pointer; font-weight:bold; color:#555; transition: all 0.3s; }
        .seat.booked { background:#e74c3c; color:#fff; border-color:#c0392b; }
        .seat.multi { box-shadow: 0 0 0 2px gold; border-color:orange; z-index:5; }
        .seat.selected { box-shadow: 0 0 0 3px #2271b1 !important; z-index:10; transform:scale(1.3); }

        /* GRUP VURGULAMA EFEKTLERİ */
        .seat-map.highlight-mode .seat { opacity: 0.15; filter: grayscale(100%); }
        .seat-map.highlight-mode .seat.highlighted { 
            opacity: 1 !important; 
            filter: grayscale(0%) !important; 
            box-shadow: 0 0 0 4px #007cba, 0 0 15px rgba(0,124,186,0.5) !important; 
            z-index: 20; 
            transform: scale(1.1);
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1.1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1.1); }
        }

        .overbook-box { background:#fff8e5; padding:10px; border:1px solid #f0c33c; margin-bottom:20px; border-radius:4px; }
        .overbook-box label { font-weight:bold; display:flex; align-items:center; gap:8px; cursor:pointer; }
        .overbook-box p { font-size:11px; margin:5px 0 0; color:#8a6d3b; }

        .res-list-item { background:#f9f9f9; border:1px solid #eee; padding:10px; margin-bottom:8px; border-radius:4px; position:relative; font-size:12px; }
        .res-list-item strong { display:block; font-size:13px; margin-bottom:2px; }
        .res-list-item .note-badge { background:#e5e5e5; padding:2px 5px; border-radius:3px; font-size:10px; }
        .del-btn { position:absolute; top:8px; right:8px; color:red; cursor:pointer; text-decoration:none; font-weight:bold; }

        .admin-add-box { margin-top:20px; padding-top:15px; border-top:2px dashed #ddd; background:#fafafa; padding:15px; border-radius:5px; }
        .admin-add-box h4 { margin-top:0; margin-bottom:10px; }
        
        .stage-box { margin-top:50px; display:flex; justify-content:center; opacity:0.5; pointer-events:none; }
        .stage { width:400px; height:50px; background:#ddd; border-top:4px solid #555; border-radius:50% 50% 0 0 / 20px; display:flex; align-items:center; justify-content:center; font-weight:bold; letter-spacing:3px; }

        /* TABLO YATAY KAYDIRMA */
        .ybs-table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        /* MOBİL UYUM */
        @media screen and (max-width: 900px) {
            #ybs-manager-wrapper {
                flex-direction: column;
                height: auto;
            }
            .ybs-map-container {
                height: 380px;
                border-right: none;
                border-bottom: 1px solid #ddd;
            }
            .ybs-sidebar {
                width: 100% !important;
                max-height: 450px;
                border-top: 2px solid #eee;
            }
            #ybs-system-status-box {
                flex-direction: column;
                align-items: flex-start !important;
            }
            #ybs-system-status-box #btn-toggle-system {
                width: 100%;
                text-align: center;
            }
            #ybs-table-section {
                padding: 12px;
            }
            #uni-modal > div {
                margin: 10px;
                padding: 15px;
                max-height: 90vh;
                border-radius: 8px;
            }
            .page-title-action {
                margin-bottom: 4px;
            }
            .map-controls {
                flex-wrap: wrap;
                gap: 6px;
            }
            #btn-download-map {
                font-size: 12px;
                padding: 4px 10px;
            }
        }
    </style>

    <script>
    function openUniModal() { document.getElementById('uni-modal').style.display = 'flex'; }
    function closeUniModal() { document.getElementById('uni-modal').style.display = 'none'; }

    let currentHighlightedGroupName = '';

    function highlightGroupSeats(seatsString, uniName) {
        currentHighlightedGroupName = uniName; 
        closeUniModal();
        
        const mapEl = document.getElementById('seat-map');
        mapEl.classList.add('highlight-mode');
        
        document.querySelectorAll('.highlighted').forEach(el => el.classList.remove('highlighted'));

        const seatsArray = seatsString.split(',').map(s => s.trim());
        
        seatsArray.forEach(id => {
            const seatEl = document.getElementById('s-' + id);
            if(seatEl) { seatEl.classList.add('highlighted'); }
        });
        
        fitMap();
    }

    function clearHighlights() {
        currentHighlightedGroupName = ''; 
        const mapEl = document.getElementById('seat-map');
        mapEl.classList.remove('highlight-mode');
        document.querySelectorAll('.highlighted').forEach(el => el.classList.remove('highlighted'));
    }

    // Haritayı SIFIR KESİLME, Padding ve Filigran ile İndirme Fonksiyonu
    function downloadMapImage() {
        const watermarkText = currentHighlightedGroupName ? currentHighlightedGroupName : '10. YBS Zirvesi - Salon Planı';
        const btn = document.getElementById('btn-download-map');
        btn.innerText = "İndiriliyor...";
        btn.disabled = true;

        const originalMap = document.getElementById('seat-map');
        const mapWidth = originalMap.scrollWidth;
        const mapHeight = originalMap.scrollHeight;

        // Geçici Kapsayıcı (Görünmez ama render edilebilir)
        const captureContainer = document.createElement('div');
        captureContainer.style.cssText = `
            position: absolute; top: -9999px; left: 0; 
            width: ${mapWidth + 140}px; /* Etrafında 70px padding kalması için ekstra alan */
            height: ${mapHeight + 140}px; 
            background: #f0f0f1; 
            display: flex; align-items: center; justify-content: center;
        `;

        // Orijinal haritanın kopyası
        const clonedMap = originalMap.cloneNode(true);
        clonedMap.style.transform = 'none'; // Zoom veya kaydırmayı sıfırla ki düz çıksın
        clonedMap.style.width = mapWidth + 'px';
        clonedMap.style.height = mapHeight + 'px';
        clonedMap.style.margin = '0'; // Klon marginleri sıfırla
        
        // Yarı Saydam Yatay Filigran
        const watermark = document.createElement('div');
        watermark.innerText = watermarkText;
        watermark.style.cssText = `
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%); 
            font-size: ${mapWidth * 0.04}px; /* Ekran boyutuna göre orantılı font büyüklüğü */
            font-weight: 900; color: rgba(0,0,0,0.06); 
            white-space: nowrap; z-index: 1; pointer-events: none;
            font-family: sans-serif; text-transform: uppercase; letter-spacing: 5px;
        `;
        
        // Elemanları birleştir
        captureContainer.appendChild(watermark);
        captureContainer.appendChild(clonedMap);
        
        // DOM'a ekle (Sadece html2canvas'ın görmesi için)
        document.body.appendChild(captureContainer);

        // Fotoğrafı çek
        html2canvas(captureContainer, {
            scale: 2, // Çözünürlüğü yüksek tutar
            backgroundColor: "#f0f0f1",
            width: mapWidth + 140,
            height: mapHeight + 140,
            logging: false,
            useCORS: true
        }).then(canvas => {
            // İşlem bitti, geçici DOM elemanını sil
            document.body.removeChild(captureContainer);
            
            // Resmi İndir
            const link = document.createElement('a');
            const safeName = watermarkText.replace(/[^a-z0-9ğüşöçıİĞÜŞÖÇ]/gi, '_');
            link.download = 'YBS_Zirve_Salon_' + safeName + '.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            
            btn.innerHTML = '<span class="dashicons dashicons-camera" style="margin-top:2px;"></span> Haritayı PNG İndir (Paylaş)';
            btn.disabled = false;
        }).catch(err => {
            alert("Harita indirilirken hata oluştu!");
            if (captureContainer.parentNode) document.body.removeChild(captureContainer);
            btn.innerHTML = '<span class="dashicons dashicons-camera" style="margin-top:2px;"></span> Haritayı PNG İndir (Paylaş)';
            btn.disabled = false;
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const data = <?php echo json_encode($data); ?>;
        const reservations = data.reservations; 
        const lastStatus = data.last_status || {}; 
        let multiSeats = data.multi_seats; 
        let currentId = null;

        const layout = [
            { row: 'O', r: 4, m: 18, l: 4 }, { row: 'N', r: 7, m: 17, l: 7 },
            { row: 'M', r: 9, m: 16, l: 9 }, { row: 'L', r: 10, m: 15, l: 10 },
            { row: 'K', r: 12, m: 14, l: 11 }, { row: 'J', r: 13, m: 13, l: 12 },
            { row: 'I', r: 13, m: 12, l: 13 }, { row: 'H', r: 13, m: 11, l: 13 },
            { row: 'G', r: 13, m: 10, l: 13 }, { row: 'F', r: 13, m: 9, l: 13 },
            { row: 'E', r: 13, m: 0, l: 13, isGap: true }, 
            { row: 'D', r: 13, m: 8, l: 13 }, { row: 'C', r: 12, m: 7, l: 12 },
            { row: 'B', r: 11, m: 6, l: 11 }, { row: 'A', r: 10, m: 5, l: 10 }
        ];

        const seatMap = document.getElementById('seat-map');
        
        function render() {
            seatMap.innerHTML = '';
            layout.forEach(c => {
                const r = document.createElement('div'); r.className='row';
                const t = c.r + c.m + c.l;
                r.appendChild(wing(c.l, t, 'left', c.row));
                if(c.isGap) r.appendChild(div('gap-large'));
                else {
                    r.appendChild(label(c.row));
                    r.appendChild(wing(c.m, t-c.l, 'center', c.row));
                    r.appendChild(label(c.row));
                }
                r.appendChild(wing(c.r, c.r, 'right', c.row));
                seatMap.appendChild(r);
            });
            const st = document.createElement('div'); st.className='stage-box'; st.innerHTML='<div class="stage">SAHNE</div>';
            seatMap.appendChild(st);
        }

        function wing(cnt, start, type, rowText) {
            const w = document.createElement('div'); w.className='wing '+type;
            if(type==='left') w.appendChild(label(rowText));
            for(let i=0; i<cnt; i++) {
                const num = start - i;
                const id = rowText + '-' + num;
                const s = document.createElement('div'); s.className='seat';
                s.innerText = num; s.id = 's-'+id;
                
                if(reservations[id] && reservations[id].length > 0) {
                    s.classList.add('booked');
                    const rec = reservations[id][reservations[id].length - 1];
                    const color = rec.color || '#e74c3c';
                    s.style.backgroundColor = color;
                    s.style.borderColor = adjustColor(color, -30);
                    s.style.color = '#fff';
                }

                if(multiSeats.includes(id)) {
                    s.classList.add('multi');
                }

                s.onclick = () => select(id);
                w.appendChild(s);
            }
            if(type==='right') w.appendChild(label(rowText));
            return w;
        }

        function label(t) { const d=document.createElement('div'); d.className='row-label'; d.innerText=t; return d; }
        function div(c) { const d=document.createElement('div'); d.className=c; return d; }
        function adjustColor(c, amt) { return '#' + c.replace(/^#/, '').replace(/../g, c => ('0'+Math.min(255, Math.max(0, parseInt(c, 16) + amt)).toString(16)).substr(-2)); }

        function select(id) {
            document.querySelectorAll('.selected').forEach(e=>e.classList.remove('selected'));
            document.getElementById('s-'+id).classList.add('selected');
            currentId = id;

            document.getElementById('view-empty').style.display = 'none';
            document.getElementById('view-seat').style.display = 'block';
            document.getElementById('s-id').innerText = id;
            document.getElementById('chk-multi').checked = multiSeats.includes(id);

            const list = document.getElementById('res-list');
            list.innerHTML = '';
            const recs = reservations[id] || [];
            document.getElementById('res-count').innerText = recs.length;

            if(recs.length === 0) {
                list.innerHTML = '<p style="color:#999; font-style:italic;">Henüz kayıt yok.</p>';
            } else {
                recs.forEach(r => {
                    const el = document.createElement('div'); el.className = 'res-list-item';
                    el.innerHTML = `
                        <strong>${r.user_name}</strong>
                        ${r.user_email}<br>
                        <span class="note-badge" style="background:${r.color}20; color:${r.color}">${r.note || r.category}</span>
                        <a class="del-btn" onclick="delRes(${r.id})">×</a>
                    `;
                    list.appendChild(el);
                });
            }
        }

        window.toggleMulti = function() {
            const fd = new FormData(); fd.append('action', 'ybs_toggle_multi'); fd.append('seat_id', currentId);
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {method:'POST', body:fd}).then(r=>r.json()).then(res=>location.reload());
        }

        window.delRes = function(dbId) {
            if(!confirm('Bu kaydı silmek istediğinize emin misiniz?')) return;
            const fd = new FormData(); fd.append('action', 'ybs_admin_delete_single'); fd.append('id', dbId);
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {method:'POST', body:fd}).then(r=>r.json()).then(res=>location.reload());
        }

        window.addManual = function() {
            const fd = new URLSearchParams();
            fd.append('action', 'ybs_admin_manual_bulk_add');
            fd.append('seats[]', currentId);
            fd.append('name', document.getElementById('add-name').value);
            fd.append('note', document.getElementById('add-note').value);
            fd.append('category', document.getElementById('add-cat').value);
            fd.append('color', document.getElementById('add-color').value);
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method:'POST', 
                body:fd,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).then(r=>r.json()).then(res=>location.reload());
        }

        window.updateColorInput = function() {
            const sel = document.getElementById('add-cat');
            const col = sel.options[sel.selectedIndex].getAttribute('data-color');
            if(col) document.getElementById('add-color').value = col;
        }

        window.filterTable = function() {
            const filterValue = document.getElementById('table-filter').value;
            const rows = document.querySelectorAll('.ybs-table-row');
            
            rows.forEach(row => {
                if (filterValue === 'all') {
                    row.style.display = ''; 
                } else {
                    if (row.dataset.category === filterValue) {
                        row.style.display = ''; 
                    } else {
                        row.style.display = 'none'; 
                    }
                }
            });
        };

        const vp = document.getElementById('map-viewport');
        const map = document.getElementById('seat-map');
        let sc=0.8, x=0, y=0, down=false, lx=0, ly=0;
        
        window.fitMap = function() { sc = 0.8; x = 0; y = 0; up(); }
        
        const up = () => map.style.transform = `translate(${x}px, ${y}px) scale(${sc})`;
        vp.onmousedown = e => { down=true; lx=e.clientX-x; ly=e.clientY-y; };
        window.onmousemove = e => { if(down) { x=e.clientX-lx; y=e.clientY-ly; up(); } };
        window.onmouseup = () => down=false;
        document.getElementById('zi').onclick = () => { sc+=0.2; up(); };
        document.getElementById('zo').onclick = () => { sc-=0.2; up(); };

        render();
        up();
    });
    </script>
    <?php
}
// =========================================================================
// YBS ZİRVE - QR KOD İLE BİLET KONTROL (CHECK-IN) SİSTEMİ - POPUP VERSİYON
// =========================================================================

// 1. Admin Menüsüne "Bilet Kontrol" Sayfası Ekle
add_action('admin_menu', 'ybs_register_qr_checkin_page');
function ybs_register_qr_checkin_page() {
    add_menu_page(
        'Bilet Kontrol', 
        'Bilet Kontrol', 
        'manage_options', 
        'ybs-qr-scanner', 
        'ybs_qr_scanner_html', 
        'dashicons-camera', 
        3
    );
}

// 2. Sayfanın HTML, CSS ve JavaScript Arayüzü (Popup ile)
function ybs_qr_scanner_html() {
    global $wpdb;
    $t_res = $wpdb->prefix . 'ybs_reservations';
    $total     = (int)$wpdb->get_var("SELECT COUNT(*) FROM $t_res WHERE user_email NOT LIKE '%@ybszirve.local%'");
    $checked   = (int)$wpdb->get_var("SELECT COUNT(*) FROM $t_res WHERE is_checked_in = 1");
    $last_hour = (int)$wpdb->get_var("SELECT COUNT(*) FROM $t_res WHERE is_checked_in = 1 AND reservation_date >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    ?>
    <div class="wrap">
        <h1 style="margin-bottom: 20px;">📷 YBS Zirvesi Bilet Kontrol</h1>

        <!-- CANLI İSTATİSTİK PANELI -->
        <div id="checkin-stats-bar" style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px;">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; text-align:center; border-top:3px solid #6b7280;">
                <div style="font-size:32px; font-weight:900; color:#374151;" id="stat-total"><?php echo $total; ?></div>
                <div style="font-size:12px; color:#6b7280; font-weight:600; margin-top:4px; text-transform:uppercase; letter-spacing:0.5px;">Toplam Rezervasyon</div>
            </div>
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; text-align:center; border-top:3px solid #10b981;">
                <div style="font-size:32px; font-weight:900; color:#10b981;" id="stat-checked"><?php echo $checked; ?></div>
                <div style="font-size:12px; color:#6b7280; font-weight:600; margin-top:4px; text-transform:uppercase; letter-spacing:0.5px;">Giriş Yaptı</div>
            </div>
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; text-align:center; border-top:3px solid #f59e0b;">
                <div style="font-size:32px; font-weight:900; color:#f59e0b;" id="stat-pending"><?php echo ($total - $checked); ?></div>
                <div style="font-size:12px; color:#6b7280; font-weight:600; margin-top:4px; text-transform:uppercase; letter-spacing:0.5px;">Henüz Gelmedi</div>
            </div>
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; text-align:center; border-top:3px solid #3b82f6;">
                <div style="font-size:32px; font-weight:900; color:#3b82f6;" id="stat-hour"><?php echo $last_hour; ?></div>
                <div style="font-size:12px; color:#6b7280; font-weight:600; margin-top:4px; text-transform:uppercase; letter-spacing:0.5px;">Son 1 Saatte Giriş</div>
            </div>
        </div>
        <div style="text-align:right; font-size:11px; color:#9ca3af; margin-bottom:12px;">
            Otomatik güncelleniyor — <span id="stat-last-update">şimdi</span>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; align-items:start;">
        <div style="background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div id="qr-reader" style="width:100%;"></div>
            <p style="text-align:center; color:#666; margin-top:15px;">Bileti okutmak için kameraya gösterin. Sayfa yenilenmeyecektir.</p>
        </div>

        <!-- SON GİRİŞLER LİSTESİ -->
        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; box-shadow:0 4px 15px rgba(0,0,0,0.05);">
            <h3 style="margin:0 0 12px 0; font-size:14px; color:#374151;">Son Girişler</h3>
            <div id="recent-checkins" style="max-height:400px; overflow-y:auto; font-size:13px;">
                <p style="color:#9ca3af; text-align:center; padding:20px 0;">Yükleniyor...</p>
            </div>
        </div>
        </div>

        <div id="checkin-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:99999; justify-content:center; align-items:center;">
            <div style="background:#fff; padding:30px; border-radius:12px; max-width:400px; width:90%; text-align:center; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                
                <div id="modal-icon" style="font-size:60px; margin-bottom:10px; line-height:1;">⏳</div>
                <h2 id="modal-title" style="margin: 0 0 10px 0; font-size: 22px;">Sorgulanıyor...</h2>
                <p id="modal-desc" style="color:#666; font-size:14px; margin:0;"></p>
                
                <div id="modal-details" style="display:none; text-align:left; background:#f9fafb; padding:15px; border-radius:8px; margin:20px 0; border:1px solid #e5e7eb; font-size:14px; line-height:1.6;">
                    <strong>İsim:</strong> <span id="det-name"></span><br>
                    <strong>Koltuk:</strong> <span id="det-seat" style="color:#3b82f6; font-weight:bold;"></span><br>
                    <strong>Kategori:</strong> <span id="det-cat"></span>
                </div>
                
                <div style="display:flex; gap:10px; justify-content:center; margin-top:25px;">
                    <button id="btn-do-checkin" onclick="confirmCheckin()" class="button button-primary button-hero" style="display:none; background:#10b981; border-color:#059669; color:#fff; text-shadow:none;">Girişi Onayla (Check-in)</button>
                    <button id="btn-next-scan" onclick="resumeScanning()" class="button button-hero">Kapat / Yeni Okut</button>
                </div>

            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", { fps: 10, qrbox: {width: 250, height: 250}, rememberLastUsedCamera: true }, false);
        
        let isProcessing = false;
        let currentToken = null; // İşlem yapılacak biletin tokeni

        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return;
            isProcessing = true;
            
            html5QrcodeScanner.pause(); // Kamerayı dondur
            
            // "token=" parametresini bul
            currentToken = "";
            try {
                let url = new URL(decodedText);
                currentToken = url.searchParams.get("token");
            } catch (e) {
                let match = decodedText.match(/[?&]token=([^&]+)/);
                if(match) currentToken = match[1];
                else currentToken = decodedText;
            }

            if(!currentToken) {
                showModal('error', 'Geçersiz QR Formatı', 'Bilette token parametresi bulunamadı.');
                return;
            }

            showModal('loading', 'Sorgulanıyor...', 'Veritabanı kontrol ediliyor...');

            // 1. AŞAMA: SADECE BİLGİ GETİR
            let fd = new FormData();
            fd.append('action', 'ybs_get_ticket_info');
            fd.append('token', currentToken);

            fetch(ajaxurl, { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    if(data.data.status === 'already_checked') {
                        showModal('warning', 'Okunmuş Bilet!', 'Bu biletle daha önce giriş yapılmış.', data.data.info);
                    } else if(data.data.status === 'seat_occupied') {
                        showModal('warning', 'Bu koltuk dolu', data.data.message || 'Bu koltuğa başka bir bireysel yolcu zaten giriş yaptı.', data.data.info);
                    } else {
                        // Bilet geçerli ve okunmamış, Check-in butonunu göster
                        showModal('valid', 'Geçerli Bilet', 'Bilet onaya hazır. İçeri alabilirsiniz.', data.data.info);
                    }
                } else {
                    showModal('error', 'Geçersiz Bilet', data.data || 'Bilet sistemde bulunamadı.');
                }
            })
            .catch(err => {
                showModal('error', 'Bağlantı Hatası', 'Sunucuya ulaşılamadı.');
            });
        }

        html5QrcodeScanner.render(onScanSuccess);

        // 2. AŞAMA: CHECK-IN YAP BUTONUNA TIKLANINCA
        window.confirmCheckin = function() {
            if(!currentToken) return;
            
            // İşlem sürerken butonu kilitle
            document.getElementById('btn-do-checkin').innerText = "Onaylanıyor...";
            document.getElementById('btn-do-checkin').disabled = true;

            let fd = new FormData();
            fd.append('action', 'ybs_confirm_checkin');
            fd.append('token', currentToken);

            fetch(ajaxurl, { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    showModal('success', 'Giriş Başarılı!', 'Kişi başarıyla içeri alındı.', data.data.info);
                } else {
                    showModal('error', 'Hata', data.data || 'Kayıt sırasında bir hata oluştu.');
                }
            })
            .catch(err => {
                showModal('error', 'Bağlantı Hatası', 'Kayıt tamamlanamadı.');
            });
        }

        // MODAL (POPUP) YÖNETİMİ
        window.showModal = function(type, title, desc, info = null) {
            const modal = document.getElementById('checkin-modal');
            const icon = document.getElementById('modal-icon');
            const dTitle = document.getElementById('modal-title');
            const dDesc = document.getElementById('modal-desc');
            const details = document.getElementById('modal-details');
            const btnCheckin = document.getElementById('btn-do-checkin');
            const btnNext = document.getElementById('btn-next-scan');

            modal.style.display = 'flex'; // Popupu aç
            dTitle.innerText = title;
            dDesc.innerText = desc;
            
            // Butonları resetle
            btnCheckin.disabled = false;
            btnCheckin.innerText = "Girişi Onayla (Check-in)";

            if(type === 'loading') {
                icon.innerText = '🔄'; dTitle.style.color = '#374151';
                details.style.display = 'none'; btnCheckin.style.display = 'none'; btnNext.style.display = 'none';
            } 
            else if (type === 'valid') {
                icon.innerText = '🎫'; dTitle.style.color = '#1f2937';
                btnCheckin.style.display = 'block'; btnNext.style.display = 'block';
            }
            else if (type === 'success') {
                icon.innerText = '✅'; dTitle.style.color = '#047857';
                btnCheckin.style.display = 'none'; btnNext.style.display = 'block';
                btnNext.innerText = "Yeni Okut";
                btnNext.style.background = "#3b82f6"; btnNext.style.color = "#fff"; btnNext.style.borderColor = "#2563eb";
            } 
            else if (type === 'error') {
                icon.innerText = '❌'; dTitle.style.color = '#b91c1c';
                details.style.display = 'none'; btnCheckin.style.display = 'none'; btnNext.style.display = 'block';
                btnNext.innerText = "Yeniden Okut"; btnNext.style.background = ""; btnNext.style.color = "";
            } 
            else if (type === 'warning') {
                icon.innerText = '⚠️'; dTitle.style.color = '#b45309';
                btnCheckin.style.display = 'none'; btnNext.style.display = 'block';
                btnNext.innerText = "Yeniden Okut"; btnNext.style.background = ""; btnNext.style.color = "";
            }

            if(info) {
                details.style.display = 'block';
                document.getElementById('det-name').innerText = info.name;
                document.getElementById('det-seat').innerText = info.seat;
                document.getElementById('det-cat').innerText = info.category;
            }
        }

        // SIRADAKİ BİLETE GEÇ (Popup'ı Kapat)
        window.resumeScanning = function() {
            document.getElementById('checkin-modal').style.display = 'none';
            currentToken = null;
            isProcessing = false;
            html5QrcodeScanner.resume();
        }

        // İstatistikleri ve son girişleri yenile
        function refreshStats() {
            fetch(ajaxurl + '?action=ybs_get_checkin_stats')
            .then(r => r.json())
            .then(d => {
                if (!d.success) return;
                const s = d.data;
                document.getElementById('stat-total').innerText   = s.total;
                document.getElementById('stat-checked').innerText = s.checked;
                document.getElementById('stat-pending').innerText = s.pending;
                document.getElementById('stat-hour').innerText    = s.last_hour;

                const box = document.getElementById('recent-checkins');
                if (s.recent.length === 0) {
                    box.innerHTML = '<p style="color:#9ca3af;text-align:center;padding:20px 0;">Henüz giriş yok.</p>';
                } else {
                    box.innerHTML = s.recent.map(r => `
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f3f4f6;">
                            <div>
                                <strong style="color:#111827;">${r.name}</strong>
                                <span style="display:block;font-size:11px;color:#6b7280;">${r.seat} · ${r.category}</span>
                            </div>
                            <span style="font-size:11px;color:#9ca3af;white-space:nowrap;margin-left:8px;">${r.time}</span>
                        </div>`).join('');
                }

                const now = new Date();
                document.getElementById('stat-last-update').innerText =
                    now.getHours().toString().padStart(2,'0') + ':' +
                    now.getMinutes().toString().padStart(2,'0') + ':' +
                    now.getSeconds().toString().padStart(2,'0');
            });
        }

        refreshStats();
        setInterval(refreshStats, 20000); // 20 saniyede bir otomatik güncelle
    });
    </script>
    <?php
}

// 2b. Check-in İstatistikleri AJAX
add_action('wp_ajax_ybs_get_checkin_stats', 'ybs_ajax_get_checkin_stats');
function ybs_ajax_get_checkin_stats() {
    if (!current_user_can('manage_options')) wp_send_json_error('Yetkisiz');
    global $wpdb;
    $t = $wpdb->prefix . 'ybs_reservations';

    $total     = (int)$wpdb->get_var("SELECT COUNT(*) FROM $t WHERE user_email NOT LIKE '%@ybszirve.local%'");
    $checked   = (int)$wpdb->get_var("SELECT COUNT(*) FROM $t WHERE is_checked_in = 1");
    $last_hour = (int)$wpdb->get_var("SELECT COUNT(*) FROM $t WHERE is_checked_in = 1 AND reservation_date >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");

    $recent_rows = $wpdb->get_results(
        "SELECT user_name, seat_id, category, reservation_date FROM $t WHERE is_checked_in = 1 ORDER BY id DESC LIMIT 15"
    );

    $recent = array_map(function($r) {
        return [
            'name'     => $r->user_name,
            'seat'     => $r->seat_id,
            'category' => $r->category,
            'time'     => date('H:i', strtotime($r->reservation_date)),
        ];
    }, $recent_rows);

    wp_send_json_success([
        'total'     => $total,
        'checked'   => $checked,
        'pending'   => $total - $checked,
        'last_hour' => $last_hour,
        'recent'    => $recent,
    ]);
}

/**
 * Bilet / görevli ekranında gösterilecek koltuk etiketi (arşivlenmişse eski koltuk + arşiv).
 *
 * @param object|array $record
 * @return string
 */
function ybs_reservation_display_seat_label( $record ) {
    $arch = null;
    $sid  = '';
    if ( is_object( $record ) ) {
        $arch = isset( $record->archived_from_seat ) ? $record->archived_from_seat : null;
        $sid  = isset( $record->seat_id ) ? (string) $record->seat_id : '';
    } elseif ( is_array( $record ) ) {
        $arch = isset( $record['archived_from_seat'] ) ? $record['archived_from_seat'] : null;
        $sid  = isset( $record['seat_id'] ) ? (string) $record['seat_id'] : '';
    }
    if ( $arch !== null && $arch !== '' ) {
        return $arch . ' (arşiv)';
    }
    return $sid;
}

/**
 * Rezervasyon bireysel bilet mi (grup / görevli placeholder e-postaları değil).
 */
function ybs_reservation_is_bireysel( $record ) {
    if ( empty( $record->user_email ) ) {
        return false;
    }
    $e = $record->user_email;
    return ( strpos( $e, '@ybszirve.local' ) === false && strpos( $e, '@gorevli.temp' ) === false );
}

/**
 * Çoklu satış (checkbox) açık koltukta, bu kayıt dışında giriş yapmış başka bireysel var mı?
 * Fiziksel tek koltuk — ilk giren sonrakileri bloke eder.
 */
function ybs_reservation_multi_seat_blocked_by_other_bireysel( $record ) {
    if ( ! ybs_reservation_is_bireysel( $record ) ) {
        return false;
    }
    $multi = get_multi_seats();
    if ( ! is_array( $multi ) || ! in_array( $record->seat_id, $multi, true ) ) {
        return false;
    }
    global $wpdb;
    $table = $wpdb->prefix . 'ybs_reservations';
    $n     = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE seat_id = %s AND is_checked_in = 1 AND id != %d
             AND user_email NOT LIKE %s AND user_email NOT LIKE %s",
            $record->seat_id,
            (int) $record->id,
            '%@ybszirve.local',
            '%@gorevli.temp'
        )
    );
    return $n > 0;
}

// 3. Backend AJAX 1: SADECE BİLGİ GETİR (Durumu Değiştirmez)
add_action('wp_ajax_ybs_get_ticket_info', 'ybs_ajax_get_ticket_info');
function ybs_ajax_get_ticket_info() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ybs_reservations';
    
    $token = sanitize_text_field($_POST['token']);
    if (empty($token)) wp_send_json_error('Token boş.');

    $record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE bilet_token = %s", $token));

    if (!$record) {
        wp_send_json_error('Geçersiz Bilet! Sistemde kayıt bulunamadı.');
    }

    $info = [
        'name'     => $record->user_name,
        'seat'     => ybs_reservation_display_seat_label( $record ),
        'category' => $record->category,
    ];

    // DEĞİŞİKLİK BURADA: status yerine is_checked_in sütununda 1 var mı diye bakıyoruz
    if (isset($record->is_checked_in) && $record->is_checked_in == 1) {
        wp_send_json_success(['status' => 'already_checked', 'info' => $info]);
    }

    if ( ybs_reservation_multi_seat_blocked_by_other_bireysel( $record ) ) {
        wp_send_json_success(
            array(
                'status'  => 'seat_occupied',
                'message' => 'Bu koltuk dolu. Çoklu satışa açık bu koltuğa başka bir bireysel yolcu zaten giriş yaptı; aynı fiziki koltuk için ikinci giriş onaylanamaz.',
                'info'    => $info,
            )
        );
    }

    wp_send_json_success(['status' => 'valid', 'info' => $info]);
}

// 4. Backend AJAX 2: CHECK-IN İŞLEMİNİ ONAYLA (is_checked_in değerini 1 Yapar)
add_action('wp_ajax_ybs_confirm_checkin', 'ybs_ajax_confirm_checkin');
function ybs_ajax_confirm_checkin() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ybs_reservations';
    
    $token = sanitize_text_field($_POST['token']);
    if (empty($token)) wp_send_json_error('Token boş.');

    $record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE bilet_token = %s", $token));
    if (!$record) wp_send_json_error('Bilet bulunamadı.');

    if ( isset( $record->is_checked_in ) && (int) $record->is_checked_in !== 1 && ybs_reservation_multi_seat_blocked_by_other_bireysel( $record ) ) {
        wp_send_json_error( 'Bu koltuk dolu. Bu koltuğa giriş yapmış başka bir bireysel yolcu bulunuyor; giriş onaylanamaz.' );
    }

    // DEĞİŞİKLİK BURADA: status sütununu değil, is_checked_in sütununu 1 yapıyoruz
    $wpdb->update(
        $table_name, 
        ['is_checked_in' => 1], // 0 olan değeri 1 yap
        ['id' => $record->id]
    );

    $info = [
        'name'     => $record->user_name,
        'seat'     => ybs_reservation_display_seat_label( $record ),
        'category' => $record->category,
    ];

    wp_send_json_success(['status' => 'success', 'info' => $info]);
}



// =========================================================================
// YBS ZİRVE - OTURUM VE YOKLAMA (ATTENDANCE) SİSTEMİ (GÜNCELLENDİ)
// =========================================================================

// 1. VERİTABANI TABLOLARINI OLUŞTUR
add_action('admin_init', 'ybs_setup_attendance_tables');
function ybs_setup_attendance_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Oturumlar Tablosu (is_active Sütunu Eklendi)
    $table_sessions = $wpdb->prefix . 'ybs_sessions';
    $sql1 = "CREATE TABLE IF NOT EXISTS $table_sessions (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        token varchar(50) NOT NULL,
        is_active tinyint(1) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY token (token)
    ) $charset_collate;";

    // Yoklama (Kayıt) Tablosu
    $table_attendance = $wpdb->prefix . 'ybs_attendance';
    $sql2 = "CREATE TABLE IF NOT EXISTS $table_attendance (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        session_id mediumint(9) NOT NULL,
        user_name varchar(100) NOT NULL,
        user_email varchar(100) NOT NULL,
        user_phone varchar(20) NOT NULL,
        checkin_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY user_session (session_id, user_email)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql1);
    dbDelta($sql2);
}

// 2. ADMİN MENÜLERİNİ OLUŞTUR
add_action('admin_menu', 'ybs_attendance_menu');
function ybs_attendance_menu() {
    add_menu_page('Yoklama Sistemi', 'Yoklamalar', 'manage_options', 'ybs-attendance', 'ybs_sessions_page', 'dashicons-clipboard', 5);
    add_submenu_page('ybs-attendance', 'Oturumlar', 'Oturumlar', 'manage_options', 'ybs-attendance', 'ybs_sessions_page');
}

// 3. AJAX: OTURUM AKTİF/PASİF DURUMUNU DEĞİŞTİRME (Veritabanı Onarımlı)
add_action('wp_ajax_ybs_toggle_session_status', 'ybs_toggle_session_status_func');
function ybs_toggle_session_status_func() {
    if (!current_user_can('manage_options')) wp_send_json_error('Yetkisiz');
    global $wpdb;
    
    $table = $wpdb->prefix . 'ybs_sessions';
    
    // KRİTİK ÇÖZÜM: Kaydetmeden önce sütun var mı diye bak, yoksa anında ekle!
    $column_check = $wpdb->get_results("SHOW COLUMNS FROM $table LIKE 'is_active'");
    if (empty($column_check)) {
        $wpdb->query("ALTER TABLE $table ADD COLUMN is_active tinyint(1) DEFAULT 0");
    }

    $id = intval($_POST['id']);
    $status = intval($_POST['status']); // 1 veya 0
    
    $result = $wpdb->update($table, ['is_active' => $status], ['id' => $id]);
    
    if ($result === false) {
        wp_send_json_error('Veritabanı güncellenemedi.');
    } else {
        wp_send_json_success();
    }
}

// 4. ADMİN: OTURUMLAR SAYFASI (QR Kodlar, Projeksiyon ve Toggle)
function ybs_sessions_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'ybs_sessions';

    // Sayfa açılırken de veritabanını onar ki listeleme hatasız çalışsın
    $column_check = $wpdb->get_results("SHOW COLUMNS FROM $table LIKE 'is_active'");
    if (empty($column_check)) {
        $wpdb->query("ALTER TABLE $table ADD COLUMN is_active tinyint(1) DEFAULT 0");
    }

    // Yeni Oturum Ekleme İşlemi
    if (isset($_POST['add_session']) && !empty($_POST['session_title'])) {
        $title = sanitize_text_field($_POST['session_title']);
        $token = wp_generate_password(10, false, false); 
        $wpdb->insert($table, ['title' => $title, 'token' => $token, 'is_active' => 0]); // Varsayılan Kapalı başlar
        echo '<div class="notice notice-success is-dismissible"><p>Oturum eklendi!</p></div>';
    }

    // Oturum Silme İşlemi
    if (isset($_GET['delete_session'])) {
        $id = intval($_GET['delete_session']);
        $wpdb->delete($table, ['id' => $id]);
        $wpdb->delete($wpdb->prefix . 'ybs_attendance', ['session_id' => $id]); 
        echo '<div class="notice notice-warning is-dismissible"><p>Oturum ve ona ait yoklama kayıtları silindi.</p></div>';
    }

    $sessions = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC");
    $site_url = site_url('/yoklama'); 
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Oturum Yönetimi</h1>
        <hr class="wp-header-end">
        
        <div style="background:#fff; padding:20px; border:1px solid #ccc; border-radius:5px; max-width:600px; margin-top:20px;">
            <h3>Yeni Oturum Oluştur</h3>
            <form method="POST" style="display:flex; gap:10px;">
                <input type="text" name="session_title" placeholder="Örn: 1. Oturum - Geleceği Yönet" style="flex:1; padding:8px;" required>
                <button type="submit" name="add_session" class="button button-primary" style="padding:4px 20px;">Ekle</button>
            </form>
            <p style="font-size:12px; color:#666;">Not: Yeni oluşturulan oturumlar varsayılan olarak "Kapalı" durumdadır. Katılımcıların yoklama verebilmesi için anahtarı "Açık" konuma getirmelisiniz.</p>
        </div>

        <h3 style="margin-top:30px;">Mevcut Oturumlar ve QR Kodlar</h3>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
        
        <div style="display:flex; flex-wrap:wrap; gap:20px; margin-top:15px;">
            <?php foreach($sessions as $s): 
                $qr_link = $site_url . '?token=' . $s->token;
                $is_active = isset($s->is_active) && $s->is_active == 1;
                $bg_color = $is_active ? '#ecfdf5' : '#f9fafb';
                $border_color = $is_active ? '#10b981' : '#d1d5db';
            ?>
            <div id="card-<?php echo $s->id; ?>" style="background:<?php echo $bg_color; ?>; border:2px solid <?php echo $border_color; ?>; border-radius:8px; padding:20px; width:300px; text-align:center; box-shadow:0 4px 6px rgba(0,0,0,0.05); transition:all 0.3s; position:relative;">
                <h4 style="margin:0 0 15px 0; font-size:16px; color:#111827; height:40px; display:flex; align-items:center; justify-content:center;"><?php echo esc_html($s->title); ?></h4>
                
                <div style="position:relative; width:200px; height:200px; margin:0 auto; overflow:hidden; border-radius:8px; border:1px solid #e5e7eb;">
                    <canvas id="qr-<?php echo $s->id; ?>" style="filter:blur(8px); transition:0.3s;" class="qr-canvas-<?php echo $s->id; ?>"></canvas>
                    
                    <div id="qr-overlay-<?php echo $s->id; ?>" style="position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.7); display:flex; justify-content:center; align-items:center; flex-direction:column; gap:10px;">
                        <button type="button" class="button" onclick="revealQR(<?php echo $s->id; ?>)" id="btn-reveal-<?php echo $s->id; ?>">QR Göster</button>
                    </div>
                </div>
                
                <div style="margin:15px 0; padding:10px; background:#fff; border-radius:6px; border:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-weight:bold; font-size:13px; color:#374151;">Yoklama Durumu:</span>
                    <label style="display:flex; align-items:center; cursor:pointer; gap:8px;">
                        <input type="checkbox" onchange="toggleSession(<?php echo $s->id; ?>, this)" <?php echo $is_active ? 'checked' : ''; ?> style="width:18px; height:18px; cursor:pointer;">
                        <span id="status-text-<?php echo $s->id; ?>" style="font-weight:bold; color:<?php echo $is_active ? '#10b981' : '#ef4444'; ?>;">
                            <?php echo $is_active ? 'AÇIK' : 'KAPALI'; ?>
                        </span>
                    </label>
                </div>

                <div style="display:flex; gap:5px; justify-content:center; margin-top:15px;">
                    <button type="button" class="button" id="btn-project-<?php echo $s->id; ?>" onclick="openProjector('<?php echo esc_js($s->title); ?>', '<?php echo esc_js($qr_link); ?>', <?php echo $s->id; ?>)">📺 Ekrana Yansıt</button>
                    <a href="?page=ybs-attendance&delete_session=<?php echo $s->id; ?>" class="button" style="color:#b91c1c; border-color:#fecaca; background:#fef2f2;" onclick="return confirm('Bu oturumu ve tüm yoklama verilerini silmek istediğinize emin misiniz?');">Sil</a>
                </div>
                
                <script>
                    new QRious({
                        element: document.getElementById('qr-<?php echo $s->id; ?>'),
                        value: '<?php echo $qr_link; ?>',
                        size: 200,
                        level: 'H'
                    });
                </script>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="projector-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:#fff; z-index:999999; flex-direction:column; justify-content:center; align-items:center; text-align:center;">
        <button onclick="document.getElementById('projector-modal').style.display='none'" style="position:absolute; top:20px; right:30px; font-size:40px; background:none; border:none; cursor:pointer; color:#9ca3af;">&times;</button>
        <h1 id="proj-title" style="font-size:5vw; font-weight:900; color:#111827; margin-bottom:10px; line-height:1.2;"></h1>
        <p style="font-size:2vw; color:#6b7280; margin-bottom:40px;">Yoklama vermek için kameranızı okutunuz</p>
        <canvas id="proj-qr"></canvas>
    </div>

    <script>
        // QR Gösterme İşlemi (Küçük Karttaki Bulanıklığı Kaldırır)
        function revealQR(id) {
            const statusText = document.getElementById('status-text-' + id).innerText;
            if(statusText === "KAPALI") {
                alert("Uyarı: Oturum durumu KAPALI olduğu için QR kodu şu an bir işe yaramaz. Önce oturumu 'AÇIK' konuma getirin.");
                return;
            }

            document.querySelector('.qr-canvas-' + id).style.filter = 'none';
            document.getElementById('qr-overlay-' + id).style.display = 'none';
            
            // 30 saniye sonra tekrar gizle (Güvenlik)
            setTimeout(() => {
                document.querySelector('.qr-canvas-' + id).style.filter = 'blur(8px)';
                document.getElementById('qr-overlay-' + id).style.display = 'flex';
            }, 30000); 
        }

        // Toggle (Aç/Kapa) İşlemi AJAX
        function toggleSession(id, checkbox) {
            const status = checkbox.checked ? 1 : 0;
            const textEl = document.getElementById('status-text-' + id);
            const cardEl = document.getElementById('card-' + id);
            
            textEl.innerText = "Yükleniyor...";
            textEl.style.color = "#9ca3af";
            checkbox.disabled = true;

            let fd = new FormData();
            fd.append('action', 'ybs_toggle_session_status');
            fd.append('id', id);
            fd.append('status', status);

            fetch(ajaxurl, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                checkbox.disabled = false;
                if(res.success) {
                    if(status === 1) {
                        textEl.innerText = "AÇIK"; textEl.style.color = "#10b981";
                        cardEl.style.borderColor = "#10b981"; cardEl.style.background = "#ecfdf5";
                    } else {
                        textEl.innerText = "KAPALI"; textEl.style.color = "#ef4444";
                        cardEl.style.borderColor = "#d1d5db"; cardEl.style.background = "#f9fafb";
                        // Oturum kapanınca açık olan QR'ı da tekrar gizle
                        document.querySelector('.qr-canvas-' + id).style.filter = 'blur(8px)';
                        document.getElementById('qr-overlay-' + id).style.display = 'flex';
                    }
                } else {
                    alert("Hata: " + (res.data || "Veritabanı güncellenemedi."));
                    checkbox.checked = !checkbox.checked;
                }
            }).catch(err => {
                alert("Bağlantı hatası.");
                checkbox.checked = !checkbox.checked; 
                checkbox.disabled = false;
            });
        }

        // Projeksiyon Ekranını Aç
        function openProjector(title, link, id) {
            const statusText = document.getElementById('status-text-' + id).innerText;
            if(statusText === "KAPALI") {
                alert("Uyarı: Oturum durumu KAPALI olduğu için ekrana yansıtılamaz. Önce oturumu 'AÇIK' konuma getirin.");
                return;
            }

            document.getElementById('proj-title').innerText = title;
            document.getElementById('projector-modal').style.display = 'flex';
            
            // Tam ekranı tetikle
            if (document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen().catch(err => {});
            }

            new QRious({
                element: document.getElementById('proj-qr'),
                value: link,
                size: window.innerHeight * 0.5, 
                level: 'H'
            });
        }

        // ESC tuşuyla projeksiyondan çık
        document.addEventListener('keydown', function(e) {
            if (e.key === "Escape") {
                document.getElementById('projector-modal').style.display = 'none';
            }
        });
    </script>
    <?php
}

// =========================================================================
// 4. ADMİN: RAPOR, ÇEKİLİŞ VE GÖREVLİ EKLEME SAYFASI
// =========================================================================
function ybs_report_page() {
    global $wpdb;
    $t_res = $wpdb->prefix . 'ybs_reservations';
    $t_att = $wpdb->prefix . 'ybs_attendance';
    $t_ses = $wpdb->prefix . 'ybs_sessions';

    $total_sessions = $wpdb->get_var("SELECT COUNT(*) FROM $t_ses");
    if ($total_sessions == 0) $total_sessions = 1;

    // --- YENİ: KATILIMCI SİLME İŞLEMİ ---
    if (isset($_GET['delete_attendee']) && current_user_can('manage_options')) {
        $email_to_delete = sanitize_email($_GET['delete_attendee']);
        
        if (!empty($email_to_delete)) {
            // Hem yoklama hem de bilet kayıtlarını e-posta üzerinden sil
            $deleted_att = $wpdb->delete($t_att, ['user_email' => $email_to_delete]);
            $deleted_res = $wpdb->delete($t_res, ['user_email' => $email_to_delete]);
            
            echo '<div class="notice notice-warning is-dismissible"><p>✅ <strong>' . esc_html($email_to_delete) . '</strong> adresine ait tüm yoklama (' . intval($deleted_att) . ' adet) ve bilet kayıtları silindi.</p></div>';
        }
    }

    // --- 1. TOPLU SERTİFİKA ÜRETME İŞLEMİ ---
    if (isset($_POST['generate_missing_certs']) && current_user_can('manage_options')) {
        $attendees = $wpdb->get_results("SELECT user_name, user_email, user_phone, COUNT(DISTINCT session_id) as session_count FROM $t_att GROUP BY user_email");
        $generated_count = 0;

        foreach ($attendees as $att) {
            $ratio = $att->session_count / $total_sessions;
            if ($ratio >= 0.01) {
                $user_res = $wpdb->get_row($wpdb->prepare("SELECT id, certificate_code FROM $t_res WHERE user_email = %s LIMIT 1", $att->user_email));
                if ($user_res) {
                    if (empty($user_res->certificate_code)) {
                        $cert_code = ybs_generate_cert_code();
                        $wpdb->update($t_res, ['certificate_code' => $cert_code], ['id' => $user_res->id]);
                        $generated_count++;
                    }
                } else {
                    $cert_code = ybs_generate_cert_code();
                    $bilet_token = md5(uniqid(mt_rand(), true)); // Token güncellendi
                    $wpdb->insert($t_res, [
                        'seat_id' => 'KAPIDAN-' . rand(1000, 9999), // AYAKTA yerine KAPIDAN
                        'user_name' => $att->user_name,
                        'user_email' => $att->user_email,
                        'user_phone' => $att->user_phone,
                        'category' => 'standard',
                        'note' => 'Yoklamadan Geldi',
                        'status' => 'approved',
                        'bilet_token' => $bilet_token,
                        'certificate_code' => $cert_code,
                        'is_checked_in' => 1
                    ]);
                    $generated_count++;
                }
            }
        }
        echo '<div class="notice notice-success is-dismissible"><p>İşlem başarılı! Hak kazanan ancak kodu olmayan <strong>' . $generated_count . '</strong> kişiye sertifika kodu üretildi.</p></div>';
    }

    // --- 2. MANUEL GÖREVLİ EKLEME (FULL KATILIM) İŞLEMİ ---
    if (isset($_POST['manual_bulk_add']) && current_user_can('manage_options')) {
        $rawText = sanitize_textarea_field($_POST['manual_names']);
        $lines = explode("\n", $rawText);
        $addedCount = 0;

        $all_sessions = $wpdb->get_col("SELECT id FROM $t_ses");

        foreach($lines as $line) {
            $line = trim($line);
            if(empty($line)) continue;

            $parts = explode("|", $line);
            $name = trim($parts[0]);
            $email = isset($parts[1]) ? sanitize_email(trim($parts[1])) : '';

            if (empty($email)) {
                $slug = sanitize_title($name);
                if(empty($slug)) $slug = 'gorevli_' . rand(1000,9999);
                $email = $slug . "@gorevli.temp"; 
            }

            $user_res = $wpdb->get_row($wpdb->prepare("SELECT id, certificate_code FROM $t_res WHERE user_email = %s LIMIT 1", $email));
            $cert_code = $user_res && !empty($user_res->certificate_code) ? $user_res->certificate_code : ybs_generate_cert_code();

            if (!$user_res) {
                $bilet_token = md5(uniqid(mt_rand(), true)); // Token güncellendi
                $wpdb->insert($t_res, [
                    'seat_id' => 'GOREVLI-' . rand(1000, 9999),
                    'user_name' => $name,
                    'user_email' => $email,
                    'user_phone' => '-',
                    'category' => 'staff',
                    'note' => 'Görevli/Protokol',
                    'status' => 'approved',
                    'bilet_token' => $bilet_token,
                    'certificate_code' => $cert_code,
                    'is_checked_in' => 1
                ]);
            } elseif (empty($user_res->certificate_code)) {
                $wpdb->update($t_res, ['certificate_code' => $cert_code], ['id' => $user_res->id]);
            }

            if(!empty($all_sessions)) {
                foreach($all_sessions as $sid) {
                    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $t_att WHERE session_id = %d AND user_email = %s", $sid, $email));
                    if ($exists == 0) {
                        $wpdb->insert($t_att, [
                            'session_id' => $sid,
                            'user_name'  => $name,
                            'user_email' => $email,
                            'user_phone' => '-'
                        ]);
                    }
                }
            }
            $addedCount++;
        }
        echo '<div class="notice notice-success is-dismissible"><p>✅ Başarılı! <strong>' . $addedCount . '</strong> kişi sisteme görevli olarak eklendi, tüm oturumlara katılmış sayıldı ve sertifikaları oluşturuldu.</p></div>';
    }


    // --- Rapor Tablosu Sorgusu ---
    $query = "SELECT 
                a.user_name, 
                a.user_email, 
                a.user_phone, 
                COUNT(DISTINCT a.session_id) as session_count, 
                GROUP_CONCAT(DISTINCT s.title SEPARATOR '<br>') as attended_sessions,
                (SELECT certificate_code FROM $t_res WHERE user_email = a.user_email LIMIT 1) as cert_code,
                (SELECT cert_emailed FROM $t_res WHERE user_email = a.user_email LIMIT 1) as cert_emailed,
                (SELECT is_checked_in FROM $t_res WHERE user_email = a.user_email LIMIT 1) as is_checked_in
              FROM $t_att a 
              JOIN $t_ses s ON a.session_id = s.id 
              GROUP BY a.user_email 
              ORDER BY session_count DESC";
              
    $reports = $wpdb->get_results($query);

    // --- ÇEKİLİŞ LİSTESİNİ HAZIRLA ---
    $raffleList = "";
    $normalizedNames = [];
    
    function cleanNameForCheck($name) { 
        return mb_strtoupper(str_replace(['i', 'İ', 'ı', 'I'], ['İ', 'İ', 'I', 'I'], trim($name)), 'UTF-8'); 
    }

    foreach($reports as $row) {
        if (($row->session_count / $total_sessions) >= 0.01) {
            $rawName = trim($row->user_name);
            $checkName = cleanNameForCheck($rawName);
            if (!in_array($checkName, $normalizedNames)) { 
                $raffleList .= $rawName . "\n"; 
                $normalizedNames[] = $checkName; 
            }
        }
    }
    $raffleList = trim($raffleList);

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Yoklama ve Sertifika Raporu</h1>
        
        <a href="?page=ybs-attendance-report&export_excel=1" class="page-title-action" style="background:#10b981; color:#fff; border-color:#059669;">Excel İndir</a>
        <a href="?page=ybs-attendance-report&export_cert_winners=1" class="page-title-action" style="background:#2563eb; color:#fff; border-color:#1d4ed8; margin-left:5px;">🏅 Sertifika Hakkı (Ad+E-Posta)</a>
        
        <form method="post" style="display:inline-block; margin-left:5px;">
            <button type="submit" name="generate_missing_certs" class="page-title-action" style="background:#3b82f6; color:#fff; border-color:#2563eb;" onclick="return confirm('Hak kazanan ancak henüz sertifikası oluşmamış herkese toplu sertifika üretilecek. Onaylıyor musunuz?');">Eksik Sertifikaları Üret</button>
        </form>

        <button type="button" class="page-title-action" style="background:#7c3aed; color:#fff; border-color:#6d28d9; margin-left:5px;" id="btn-bulk-cert-mail" onclick="sendAllCertMails()">📨 Toplu Sertifika Maili</button>
        <button type="button" class="page-title-action" style="background:#0dcaf0; color:#000; border-color:#0bacce; margin-left:5px;" onclick="document.getElementById('manual-add-modal').style.display='flex'">➕ Görevli Ekle</button>
        <button type="button" class="page-title-action" style="background:#f59e0b; color:#fff; border-color:#d97706; margin-left:5px;" onclick="document.getElementById('raffle-modal').style.display='flex'">🎟️ Çekiliş Listesi Al</button>

        <hr class="wp-header-end">

        <!-- OTURUM BAZLI KATILIM GRAFİĞİ -->
        <?php
        $session_stats = $wpdb->get_results("
            SELECT s.title, COUNT(a.id) as cnt
            FROM {$wpdb->prefix}ybs_sessions s
            LEFT JOIN {$wpdb->prefix}ybs_attendance a ON a.session_id = s.id
            GROUP BY s.id
            ORDER BY s.id ASC
        ");
        if (!empty($session_stats)): ?>
        <div style="background:#fff; border:1px solid #ccd0d4; border-radius:8px; padding:20px; margin-bottom:20px;">
            <h2 style="margin:0 0 16px 0; font-size:15px;">📊 Oturum Bazlı Katılım</h2>
            <canvas id="session-chart" height="80"></canvas>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            new Chart(document.getElementById('session-chart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($session_stats, 'title')); ?>,
                    datasets: [{
                        label: 'Katılımcı Sayısı',
                        data: <?php echo json_encode(array_map('intval', array_column($session_stats, 'cnt'))); ?>,
                        backgroundColor: 'rgba(37,99,235,0.7)',
                        borderColor: '#2563eb',
                        borderWidth: 1,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        });
        </script>
        <?php endif; ?>
        
        <div style="background:#fff; border-left:4px solid #3b82f6; padding:15px; margin:15px 0; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <strong>Toplam Oturum Sayısı:</strong> <?php echo $total_sessions; ?> <br>
            <span style="font-size:12px; color:#666;">Sistem %1 katılım kuralını bu sayı üzerinden hesaplamaktadır. Sadece sertifika kodu oluşturulmuş kişilere mail atabilirsiniz.</span>
        </div>
        
        <table class="wp-list-table widefat fixed striped" style="background:#fff;">
            <thead>
                <tr>
                    <th style="width:14%;">Ad Soyad</th>
                    <th style="width:14%;">E-Posta</th>
                    <th style="width:9%;">Katılım Oranı</th>
                    <th style="width:10%;">Durum</th>
                    <th style="width:8%; text-align:center;">Kapı Girişi</th>
                    <th style="width:14%;">Sertifika Kodu</th>
                    <th style="width:21%;">Katıldığı Oturumlar</th>
                    <th style="width:10%;">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($reports)): ?>
                    <tr><td colspan="8">Henüz yoklama kaydı bulunmuyor.</td></tr>
                <?php else: ?>
                    <?php foreach($reports as $row): 
                        $ratio = $row->session_count / $total_sessions;
                        $yuzde = round($ratio * 100);
                        $is_passed = ($ratio >= 0.01);
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html($row->user_name); ?></strong></td>
                        <td><?php echo esc_html($row->user_email); ?></td>
                        <td>
                            <span style="background:#374151; color:#fff; padding:3px 8px; border-radius:12px; font-weight:bold; font-size:11px;">
                                %<?php echo $yuzde; ?> (<?php echo $row->session_count . '/' . $total_sessions; ?>)
                            </span>
                        </td>
                        <td>
                            <?php if($is_passed): ?>
                                <span style="color:#10b981; font-weight:bold;">✅ Hak Kazandı</span>
                            <?php else: ?>
                                <span style="color:#ef4444; font-weight:bold;">❌ Yetersiz</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;">
                            <?php if(!empty($row->is_checked_in)): ?>
                                <span style="color:#10b981; font-size:16px;" title="Kapıdan geçti">✅</span>
                            <?php else: ?>
                                <span style="color:#d1d5db;" title="Kapı kaydı yok">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-family:monospace; color:#2563eb; font-weight:bold;">
                            <?php echo !empty($row->cert_code) ? esc_html($row->cert_code) : '<span style="color:#9ca3af; font-weight:normal;">Oluşturulmadı</span>'; ?>
                        </td>
                        <td style="font-size:12px; color:#555;"><?php echo $row->attended_sessions; ?></td>
                        
                        <td>
                            <div style="display:flex; flex-direction:column; gap:5px;">
                                <?php if($is_passed && !empty($row->cert_code)): ?>
                                    <?php if($row->cert_emailed == 1): ?>
                                        <button class="button action-send-mail" style="border-color:#10b981; color:#10b981; background:#ecfdf5;" data-email="<?php echo esc_attr($row->user_email); ?>" data-name="<?php echo esc_attr($row->user_name); ?>" data-code="<?php echo esc_attr($row->cert_code); ?>">Tekrar Gönder</button>
                                        <div style="font-size:10px; color:#10b981; text-align:center; font-weight:bold;">✓ İletildi</div>
                                    <?php else: ?>
                                        <button class="button action-send-mail" style="border-color:#2563eb; color:#2563eb;" data-email="<?php echo esc_attr($row->user_email); ?>" data-name="<?php echo esc_attr($row->user_name); ?>" data-code="<?php echo esc_attr($row->cert_code); ?>">Mail At</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <a href="?page=ybs-attendance-report&delete_attendee=<?php echo urlencode($row->user_email); ?>" class="button" style="border-color:#ef4444; color:#ef4444; text-align:center;" onclick="return confirm('DİKKAT: Bu kişiye ait tüm yoklama ve bilet kayıtları silinecektir. Emin misiniz?');">Sil</a>
                            </div>
                        </td>
                        
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div id="raffle-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:99999; justify-content:center; align-items:center;">
            <div style="background:#fff; padding:25px; border-radius:8px; width:500px; max-width:90%; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding-bottom:15px; margin-bottom:15px;">
                    <h3 style="margin:0;">🎟️ Çekiliş Hakkı Kazananlar</h3>
                    <button type="button" onclick="document.getElementById('raffle-modal').style.display='none'" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
                </div>
                <p style="font-size:13px; color:#666; margin-top:0;">Sadece %1 barajını geçen katılımcılar. Mükerrer isimler otomatik temizlenmiştir.</p>
                <textarea id="raffle-list-text" readonly style="width:100%; height:250px; padding:10px; font-family:monospace; font-size:13px; border:1px solid #ccc; border-radius:4px; background:#f9fafb; resize:none;"><?php echo esc_textarea($raffleList); ?></textarea>
                <div style="margin-top:15px; text-align:right;">
                    <button type="button" class="button" onclick="document.getElementById('raffle-modal').style.display='none'">Kapat</button>
                    <button type="button" class="button button-primary" onclick="copyRaffleList()">📋 Listeyi Kopyala</button>
                </div>
            </div>
        </div>

        <div id="manual-add-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:99999; justify-content:center; align-items:center;">
            <div style="background:#fff; padding:25px; border-radius:8px; width:500px; max-width:90%; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding-bottom:15px; margin-bottom:15px;">
                    <h3 style="margin:0;">➕ Manuel Görevli Ekle</h3>
                    <button type="button" onclick="document.getElementById('manual-add-modal').style.display='none'" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
                </div>
                <form method="POST">
                    <p style="font-size:13px; color:#666; margin-top:0;">
                        Buraya eklediğin isimler <strong>TÜM OTURUMLARA</strong> katılmış sayılacak ve sertifika hakkı kazanacak.<br><br>
                        <strong>Format:</strong><br>
                        <code>Ad Soyad | email@adresi.com</code><br>
                        <code>Sadece Ad Soyad</code> <span style="color:#999;">(E-posta bilinmiyorsa fake mail oluşur)</span>
                    </p>
                    <textarea name="manual_names" required placeholder="Ali Yılmaz | ali@gmail.com&#10;Ayşe Demir&#10;Mehmet Kaya | mehmet@hotmail.com" style="width:100%; height:150px; padding:10px; font-family:monospace; font-size:13px; border:1px solid #ccc; border-radius:4px; resize:none;"></textarea>
                    <input type="hidden" name="manual_bulk_add" value="1">
                    <div style="margin-top:15px; text-align:right;">
                        <button type="button" class="button" onclick="document.getElementById('manual-add-modal').style.display='none'">İptal</button>
                        <button type="submit" class="button button-primary">💾 Kaydet ve Hak Ver</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        // Toplu Sertifika Mail Gönderimi
        async function sendAllCertMails() {
            const btn = document.getElementById('btn-bulk-cert-mail');
            if (!confirm('Sertifika kodu olan ve daha önce mail gönderilmemiş tüm hak sahiplerine sertifika maili gönderilecek. Emin misiniz?')) return;

            btn.disabled = true;
            btn.innerText = '⏳ Gönderiliyor...';

            let offset = 0;
            const limit = 5;
            let totalSent = 0;
            let totalFail = 0;

            async function sendNext() {
                const fd = new FormData();
                fd.append('action', 'ybs_send_all_cert_emails');
                fd.append('offset', offset);
                fd.append('limit', limit);

                const res = await fetch(ajaxurl, { method: 'POST', body: fd });
                const data = await res.json();

                if (!data.success) {
                    alert('Hata: ' + data.data);
                    btn.disabled = false;
                    btn.innerText = '📨 Toplu Sertifika Maili';
                    return;
                }

                totalSent += data.data.sent;
                totalFail += data.data.failed;
                offset += data.data.processed;
                btn.innerText = `⏳ ${offset} gönderildi...`;

                if (data.data.has_more) {
                    await sendNext();
                } else {
                    btn.disabled = false;
                    btn.innerText = '📨 Toplu Sertifika Maili';
                    alert(`✅ Tamamlandı!\n${totalSent} mail gönderildi, ${totalFail} başarısız.`);
                    location.reload();
                }
            }

            await sendNext();
        }

        // Çekiliş Listesi Kopyalama İşlemi
        function copyRaffleList() {
            var copyText = document.getElementById("raffle-list-text");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value).then(() => {
                alert("✅ Liste başarıyla kopyalandı!");
            });
        }

        // Mail Gönderme İşlemi
        document.querySelectorAll('.action-send-mail').forEach(btn => {
            btn.addEventListener('click', function() {
                const email = this.getAttribute('data-email');
                const name = this.getAttribute('data-name');
                const code = this.getAttribute('data-code');
                const originalText = this.innerText;

                this.innerText = 'Gönderiliyor...';
                this.disabled = true;

                let fd = new FormData();
                fd.append('action', 'ybs_send_cert_email');
                fd.append('email', email);
                fd.append('name', name);
                fd.append('cert_code', code);

                fetch(ajaxurl, { method: 'POST', body: fd })
                .then(res => res.json())
                .then(res => {
                    if(res.success) {
                        this.innerText = 'Tekrar Gönder';
                        this.style.backgroundColor = '#ecfdf5';
                        this.style.color = '#047857';
                        this.style.borderColor = '#10b981';
                        this.disabled = false;
                        
                        if(!this.nextElementSibling || !this.nextElementSibling.innerText.includes('İletildi')) {
                            this.insertAdjacentHTML('afterend', '<div style="font-size:10px; color:#10b981; text-align:center; font-weight:bold;">✓ İletildi</div>');
                        }
                    } else {
                        alert(res.data);
                        this.innerText = originalText;
                        this.disabled = false;
                    }
                })
                .catch(err => {
                    alert('Sunucuyla bağlantı kurulamadı.');
                    this.innerText = originalText;
                    this.disabled = false;
                });
            });
        });
    </script>
    <?php
}




// 5. FRONTEND: YOKLAMA FORMU (Güncellendi: Otomatik DB Onarımı ve H/F Gizleme Eklendi)
add_shortcode('ybs_yoklama_formu', 'ybs_render_attendance_form');
function ybs_render_attendance_form() {
    if (!isset($_GET['token']) || empty($_GET['token'])) {
        return '<div style="text-align:center; padding:50px; color:red; font-weight:bold;">Geçersiz bağlantı. Lütfen QR kodu tekrar okutun.</div>';
    }

    $token = sanitize_text_field($_GET['token']);
    
    global $wpdb;
    $table_sessions = $wpdb->prefix . 'ybs_sessions';
    
    // --- KRİTİK DÜZELTME: Veritabanında is_active sütunu yoksa otomatik ekle ---
    $column_check = $wpdb->get_results("SHOW COLUMNS FROM $table_sessions LIKE 'is_active'");
    if (empty($column_check)) {
        $wpdb->query("ALTER TABLE $table_sessions ADD COLUMN is_active tinyint(1) DEFAULT 0");
    }

    $session = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_sessions WHERE token = %s", $token));

    if (!$session) {
        return '<div style="text-align:center; padding:50px; color:red; font-weight:bold;">Oturum bulunamadı veya silinmiş.</div>';
    }

    // --- OTURUM KAPALIYSA (Süre Doldu Ekranı - Header/Footer Gizlendi) ---
    if (isset($session->is_active) && $session->is_active == 0) {
        return '
        <style>
            header, footer, #masthead, #colophon, .site-header, .site-footer, .elementor-location-header, .elementor-location-footer { display: none !important; }
            html, body { background-color: #f3f4f6 !important; margin: 0; padding: 0; height: 100%; }
            .site-main, #primary, #main { padding: 0 !important; margin: 0 !important; min-height: 100vh; display: flex; justify-content: center; align-items: center; width: 100%;}
        </style>
        <div style="height:100vh; width:100%; display:flex; justify-content:center; align-items:center; background:#f3f4f6; font-family:-apple-system, sans-serif; padding:20px; box-sizing:border-box;">
            <div style="background:#fff; border-top:5px solid #ef4444; padding:40px 30px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.1); text-align:center; max-width:400px; width:100%;">
                <div style="font-size:60px; line-height:1; margin-bottom:15px;">⏳</div>
                <h2 style="margin:0 0 10px 0; color:#111827;">Süre Doldu</h2>
                <p style="color:#4b5563; font-size:15px; margin:0; line-height:1.5;">
                    <strong>' . esc_html($session->title) . '</strong> oturumu için yoklama alımı kapatılmıştır. Lütfen bir sonraki oturumu bekleyiniz.
                </p>
            </div>
        </div>';
    }

    ob_start();
    ?>
    
    <style>
        header, footer, #masthead, #colophon, .site-header, .site-footer, .elementor-location-header, .elementor-location-footer { display: none !important; }
        html, body { background-color: #f3f4f6 !important; margin: 0; padding: 0; height: 100%; }
        .site-main, #primary, #main { padding: 0 !important; margin: 0 !important; min-height: 100vh; display: flex; justify-content: center; align-items: center;}
    </style>

    <div style="width: 100%; max-width:400px; margin: 20px auto; background:#fff; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.1); overflow:hidden; font-family:-apple-system, sans-serif;">
        <div style="background:#111827; color:#fff; padding:30px 20px; text-align:center;">
            <div style="font-size:40px; margin-bottom:10px;">📱</div>
            <h2 style="margin:0; font-size:20px; color:#fff;"><?php echo esc_html($session->title); ?></h2>
            <p style="margin:5px 0 0 0; font-size:13px; color:#9ca3af;">Yoklama Kayıt Formu</p>
        </div>
        
        <div style="padding:30px 20px;" id="att-form-box">
            
            <div style="background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; padding:15px; border-radius:8px; margin-bottom:20px; font-size:12px; line-height:1.5;">
                <strong style="display:block; margin-bottom:5px; font-size:13px;">⚠️ ÇOK ÖNEMLİ</strong>
                Sertifika hakkı kazanabilmeniz için buraya <u>Bilet Rezervasyonu yaparken kullandığınız</u> E-Posta adresini girmelisiniz. Farklı bir adres girerseniz sistem biletinizle eşleşemez.
            </div>
            
            <div style="margin-bottom:15px;">
                <label style="display:block; font-size:11px; font-weight:bold; color:#555; text-transform:uppercase; margin-bottom:5px;">Ad Soyad</label>
                <input type="text" id="att-name" style="width:100%; padding:12px; border:1px solid #ccc; border-radius:6px; box-sizing:border-box;" required>
            </div>
            
            <div style="margin-bottom:15px;">
                <label style="display:block; font-size:11px; font-weight:bold; color:#0284c7; text-transform:uppercase; margin-bottom:5px;">Kayıtlı E-Posta Adresiniz</label>
                <input type="email" id="att-email" style="width:100%; padding:12px; border:2px solid #bae6fd; border-radius:6px; box-sizing:border-box; background:#f0f9ff;" placeholder="Bilet aldığınız e-posta" required>
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:11px; font-weight:bold; color:#555; text-transform:uppercase; margin-bottom:5px;">Telefon</label>
                <input type="tel" id="att-phone" style="width:100%; padding:12px; border:1px solid #ccc; border-radius:6px; box-sizing:border-box;" required>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer; font-size:12px; color:#475569; line-height:1.4; background:#f8fafc; padding:10px; border-radius:6px; border:1px solid #e2e8f0;">
                    <input type="checkbox" id="att-confirm" style="margin-top:2px; transform:scale(1.2);">
                    <span>Bilet alırken kullandığım E-Posta adresini doğru ve eksiksiz yazdığımı onaylıyorum.</span>
                </label>
            </div>
            
            <button id="btn-att-submit" onclick="submitAttendance()" style="width:100%; background:#2563eb; color:#fff; border:none; padding:15px; border-radius:6px; font-size:16px; font-weight:bold; cursor:pointer; transition:0.2s;">Yoklamaya Katıl</button>
            <div id="att-msg" style="margin-top:15px; text-align:center; font-weight:bold; font-size:14px;"></div>
        </div>
        
        <div id="att-success-box" style="display:none; padding:40px 20px; text-align:center;">
            <div style="font-size:60px; color:#10b981; line-height:1;">✅</div>
            <h3 style="color:#047857; margin:10px 0;">Yoklama Alındı!</h3>
            <p style="color:#666; font-size:14px;">Katılımınız başarıyla kaydedildi. İyi zirveler dileriz!</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // LocalStorage'dan eski bilgileri otomatik doldur
            document.getElementById('att-name').value = localStorage.getItem('ybs_att_name') || '';
            document.getElementById('att-email').value = localStorage.getItem('ybs_att_email') || '';
            document.getElementById('att-phone').value = localStorage.getItem('ybs_att_phone') || '';
        });

        function submitAttendance() {
            const name = document.getElementById('att-name').value.trim();
            const email = document.getElementById('att-email').value.trim();
            const phone = document.getElementById('att-phone').value.trim();
            const confirmCheck = document.getElementById('att-confirm');
            const msg = document.getElementById('att-msg');
            const btn = document.getElementById('btn-att-submit');

            // 1. Boş alan kontrolü
            if(!name || !email || !phone) {
                msg.style.color = '#b91c1c'; msg.innerText = "Lütfen tüm alanları doldurun."; return;
            }

            // 2. Onay kutusu kontrolü
            if(!confirmCheck.checked) {
                msg.style.color = '#b91c1c'; msg.innerText = "Lütfen e-posta adresinizi kontrol edip onay kutusunu işaretleyin."; return;
            }

            btn.disabled = true; 
            btn.innerText = "Kaydediliyor...";
            btn.style.background = "#9ca3af";
            msg.innerText = "";

            let fd = new FormData();
            fd.append('action', 'ybs_save_attendance');
            fd.append('session_id', '<?php echo $session->id; ?>');
            fd.append('name', name);
            fd.append('email', email);
            fd.append('phone', phone);

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if(data && data.success) {
                    localStorage.setItem('ybs_att_name', name);
                    localStorage.setItem('ybs_att_email', email);
                    localStorage.setItem('ybs_att_phone', phone);

                    document.getElementById('att-form-box').style.display = 'none';
                    document.getElementById('att-success-box').style.display = 'block';
                } else {
                    msg.style.color = '#b45309'; 
                    msg.innerText = (data && data.data) ? data.data : "Sistem yanıt vermedi veya oturum süresi doldu.";
                    btn.disabled = false; btn.innerText = "Tekrar Dene";
                    btn.style.background = "#2563eb";
                }
            })
            .catch(err => {
                msg.style.color = '#b91c1c'; msg.innerText = "Bağlantı hatası! Lütfen internetinizi kontrol edin.";
                btn.disabled = false; btn.innerText = "Tekrar Dene";
                btn.style.background = "#2563eb";
            });
        }
    </script>
    <?php
    return ob_get_clean();
}

add_action('admin_init', 'ybs_export_attendance_report');
function ybs_export_attendance_report() {
    $valid_pages = ['ybs-attendance-report', 'ybs-etkinlik-ozeti'];
    if (isset($_GET['page']) && in_array($_GET['page'], $valid_pages) && isset($_GET['export_excel'])) {
        if (!current_user_can('manage_options')) wp_die('Yetkisiz işlem');

        global $wpdb;
        $t_res = $wpdb->prefix . 'ybs_reservations';
        $t_att = $wpdb->prefix . 'ybs_attendance';
        $t_ses = $wpdb->prefix . 'ybs_sessions';

        $total_sessions = $wpdb->get_var("SELECT COUNT(*) FROM $t_ses");
        if ($total_sessions == 0) $total_sessions = 1;

        // Rapor Sorgusu (Sertifika kodu + izin durumunu da çekiyor)
        $query = "SELECT 
                    a.user_name, 
                    a.user_email, 
                    a.user_phone, 
                    COUNT(DISTINCT a.session_id) as session_count, 
                    GROUP_CONCAT(DISTINCT s.title SEPARATOR ' | ') as attended_sessions,
                    (SELECT certificate_code FROM $t_res WHERE user_email = a.user_email LIMIT 1) as cert_code,
                    (SELECT kvkk_sponsor_izin FROM $t_res WHERE user_email = a.user_email ORDER BY id DESC LIMIT 1) as sponsor_izin
                  FROM $t_att a 
                  JOIN $t_ses s ON a.session_id = s.id 
                  GROUP BY a.user_email 
                  ORDER BY session_count DESC";
                  
        $reports = $wpdb->get_results($query, ARRAY_A);

        if (ob_get_length()) ob_clean();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=ybs_zirve_detayli_rapor_' . date('Y-m-d_H-i') . '.csv');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // Excel Türkçe Karakter (BOM)

        fputcsv($output, ['Ad Soyad', 'E-Posta', 'Telefon', 'Katilim Yuzdesi', 'Durum', 'Izin Bilgisi', 'Sertifika Kodu', 'Katildigi Oturumlar'], ';');

        if (!empty($reports)) {
            foreach ($reports as $row) {
                $ratio = $row['session_count'] / $total_sessions;
                $yuzde = '%' . round($ratio * 100);
                $durum = ($ratio >= 0.01) ? 'Sertifika Kazandi' : 'Yetersiz Katilim';
                $izin  = (isset($row['sponsor_izin']) && (int) $row['sponsor_izin'] === 1) ? 'Izin Verdi' : 'Izin Vermedi';
                $kod = !empty($row['cert_code']) ? $row['cert_code'] : 'Henuz Uretilmedi';

                fputcsv($output, [
                    $row['user_name'],
                    $row['user_email'],
                    $row['user_phone'],
                    $yuzde . ' (' . $row['session_count'] . '/' . $total_sessions . ')',
                    $durum,
                    $izin,
                    $kod,
                    $row['attended_sessions']
                ], ';');
            }
        }
        fclose($output);
        exit;
    }

    if (isset($_GET['page']) && in_array($_GET['page'], $valid_pages) && isset($_GET['export_cert_winners'])) {
        if (!current_user_can('manage_options')) wp_die('Yetkisiz işlem');

        global $wpdb;
        $t_att = $wpdb->prefix . 'ybs_attendance';
        $t_ses = $wpdb->prefix . 'ybs_sessions';

        $total_sessions = (int) $wpdb->get_var("SELECT COUNT(*) FROM $t_ses");
        if ($total_sessions === 0) $total_sessions = 1;

        $rows = $wpdb->get_results("
            SELECT
                MAX(user_name) AS user_name,
                user_email,
                COUNT(DISTINCT session_id) AS session_count
            FROM $t_att
            GROUP BY user_email
            ORDER BY MAX(user_name) ASC
        ", ARRAY_A);

        if (ob_get_length()) ob_clean();

        header('Content-Type: application/vnd.ms-excel; charset=UTF-16LE');
        header('Content-Disposition: attachment; filename=ybs_sertifika_hakki_kazananlar_' . date('Y-m-d_H-i') . '.xls');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'wb');
        fwrite($output, "\xFF\xFE"); // UTF-16LE BOM
        $excel_line = function(array $cols) use ($output) {
            $line = implode("\t", array_map(function($v) {
                $v = (string) $v;
                return str_replace(["\t", "\r", "\n"], [' ', ' ', ' '], $v);
            }, $cols)) . "\r\n";
            if (function_exists('mb_convert_encoding')) {
                fwrite($output, mb_convert_encoding($line, 'UTF-16LE', 'UTF-8'));
            } else {
                fwrite($output, iconv('UTF-8', 'UTF-16LE//IGNORE', $line));
            }
        };
        $excel_line(['Ad Soyad', 'E-Posta']);

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $ratio = ((int) $row['session_count']) / $total_sessions;
                if ($ratio >= 0.01) {
                    $excel_line([
                        $row['user_name'],
                        $row['user_email'],
                    ]);
                }
            }
        }

        fclose($output);
        exit;
    }
}

// Topluluk Yönetimi > Üyeler sayfası için: Organizasyon Ekibi Excel çıktısı
add_action('admin_init', function() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'ybs-uyeler' || !isset($_GET['export_org_team'])) {
        return;
    }
    if (!current_user_can('manage_options')) {
        wp_die('Yetkisiz işlem');
    }

    $args = [
        'role'    => 'topluluk_uyesi',
        'number'  => -1,
        'orderby' => 'display_name',
        'order'   => 'ASC',
        'meta_query' => [
            'relation' => 'AND',
            [
                'key'     => 'ybs_status',
                'value'   => 'pasif',
                'compare' => '!='
            ],
        ],
    ];

    $user_query = new WP_User_Query($args);
    $users      = $user_query->get_results();

    if (ob_get_length()) {
        ob_clean();
    }

    header('Content-Type: application/vnd.ms-excel; charset=UTF-16LE');
    header('Content-Disposition: attachment; filename=organizasyon_ekibi_' . date('Y-m-d_H-i') . '.xls');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'wb');
    fwrite($output, "\xFF\xFE"); // UTF-16LE BOM
    $excel_line = function(array $cols) use ($output) {
        $line = implode("\t", array_map(function($v) {
            $v = (string) $v;
            return str_replace(["\t", "\r", "\n"], [' ', ' ', ' '], $v);
        }, $cols)) . "\r\n";
        if (function_exists('mb_convert_encoding')) {
            fwrite($output, mb_convert_encoding($line, 'UTF-16LE', 'UTF-8'));
        } else {
            fwrite($output, iconv('UTF-8', 'UTF-16LE//IGNORE', $line));
        }
    };
    $excel_line(['Ad Soyad', 'E-Posta']);

    if (!empty($users)) {
        foreach ($users as $user) {
            /** @var WP_User $user */
            $status = get_user_meta($user->ID, 'ybs_status', true);
            if ($status === 'pasif') {
                continue;
            }
            $excel_line([
                $user->display_name,
                $user->user_email,
            ]);
        }
    }

    fclose($output);
    exit;
});

// 2. Rastgele Doğrulama Kodu Üretici (Örn: YBS26-X7R2-M9Q)
function ybs_generate_cert_code() {
    global $wpdb;
    $table = $wpdb->prefix . 'ybs_reservations';
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    do {
        $raw = '';
        for ($i = 0; $i < 7; $i++) {
            $raw .= $characters[random_int(0, strlen($characters) - 1)];
        }
        $code = 'YBS26-' . substr($raw, 0, 4) . '-' . substr($raw, 4, 3);
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE certificate_code = %s", $code));
    } while ($exists > 0);
    return $code;
}

// 3. FRONTEND KISA KODU: [ybs_sertifika]
add_shortcode('ybs_sertifika', 'ybs_render_certificate_page');
function ybs_render_certificate_page() {
    ob_start();
    
    // URL'den doğrulama kodu gelmiş mi kontrol et
    $auto_verify_code = isset($_GET['verify']) ? sanitize_text_field($_GET['verify']) : '';
    ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Montserrat:wght@400;500;600;700&display=swap');

        /* TEMA HEADER/FOOTER GİZLEME */
        header, footer, #masthead, #colophon, .site-header, .site-footer { display: none !important; }
        html, body { background-color: #f3f4f6 !important; margin: 0; padding: 0; min-height: 100vh; display: flex; flex-direction: column; }
        .site-main, #primary, #main { padding: 0 !important; margin: 0 !important; flex: 1; display: flex; flex-direction: column; justify-content: center; }

        /* ANA KONTEYNER */
        .cert-container { width: 100%; max-width: 1200px; margin: 40px auto; padding: 0 20px; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        
        /* FORM ALANI */
        .cert-form-area { max-width: 500px; margin: 0 auto; background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 40px; border: 1px solid #e5e7eb; }
        .cert-header { text-align: center; margin-bottom: 30px; }
        .cert-header h1 { font-size: 26px; color: #111827; margin: 0 0 10px 0; font-weight: 800; }
        .cert-header p { color: #6b7280; margin: 0; font-size: 14px; line-height: 1.5; }
        .cert-form-group { margin-bottom: 20px; }
        .cert-form-group label { display: block; font-weight: 700; margin-bottom: 8px; color: #374151; font-size:12px; text-transform:uppercase; letter-spacing: 0.5px; }
        .cert-input { width: 100%; padding: 14px 16px; border: 1.5px solid #d1d5db; border-radius: 8px; font-size: 15px; outline: none; transition: all 0.2s; box-sizing: border-box; background: #f9fafb; color: #111; }
        .cert-input:focus { border-color: #3b82f6; background: #fff; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
        .cert-btn { width: 100%; background: #2563eb; color: #fff; border: none; padding: 15px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: background 0.2s; box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2); }
        .cert-btn:hover { background: #1d4ed8; }
        .cert-btn.secondary { background: #4b5563; box-shadow: 0 4px 6px rgba(75, 85, 99, 0.2); }
        .cert-btn.secondary:hover { background: #374151; }
        
        .cert-divider { text-align: center; margin: 25px 0; position: relative; }
        .cert-divider::before { content: ""; position: absolute; left: 0; top: 50%; width: 100%; height: 1px; background: #e5e7eb; z-index: 1; }
        .cert-divider span { background: #fff; padding: 0 15px; color: #9ca3af; position: relative; z-index: 2; font-size: 12px; font-weight: 700; }
        
        .cert-msg { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; display: none; text-align:center; font-size: 14px; line-height: 1.5; }
        .cert-msg.error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; display: block; }
        .cert-msg.success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; display: block; }

        /* =============================================
           SERTİFİKA TASARIMI — NAVY & GOLD PRESTIGE
           ============================================= */

        #sertifika-wrapper { display: none; padding: 30px 0; overflow-x: auto; text-align: center; }

        #sertifika-sablonu {
            width: 1123px;
            height: 794px;
            min-width: 1123px;
            min-height: 794px;
            margin: 0 auto;
            background: #fdfcf8;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            box-sizing: border-box;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            font-family: 'Montserrat', -apple-system, sans-serif;
        }

        /* DEKORATIF ÇERÇEVE */
        .s-outer-frame { position: absolute; inset: 12px; border: 2px solid #c9a227; pointer-events: none; z-index: 10; }
        .s-inner-frame { position: absolute; inset: 19px; border: 0.5px solid rgba(201,162,39,0.4); pointer-events: none; z-index: 10; }

        /* KÖŞE SÜSLEMELERİ */
        .s-corner-svg { position: absolute; width: 55px; height: 55px; z-index: 11; }
        .s-corner-tl { top: 5px; left: 5px; }
        .s-corner-tr { top: 5px; right: 5px; }
        .s-corner-bl { bottom: 5px; left: 5px; }
        .s-corner-br { bottom: 5px; right: 5px; }

        /* ÜST ALTIN AKSANI */
        .s-accent-top { height: 5px; background: linear-gradient(to right, #1e3a8a 0%, #c9a227 25%, #f0d060 50%, #c9a227 75%, #1e3a8a 100%); flex-shrink: 0; }

        /* HEADER */
        .s-head { display: flex; align-items: center; justify-content: space-between; padding: 36px 60px 22px; flex-shrink: 0; }
        .s-head img { height: 78px; object-fit: contain; }
        .s-head-center { text-align: center; flex: 1; padding: 0 30px; }
        .s-head-ornament-line { color: #c9a227; font-size: 13px; letter-spacing: 10px; margin-bottom: 5px; display: block; }
        .s-title { font-family: 'Cormorant Garamond', Georgia, serif; font-size: 40px; color: #1a2c5b; margin: 0; font-weight: 700; letter-spacing: 6px; line-height: 1; }
        .s-sub { font-family: 'Montserrat', sans-serif; font-size: 10px; color: #9ca3af; font-weight: 600; margin: 7px 0 0 0; text-transform: uppercase; letter-spacing: 4px; }

        .s-qr-container { width: 80px; height: 80px; background: #fff; padding: 4px; border: 1px solid #e5e7eb; border-radius: 6px; flex-shrink: 0; }
        .s-qr-container canvas { width: 100% !important; height: 100% !important; display: block; }

        /* ALTIN AYIRICI ÇİZGİ */
        .s-gold-divider { margin: 0 58px; height: 1px; background: linear-gradient(to right, transparent, #c9a227 20%, #f0d060 50%, #c9a227 80%, transparent); flex-shrink: 0; }

        /* GÖVDE */
        .s-body { flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 16px 70px 8px; text-align: center; }
        .s-body-prefix { font-family: 'Montserrat', sans-serif; font-size: 10.5px; color: #9ca3af; margin: 0 0 14px 0; font-weight: 600; text-transform: uppercase; letter-spacing: 5px; }
        .s-name { font-family: 'Cormorant Garamond', Georgia, serif; font-size: 64px; color: #1a2c5b; font-weight: 700; margin: 0 0 6px 0; text-transform: uppercase; letter-spacing: 4px; line-height: 1.05; }
        .s-name-underline { width: 280px; height: 2px; background: linear-gradient(to right, transparent, #c9a227, transparent); margin: 0 auto 14px; }
        .s-diamond { color: #c9a227; font-size: 18px; margin: 0 0 14px 0; letter-spacing: 14px; }
        .s-text { font-family: 'Montserrat', sans-serif; font-size: 12px; color: #6b7280; line-height: 2; max-width: 760px; margin: 0 auto; font-weight: 400; }
        .s-text strong { color: #374151; font-weight: 700; }

        /* ALT BİLGİLER VE İMZALAR */
        .s-foot { display: flex; justify-content: space-between; align-items: flex-end; padding: 14px 65px 30px; flex-shrink: 0; }
        .s-info-text { font-family: 'Montserrat', sans-serif; font-size: 9.5px; color: #9ca3af; line-height: 1.9; }
        .s-info-text strong { color: #4b5563; }
        .s-signatures { display: flex; gap: 50px; }
        .s-sign { text-align: center; width: 175px; }
        .s-sign-line { border-bottom: 1px solid #d1d5db; margin-bottom: 8px; height: 36px; }
        .s-sign-name { font-family: 'Montserrat', sans-serif; font-size: 10px; font-weight: 700; color: #1f2937; margin: 0; }
        .s-sign-title { font-family: 'Montserrat', sans-serif; color: #9ca3af; font-size: 9px; margin: 3px 0 0 0; }

        /* ALT ALTIN AKSANI */
        .s-accent-bottom { height: 5px; background: linear-gradient(to right, #1e3a8a 0%, #c9a227 25%, #f0d060 50%, #c9a227 75%, #1e3a8a 100%); flex-shrink: 0; }

        .verify-badge { display: none; position: absolute; top: 4px; left: 50%; transform: translateX(-50%); background: #10b981; color: #fff; padding: 5px 18px; border-radius: 0 0 8px 8px; font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; z-index: 20; }

        /* İNDİRME BUTONLARI */
        .download-btns { display: none; gap: 15px; margin-top: 30px; justify-content:center; flex-wrap:wrap; max-width: 900px; margin-left: auto; margin-right: auto;}
        .download-btns button { padding: 14px 28px; font-size: 14px; font-weight: 700; border-radius: 8px; border: none; cursor: pointer; color: #fff; transition:0.2s; display: flex; align-items: center; gap: 8px; }
        .btn-jpg { background: #10b981; } .btn-jpg:hover { background: #059669; }
        .btn-pdf { background: #ef4444; } .btn-pdf:hover { background: #dc2626; }
        .btn-reset { background: #6b7280; } .btn-reset:hover { background: #4b5563; }

        /* MİNİMAL FOOTER */
        .cert-minimal-footer { margin-top: auto; padding: 30px 20px; text-align: center; }
        .cert-home-link { display: inline-flex; align-items: center; gap: 6px; color: #4b5563; text-decoration: none; font-weight: 600; font-size: 14px; padding: 10px 20px; background: #fff; border: 1px solid #d1d5db; border-radius: 999px; transition: all 0.2s; margin-bottom: 15px; }
        .cert-home-link:hover { background: #f9fafb; color: #111827; border-color: #9ca3af; }
        .cert-copy { font-size: 12px; color: #9ca3af; margin: 0; }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>

    <div class="cert-container">
        
        <div id="cert-form-area" class="cert-form-area">
            <div class="cert-header">
                <h1>Sertifika Merkezi</h1>
                <p>10. Ulusal YBS Zirvesi katılım sertifikanızı almak veya doğrulamak için bilgilerinizi girin.</p>
            </div>

            <div id="cert-msg" class="cert-msg"></div>

            <div class="cert-form-group">
                <label>E-Posta Adresi</label>
                <input type="email" id="sorgu-email" class="cert-input" placeholder="ornek@ogrenci.edu.tr">
            </div>
            <button class="cert-btn" id="btn-sorgula-email" onclick="sorgula('email')">Sorgula ve İndir</button>

            <div class="cert-divider"><span>VEYA</span></div>

            <div class="cert-form-group">
                <label>Sertifika No (Doğrulama Kodu)</label>
                <input type="text" id="sorgu-kod" class="cert-input" placeholder="YBS26-XXXX-XXX" style="text-transform: uppercase;">
            </div>
            <button class="cert-btn secondary" id="btn-sorgula-kod" onclick="sorgula('kod')">Belgeyi Doğrula</button>
        </div>

        <div id="sertifika-wrapper">
            <div id="sertifika-sablonu">

                <!-- Dekoratif çerçeve -->
                <div class="s-outer-frame"></div>
                <div class="s-inner-frame"></div>

                <!-- Köşe süslemeleri (TL) -->
                <svg class="s-corner-svg s-corner-tl" viewBox="0 0 55 55" fill="none">
                    <polyline points="48,8 8,8 8,48" stroke="#c9a227" stroke-width="2.5"/>
                    <polyline points="41,15 15,15 15,41" stroke="#1a2c5b" stroke-width="1"/>
                    <polygon points="8,4 12,8 8,12 4,8" fill="#c9a227"/>
                </svg>
                <!-- Köşe süslemeleri (TR) -->
                <svg class="s-corner-svg s-corner-tr" viewBox="0 0 55 55" fill="none">
                    <polyline points="7,8 47,8 47,48" stroke="#c9a227" stroke-width="2.5"/>
                    <polyline points="14,15 40,15 40,41" stroke="#1a2c5b" stroke-width="1"/>
                    <polygon points="47,4 51,8 47,12 43,8" fill="#c9a227"/>
                </svg>
                <!-- Köşe süslemeleri (BL) -->
                <svg class="s-corner-svg s-corner-bl" viewBox="0 0 55 55" fill="none">
                    <polyline points="48,47 8,47 8,7" stroke="#c9a227" stroke-width="2.5"/>
                    <polyline points="41,40 15,40 15,14" stroke="#1a2c5b" stroke-width="1"/>
                    <polygon points="8,43 12,47 8,51 4,47" fill="#c9a227"/>
                </svg>
                <!-- Köşe süslemeleri (BR) -->
                <svg class="s-corner-svg s-corner-br" viewBox="0 0 55 55" fill="none">
                    <polyline points="7,47 47,47 47,7" stroke="#c9a227" stroke-width="2.5"/>
                    <polyline points="14,40 40,40 40,14" stroke="#1a2c5b" stroke-width="1"/>
                    <polygon points="47,43 51,47 47,51 43,47" fill="#c9a227"/>
                </svg>

                <div class="verify-badge" id="verify-badge"></div>

                <!-- Üst altın aksan çizgisi -->
                <div class="s-accent-top"></div>

                <!-- Header: Logo | Başlık | QR -->
                <div class="s-head">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/zirvelogo.png" alt="Zirve Logo">

                    <div class="s-head-center">
                        <span class="s-head-ornament-line">◆ ◆ ◆</span>
                            <h1 class="s-title">KATILIM SERTİFİKASI</h1>
                        <p class="s-sub">10. Ulusal YBS Öğrenci Zirvesi</p>
                    </div>

                    <div class="s-qr-container">
                        <canvas id="cert-qr"></canvas>
                    </div>
                </div>

                <!-- Altın ayırıcı -->
                <div class="s-gold-divider"></div>

                <!-- Gövde -->
                <div class="s-body">
                    <p class="s-body-prefix">Sayın</p>
                    <div class="s-name" id="print-name">İSİM SOYİSİM</div>
                    <div class="s-name-underline"></div>
                    <p class="s-diamond">◆ ◆ ◆</p>
                    <p class="s-text">
                        28–29 Mart 2026 tarihlerinde Düzce Üniversitesi ev sahipliğinde gerçekleştirilen<br>
                        <strong>"10. Ulusal Yönetim Bilişim Sistemleri Öğrenci Zirvesi"</strong><br>
                        kapsamındaki oturumlara gösterdiğiniz değerli katılım için teşekkür ederiz.
                    </p>
                </div>

                <!-- Alt bölüm: bilgi + imzalar -->
                <div class="s-gold-divider"></div>
                <div class="s-foot">
                    <div class="s-info-text">
                        <strong>Tarih:</strong> 28–29 Mart 2026<br>
                        <strong>Yer:</strong> Düzce Üniversitesi<br>
                        <strong>Belge No:</strong> <span id="print-code"></span><br>
                        <strong>Doğrulama:</strong> 2026.ybszirve.org.tr/sertifika
                    </div>

                    <div class="s-signatures">
                        <div class="s-sign">
                            <div class="s-sign-line"></div>
                            <h4 class="s-sign-name">Adem Arda Demiröz</h4>
                            <p class="s-sign-title">Organizasyon Komitesi</p>
                        </div>
                        <div class="s-sign">
                            <div class="s-sign-line"></div>
                            <h4 class="s-sign-name">Prof. Dr. Vahap Tecim</h4>
                            <p class="s-sign-title">YBS Enstitüsü Başkanı</p>
                        </div>
                    </div>
                </div>

                <!-- Alt altın aksan çizgisi -->
                <div class="s-accent-bottom"></div>

            </div>
        </div>

        <div class="download-btns" id="download-btns">
            <button class="btn-jpg" onclick="indir('jpg')">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                JPG İndir
            </button>
            <button class="btn-pdf" onclick="indir('pdf')">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                PDF İndir
            </button>
            <button class="btn-reset" onclick="location.href='<?php echo site_url('/sertifika'); ?>'">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 .49-4.5"></path></svg>
                Yeni Sorgu
            </button>
        </div>

    </div>

    <div class="cert-minimal-footer">
        <a href="<?php echo site_url(); ?>" class="cert-home-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            Zirve Ana Sayfasına Dön
        </a>
        <p class="cert-copy">&copy; <?php echo date('Y'); ?> 10. Ulusal YBS Öğrenci Zirvesi. Tüm hakları saklıdır.</p>
    </div>

    <div id="render-container" style="position: absolute; left: -9999px; top: 0; width: 1123px; height: 794px; background: white;"></div>

    <script>
        // OTOMATİK DOĞRULAMA KONTROLÜ
        document.addEventListener('DOMContentLoaded', function() {
            const autoCode = '<?php echo $auto_verify_code; ?>';
            if (autoCode !== '') {
                document.getElementById('sorgu-kod').value = autoCode;
                document.getElementById('verify-badge').style.display = 'block'; 
                sorgula('kod');
            }
        });

        function sorgula(tip) {
            const email = document.getElementById('sorgu-email').value;
            const kod = document.getElementById('sorgu-kod').value;
            const msgBox = document.getElementById('cert-msg');
            
            if(tip === 'email' && !email) { msgBox.className = 'cert-msg error'; msgBox.innerText = 'E-Posta giriniz.'; return; }
            if(tip === 'kod' && !kod) { msgBox.className = 'cert-msg error'; msgBox.innerText = 'Kod giriniz.'; return; }

            let data = new FormData();
            data.append('action', 'ybs_sorgula_sertifika');
            data.append('tip', tip);
            if(tip === 'email') data.append('deger', email);
            if(tip === 'kod') data.append('deger', kod);

            msgBox.className = 'cert-msg';
            msgBox.style.display = 'block';
            msgBox.innerText = 'Sistem kontrol ediliyor...';

            document.querySelectorAll('.cert-btn').forEach(b => b.style.opacity = '0.7');

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: data })
            .then(res => res.json())
            .then(response => {
                document.querySelectorAll('.cert-btn').forEach(b => b.style.opacity = '1');
                
                if(response.success) {
                    document.getElementById('print-name').innerText = response.data.name;
                    document.getElementById('print-code').innerText = response.data.code;
                    
                    // Yüksek Çözünürlüklü QR Oluşturma (CSS Küçültecek)
                    const verifyUrl = 'https://2026.ybszirve.org.tr/sertifika?verify=' + response.data.code;
                    new QRious({
                        element: document.getElementById('cert-qr'),
                        value: verifyUrl,
                        size: 300, // HD basılması için
                        level: 'H'
                    });

                    document.getElementById('cert-form-area').style.display = 'none';
                    document.getElementById('sertifika-wrapper').style.display = 'block';
                    document.getElementById('download-btns').style.display = 'flex';
                    msgBox.style.display = 'none';
                } else {
                    msgBox.classList.add('error');
                    msgBox.innerHTML = response.data;
                }
            })
            .catch(err => {
                document.querySelectorAll('.cert-btn').forEach(b => b.style.opacity = '1');
                msgBox.classList.add('error');
                msgBox.innerText = 'Sunucu bağlantı hatası. Lütfen sayfayı yenileyin.';
            });
        }

        async function generateCanvas() {
            return new Promise((resolve, reject) => {
                const sertifika = document.getElementById('sertifika-sablonu');
                const originalWrapper = document.getElementById('sertifika-wrapper'); 
                const renderWrapper = document.getElementById('render-container');  

                renderWrapper.appendChild(sertifika);

                html2canvas(sertifika, {
                    scale: 2, 
                    useCORS: true,
                    backgroundColor: "#ffffff",
                    width: 1123,
                    height: 794,
                    windowWidth: 1123,
                    windowHeight: 794
                }).then(canvas => {
                    originalWrapper.prepend(sertifika);
                    resolve(canvas);
                }).catch(err => {
                    originalWrapper.prepend(sertifika);
                    reject(err);
                });
            });
        }

        async function indir(format) {
            const name = document.getElementById('print-name').innerText.replace(/\s+/g, '_');
            const fileName = 'YBS_Zirvesi_Sertifikasi_' + name;

            try {
                document.querySelectorAll('.download-btns button').forEach(b => { b.style.opacity = '0.5'; b.disabled = true; });

                const canvas = await generateCanvas();

                if (format === 'jpg') {
                    let link = document.createElement('a');
                    link.download = fileName + '.jpg';
                    link.href = canvas.toDataURL('image/jpeg', 1.0);
                    link.click();
                } 
                else if (format === 'pdf') {
                    const { jsPDF } = window.jspdf;
                    const pdf = new jsPDF({ orientation: 'l', unit: 'mm', format: 'a4' }); 
                    const imgData = canvas.toDataURL('image/jpeg', 1.0);
                    
                    pdf.addImage(imgData, 'JPEG', 0, 0, 297, 210);
                    pdf.save(fileName + '.pdf');
                }
                
                document.querySelectorAll('.download-btns button').forEach(b => { b.style.opacity = '1'; b.disabled = false; });
            } catch (err) {
                alert('Belge oluşturulurken bir hata meydana geldi. Lütfen tekrar deneyin.');
                document.querySelectorAll('.download-btns button').forEach(b => { b.style.opacity = '1'; b.disabled = false; });
            }
        }
    </script>
    <?php
    return ob_get_clean();
}
// =========================================================================
// AJAX: YOKLAMA KAYDETME İŞLEMİ (UNDEFINED HATASINI ÇÖZEN KISIM)
// =========================================================================
add_action('wp_ajax_ybs_save_attendance', 'ybs_save_attendance_func');
add_action('wp_ajax_nopriv_ybs_save_attendance', 'ybs_save_attendance_func'); // Ziyaretçiler için şart!

function ybs_save_attendance_func() {
    global $wpdb;

    $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';

    if (empty($session_id) || empty($name) || empty($email) || empty($phone)) {
        wp_send_json_error('Lütfen tüm alanları eksiksiz doldurun.');
    }

    $t_att = $wpdb->prefix . 'ybs_attendance';

    // Kişi bu oturuma daha önce yoklama vermiş mi kontrol et
    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $t_att WHERE session_id = %d AND user_email = %s", $session_id, $email));

    if ($exists > 0) {
        wp_send_json_error('Bu e-posta adresiyle bu oturuma zaten yoklama vermişsiniz.');
    }

    // Yoklamayı Veritabanına Ekle
    $inserted = $wpdb->insert($t_att, [
        'session_id' => $session_id,
        'user_name'  => $name,
        'user_email' => $email,
        'user_phone' => $phone
    ]);

    if ($inserted) {
        wp_send_json_success('Yoklama başarıyla kaydedildi.');
    } else {
        wp_send_json_error('Sistemsel bir hata oluştu, kaydedilemedi.');
    }
}
// 4. AJAX: ARKA PLAN SORGUSU VE MANTIK İŞLETMESİ
add_action('wp_ajax_ybs_sorgula_sertifika', 'ybs_ajax_sorgula_sertifika');
add_action('wp_ajax_nopriv_ybs_sorgula_sertifika', 'ybs_ajax_sorgula_sertifika');
function ybs_ajax_sorgula_sertifika() {
    global $wpdb;
    $t_res = $wpdb->prefix . 'ybs_reservations';
    $t_att = $wpdb->prefix . 'ybs_attendance';
    $t_ses = $wpdb->prefix . 'ybs_sessions';

    $tip = sanitize_text_field($_POST['tip']); 
    $deger = sanitize_text_field(trim($_POST['deger']));

    if(empty($deger)) wp_send_json_error('Lütfen bilgileri eksiksiz girin.');

    if ($tip === 'kod') {
        $user = $wpdb->get_row($wpdb->prepare("SELECT user_name, certificate_code FROM $t_res WHERE certificate_code = %s", $deger));
        if ($user) {
            wp_send_json_success(['name' => $user->user_name, 'code' => $user->certificate_code]);
        } else {
            wp_send_json_error('Bu doğrulama koduna ait sertifika bulunamadı.');
        }
    } 
    else if ($tip === 'email') {
        if (!filter_var($deger, FILTER_VALIDATE_EMAIL)) wp_send_json_error('Geçersiz e-posta formatı.');

        // Katılım Oranı Hesaplama (%1 Kuralı)
        $total_sessions = $wpdb->get_var("SELECT COUNT(*) FROM $t_ses");
        if ($total_sessions == 0) $total_sessions = 1; 

        // Öğrencinin katıldığı oturum sayısı
        $attended_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT session_id) FROM $t_att WHERE user_email = %s", $deger));
        $ratio = $attended_count / $total_sessions;
        $yuzde = number_format($ratio * 100);

        if ($ratio >= 0.01) {
            // Hak kazanmış. Kaydı var mı bakıyoruz
            $user_res = $wpdb->get_row($wpdb->prepare("SELECT id, user_name, certificate_code FROM $t_res WHERE user_email = %s LIMIT 1", $deger));
            
            if ($user_res) {
                // Önceden bilet almış biriyse
                $cert_code = $user_res->certificate_code;
                if (empty($cert_code)) {
                    $cert_code = ybs_generate_cert_code();
                    $wpdb->update($t_res, ['certificate_code' => $cert_code], ['id' => $user_res->id]);
                }
                wp_send_json_success(['name' => $user_res->user_name, 'code' => $cert_code]);
            } else {
                // Koltuk Almamış ama yoklamaya katılmış kişi! Otomatik veritabanına ekle.
                $user_att = $wpdb->get_row($wpdb->prepare("SELECT user_name, user_phone FROM $t_att WHERE user_email = %s LIMIT 1", $deger));
                
                if ($user_att) {
                    $cert_code = ybs_generate_cert_code();
                    $bilet_token = wp_generate_password(20, false, false);
                    
                    $wpdb->insert($t_res, [
                        'seat_id' => 'BLTSZ-' . rand(100, 999),
                        'user_name' => $user_att->user_name,
                        'user_email' => $deger,
                        'user_phone' => $user_att->user_phone,
                        'category' => 'standard',
                        'note' => 'Yoklamadan Geldi',
                        'status' => 'approved',
                        'bilet_token' => $bilet_token,
                        'certificate_code' => $cert_code,
                        'is_checked_in' => 1
                    ]);
                    
                    wp_send_json_success(['name' => $user_att->user_name, 'code' => $cert_code]);
                } else {
                    // %1 katılım sağlamış ama yoklama kaydında isim/telefon yok → yanıtsız kalma
                    wp_send_json_error('Sertifika oluşturulamadı: Yoklama kaydınız sistemde bulunamadı. Lütfen organizasyon ekibiyle iletişime geçin.');
                }
            }
        } else {
            // Eğer hiç kaydı yoksa ve yoklamada da yoksa
            $is_registered = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $t_res WHERE user_email = %s", $deger));
            if ($is_registered == 0 && $attended_count == 0) {
                 wp_send_json_error('Bu e-posta adresiyle yapılmış bir kayıt veya yoklama bulunamadı.');
            } else {
                 // Kaydı var ama katılımı yetersiz
                 wp_send_json_error("Sertifika hak edişiniz bulunmuyor.<br><br>Gerekli katılım: <strong>%1</strong><br>Sizin katılımınız: <strong>%$yuzde</strong> ($attended_count / $total_sessions oturum)");
            }
        }
    }
}

// =========================================================================
// SMTP AYARLARI (GELİŞTİRİLMİŞ VE HATALARDAN ARINDIRILMIŞ)
// =========================================================================
// Gönderici adresini zorla
add_filter('wp_mail_from', function() { return 'noreply@duybs.com'; });
add_filter('wp_mail_from_name', function() { return '10. Ulusal YBS Zirvesi'; });

add_action('phpmailer_init', 'ybs_custom_smtp_settings');
function ybs_custom_smtp_settings($phpmailer) {
    $phpmailer->isSMTP();
    $phpmailer->Host       = 'masked.duybs.com';
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = 333;
    $phpmailer->Username   = 'masked@duybs.com';
    $phpmailer->Password   = 'masked';
    $phpmailer->SMTPSecure = 'tls'; 
    $phpmailer->CharSet    = 'UTF-8';
    
    // MAİLLERİN GİTMEME SORUNUNU ÇÖZEN KISIM (SSL Doğrulamasını Atlar)
    $phpmailer->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
}
// =========================================================================
// AJAX: SERTİFİKA MAİLİ GÖNDERME İŞLEMİ
// =========================================================================
add_action('wp_ajax_ybs_send_cert_email', 'ybs_ajax_send_cert_email');
function ybs_ajax_send_cert_email() {
    if (!current_user_can('manage_options')) wp_send_json_error('Yetkisiz işlem');

    global $wpdb;
    $t_res = $wpdb->prefix . 'ybs_reservations';

    $email = sanitize_email($_POST['email']);
    $cert_code = sanitize_text_field($_POST['cert_code']);
    $name = sanitize_text_field($_POST['name']);

    if(empty($email) || empty($cert_code)) {
        wp_send_json_error('Eksik bilgi. Sertifika kodu henüz üretilmemiş olabilir.');
    }

    $cert_link = 'https://2026.ybszirve.org.tr/sertifika?verify=' . $cert_code;
    $subject = 'Katılım Sertifikanız - 10. Ulusal YBS Zirvesi';
    
    // Şık HTML Mail Şablonu
    $message = "
    <div style='font-family: -apple-system, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;'>
        <div style='background-color: #1e3a8a; padding: 25px; text-align: center; color: #fff;'>
            <h2 style='margin: 0; font-size: 22px; letter-spacing: 1px;'>10. Ulusal YBS Zirvesi</h2>
        </div>
        <div style='padding: 30px; color: #374151; line-height: 1.6; font-size: 15px;'>
            <p>Sayın <strong>$name</strong>,</p>
            <p>Düzce Üniversitesi ev sahipliğinde gerçekleştirilen 10. Ulusal Yönetim Bilişim Sistemleri Öğrenci Zirvesi'ne katılımınız için teşekkür ederiz.</p>
            <p>Etkinlik oturumlarındaki devamlılığınız doğrultusunda <strong>Katılım Sertifikası</strong> almaya hak kazandınız. Belgenize aşağıdaki butona tıklayarak ulaşabilirsiniz.</p>
            
            <div style='text-align: center; margin: 35px 0;'>
                <a href='$cert_link' style='background-color: #2563eb; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;'>Sertifikanızı Görüntüleyin</a>
            </div>
            
            <p style='font-size: 13px; color: #6b7280; background: #f9fafb; padding: 12px; border-radius: 6px; text-align: center; border: 1px dashed #d1d5db;'>
                <strong>Sertifika No:</strong> $cert_code
            </p>
        </div>
        <div style='background-color: #f3f4f6; padding: 15px; text-align: center; font-size: 12px; color: #9ca3af;'>
            &copy; 2026 YBS Zirvesi Organizasyon Komitesi<br>
            Bu e-posta sistem tarafından otomatik gönderilmiştir.
        </div>
    </div>
    ";

    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Mail Göndermeyi Dene
    if(wp_mail($email, $subject, $message, $headers)) {
        // BAŞARILIYSA VERİTABANINI GÜNCELLE
        $wpdb->update($t_res, ['cert_emailed' => 1], ['user_email' => $email]);
        wp_send_json_success('Mail başarıyla gönderildi.');
    } else {
        global $phpmailer;
        $error_msg = isset($phpmailer->ErrorInfo) ? $phpmailer->ErrorInfo : 'Bilinmeyen SMTP hatası.';
        wp_send_json_error("Mail gönderilemedi. Hata: " . $error_msg);
    }
}

// =========================================================================
// AJAX: TOPLU SERTİFİKA MAİLİ GÖNDERME
// =========================================================================
add_action('wp_ajax_ybs_send_all_cert_emails', 'ybs_ajax_send_all_cert_emails');
function ybs_ajax_send_all_cert_emails() {
    if (!current_user_can('manage_options')) wp_send_json_error('Yetkisiz');

    global $wpdb;
    $t_res = $wpdb->prefix . 'ybs_reservations';
    $t_att = $wpdb->prefix . 'ybs_attendance';
    $t_ses = $wpdb->prefix . 'ybs_sessions';

    $offset = max(0, intval($_POST['offset']));
    $limit  = max(1, min(10, intval($_POST['limit'])));

    $total_sessions = (int)$wpdb->get_var("SELECT COUNT(*) FROM $t_ses");
    if ($total_sessions < 1) $total_sessions = 1;

    // Sertifika kodu olan ama henüz mail gönderilmemiş hak sahipleri
    $rows = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT r.user_name, r.user_email, r.certificate_code
        FROM $t_res r
        WHERE r.certificate_code != ''
          AND (r.cert_emailed IS NULL OR r.cert_emailed = 0)
          AND r.user_email NOT LIKE '%%@ybszirve.local%%'
          AND r.user_email NOT LIKE '%%@gorevli.temp%%'
        ORDER BY r.id ASC
        LIMIT %d OFFSET %d
    ", $limit, $offset));

    $total_pending = (int)$wpdb->get_var(
        "SELECT COUNT(DISTINCT user_email) FROM $t_res WHERE certificate_code != '' AND (cert_emailed IS NULL OR cert_emailed = 0) AND user_email NOT LIKE '%@ybszirve.local%' AND user_email NOT LIKE '%@gorevli.temp%'"
    );

    // cert_emailed sütunu yoksa ekle
    $col_check = $wpdb->get_results("SHOW COLUMNS FROM $t_res LIKE 'cert_emailed'");
    if (empty($col_check)) {
        $wpdb->query("ALTER TABLE $t_res ADD COLUMN cert_emailed tinyint(1) DEFAULT 0");
    }

    $sent = 0;
    $failed = 0;

    foreach ($rows as $row) {
        $cert_link = 'https://2026.ybszirve.org.tr/sertifika?verify=' . $row->certificate_code;
        $subject   = 'Katılım Sertifikanız - 10. Ulusal YBS Zirvesi';

        $body = '<!DOCTYPE html><html><body style="margin:0;padding:0;background:#f3f4f6;">'
            . '<div style="max-width:580px;margin:30px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">'
            . '<div style="background:linear-gradient(135deg,#1e3a8a,#1a2c5b);padding:30px 40px;text-align:center;">'
            . '<h1 style="color:#f0d060;font-size:24px;margin:0;letter-spacing:2px;">10. Ulusal YBS Öğrenci Zirvesi</h1>'
            . '</div>'
            . '<div style="padding:35px 40px;">'
            . '<p style="font-size:16px;color:#374151;">Sayın <strong>' . esc_html($row->user_name) . '</strong>,</p>'
            . '<p style="color:#6b7280;line-height:1.7;">28–29 Mart 2026 tarihlerinde Düzce Üniversitesi\'nde gerçekleştirilen zirvemize katılımınız için teşekkür ederiz.</p>'
            . '<p style="color:#6b7280;">Sertifikanıza aşağıdaki kodla ulaşabilirsiniz:</p>'
            . '<div style="background:#f0f6fc;border:1px dashed #93c5fd;border-radius:8px;padding:16px;text-align:center;margin:20px 0;">'
            . '<div style="font-size:11px;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:1px;">Sertifika Kodunuz</div>'
            . '<div style="font-size:22px;font-weight:900;color:#1e3a8a;font-family:monospace;">' . esc_html($row->certificate_code) . '</div>'
            . '</div>'
            . '<div style="text-align:center;margin:24px 0;">'
            . '<a href="' . esc_url($cert_link) . '" style="background:#1e3a8a;color:#fff;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:15px;">Sertifikamı Görüntüle & İndir</a>'
            . '</div>'
            . '</div>'
            . '<div style="background:#f9fafb;padding:16px 40px;text-align:center;font-size:12px;color:#9ca3af;">2026.ybszirve.org.tr</div>'
            . '</div></body></html>';

        $ok = wp_mail($row->user_email, $subject, $body, ['Content-Type: text/html; charset=UTF-8']);
        if ($ok) {
            $wpdb->update($t_res, ['cert_emailed' => 1], ['certificate_code' => $row->certificate_code]);
            $sent++;
        } else {
            $failed++;
        }
    }

    wp_send_json_success([
        'sent'      => $sent,
        'failed'    => $failed,
        'processed' => count($rows),
        'has_more'  => ($offset + count($rows)) < $total_pending,
    ]);
}


// =========================================================================
// ADMİN: TÜM SALONU (KOLTUKLARI) BOŞALT
// =========================================================================
add_action('wp_ajax_ybs_admin_delete_all_seats', 'ybs_admin_delete_all_seats');
function ybs_admin_delete_all_seats() {
    if (!current_user_can('manage_options')) wp_die();
    global $wpdb;
    $table = $wpdb->prefix . 'ybs_reservations';
    
    // TRUNCATE komutu tüm tabloyu anında temizler ve ID sayacını sıfırlar
    $wpdb->query("TRUNCATE TABLE $table"); 
    
    // İsteğe bağlı: Çoklu satış (overbook) ayarlarını da sıfırlamak istersen alttaki satırın başındaki // işaretini kaldırabilirsin.
    // update_option('ybs_multi_seats', []); 
    
    wp_send_json_success();
}

// =========================================================================
// ADMİN HARİTASI İÇİN AJAX İŞLEMLERİ (SORUNSUZ SÜRÜM)
// =========================================================================

// 1. Çoklu Veya Tekli Koltuk Kaydetme (Admin Panelinden Toplu Blokaj İçin Güncellendi)
add_action('wp_ajax_ybs_admin_manual_bulk_add', 'ybs_admin_manual_bulk_add_func');
function ybs_admin_manual_bulk_add_func() {
    if (!current_user_can('manage_options')) wp_send_json_error('Yetkisiz işlem.');

    // AJAX isteklerinde admin_init çalışmaz; şema güncelliğini garantile
    ybs_setup_database();
    
    global $wpdb;
    $table = $wpdb->prefix . 'ybs_reservations';
    
    $seat_ids = isset($_POST['seats']) ? $_POST['seats'] : [];
    if (!is_array($seat_ids)) {
        $seat_ids = explode(',', $seat_ids);
    }

    if (empty($seat_ids)) {
        wp_send_json_error('Koltuk seçilmedi.');
    }

    $cat = sanitize_text_field($_POST['category']);
    $note = sanitize_text_field($_POST['note']);
    $color = sanitize_text_field($_POST['color']);
    $is_multi = (isset($_POST['is_multi']) && $_POST['is_multi'] == '1') ? true : false;
    
    $multi_seats = get_option('ybs_multi_seats', []);
    if (!is_array($multi_seats)) $multi_seats = [];

    foreach($seat_ids as $seat_id) {
        $seat_id = sanitize_text_field($seat_id);
        if (empty($seat_id)) continue;
        
        // Çoklu satış seçildiyse ve listede yoksa ekle
        if ($is_multi && !in_array($seat_id, $multi_seats)) {
            $multi_seats[] = $seat_id;
        }

        // ==========================================
        // TOPLU GRUP REZERVASYONU MANTIĞI EKLENDİ
        // Var olan kayıtlar silinmez; sadece yeni bir kayıt eklenir.
        // Böylece çoklu satış kapatılsa bile geçmiş kayıtlar korunur.
        // ==========================================
        
        // İsmi nottan al, yoksa "Grup Rezervasyonu" yaz
        $display_name = !empty($note) ? $note : 'Grup Rezervasyonu';
        
        // Adminin kendi e-postasını kirletmemek için koltuğa özel benzersiz, sahte bir misafir maili üret
        $fake_email = 'misafir_' . strtolower(str_replace('-', '_', $seat_id)) . '_' . rand(100,999) . '@ybszirve.local';

        $benzersiz_token = md5(uniqid($seat_id . mt_rand(), true));

        // Yeni Kaydı Ekle
        $inserted = $wpdb->insert($table, [
            'seat_id' => $seat_id,
            'user_name' => $display_name,
            'user_email' => $fake_email, // Kendi mailin yerine sahte mail kaydedilir
            'user_phone' => '-',
            'category' => $cat,
            'note' => $display_name,
            'color' => $color,
            'status' => 'approved',
            'is_checked_in' => 0,
            'kvkk_sponsor_izin' => ($cat === 'club' ? 1 : 0), // Dışarıdan gelen club kayıtları için izin verildi
            'bilet_token' => $benzersiz_token,
            'reservation_date' => current_time('mysql')
        ]);
        
        if ($inserted === false) {
            wp_send_json_error('DB Hatası: ' . $wpdb->last_error);
        }
    }
    
    // Çoklu satış (overbook) listesini güncelle
    update_option('ybs_multi_seats', array_values($multi_seats));
    
    wp_send_json_success('Başarıyla Kaydedildi.');
}

// 2. Çoklu Satışı (Overbook) Aç / Kapat
add_action('wp_ajax_ybs_admin_toggle_multi', 'ybs_admin_toggle_multi_func');
function ybs_admin_toggle_multi_func() {
    if (!current_user_can('manage_options')) wp_send_json_error('Yetkisiz');
    
    $seat_id = sanitize_text_field($_POST['seat_id']);
    $action = sanitize_text_field($_POST['do']); // 'add_multi' veya 'remove_multi'
    
    $multi = get_option('ybs_multi_seats', []);
    if (!is_array($multi)) $multi = [];
    
    if ($action === 'add_multi' && !in_array($seat_id, $multi)) {
        $multi[] = $seat_id;
    } elseif ($action === 'remove_multi') {
        $multi = array_diff($multi, [$seat_id]);
    }
    
    update_option('ybs_multi_seats', array_values($multi));
    wp_send_json_success();
}

// 3. Tekil Kayıt Sil (Örneğin çoklu koltuktaki tek bir kişiyi silerken)
add_action('wp_ajax_ybs_admin_delete_single', 'ybs_admin_delete_single_func');
function ybs_admin_delete_single_func() {
    if (!current_user_can('manage_options')) wp_die();
    global $wpdb;
    $id = intval($_POST['id']);
    $wpdb->delete($wpdb->prefix . 'ybs_reservations', ['id' => $id]);
    wp_send_json_success();
}

// =========================================================================
// TOPLU E-POSTA GÖNDERİMİ
// =========================================================================

add_action('admin_menu', 'ybs_bulk_email_menu');
function ybs_bulk_email_menu() {
    add_submenu_page('ybs-hall-manager', 'Toplu Mail Gönder', 'Toplu Mail', 'manage_options', 'ybs-bulk-email', 'ybs_bulk_email_page');
}

function ybs_bulk_email_page() {
    global $wpdb;
    $t_res = $wpdb->prefix . 'ybs_reservations';

    $categories = [
        'all'      => 'Tümü',
        'standard' => 'Standart',
        'protocol' => 'Protokol',
        'sponsor'  => 'Sponsor',
        'club'     => 'Kulüp',
        'staff'    => 'Görevli',
    ];

    // Sayım için kategoriye göre kişi sayıları
    $counts = [];
    foreach ($categories as $key => $label) {
        if ($key === 'all') {
            $counts[$key] = $wpdb->get_var("SELECT COUNT(DISTINCT user_email) FROM $t_res WHERE user_email NOT LIKE '%@ybszirve.local%'");
        } else {
            $counts[$key] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT user_email) FROM $t_res WHERE category = %s AND user_email NOT LIKE '%%@ybszirve.local%%'", $key));
        }
    }
    ?>
    <div class="wrap">
        <h1>📧 Toplu E-posta Gönderimi</h1>
        <hr class="wp-header-end">

        <div style="display:grid; grid-template-columns:340px 1fr; gap:24px; margin-top:20px;">

            <!-- SOL: FORM -->
            <div style="background:#fff; border:1px solid #ccd0d4; border-radius:8px; padding:20px;">
                <h2 style="margin-top:0; font-size:16px; border-bottom:1px solid #eee; padding-bottom:12px;">Gönderim Ayarları</h2>

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-weight:bold; margin-bottom:6px;">Alıcı Grubu</label>
                    <select id="bulk-cat" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" onchange="updateCount()">
                        <?php foreach ($categories as $key => $label): ?>
                            <option value="<?php echo $key; ?>"><?php echo $label; ?> (<?php echo $counts[$key]; ?> kişi)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-weight:bold; margin-bottom:6px;">Konu</label>
                    <input type="text" id="bulk-subject" class="widefat" placeholder="10. Ulusal YBS Zirvesi - Duyuru" style="padding:8px;">
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-weight:bold; margin-bottom:6px;">Mesaj İçeriği</label>
                    <p style="font-size:12px; color:#666; margin:0 0 6px;">
                        Kullanılabilir değişkenler: <code>{{isim}}</code>
                    </p>
                    <textarea id="bulk-body" style="width:100%; height:200px; padding:10px; border:1px solid #ddd; border-radius:4px; font-size:13px; resize:vertical;" placeholder="Sayın {{isim}},&#10;&#10;10. Ulusal YBS Öğrenci Zirvesi hakkında duyurmak istediğimiz..."></textarea>
                </div>

                <div style="background:#f0f6fc; border:1px solid #c3d8f0; border-radius:6px; padding:12px; margin-bottom:16px; font-size:13px;">
                    <strong>📊 Gönderilecek:</strong> <span id="send-count" style="font-size:18px; font-weight:900; color:#2271b1;"><?php echo $counts['all']; ?></span> kişi
                </div>

                <button id="btn-start-send" class="button button-primary" style="width:100%; padding:10px; font-size:14px;" onclick="startBulkSend()">
                    📧 Gönderimi Başlat
                </button>
            </div>

            <!-- SAĞ: İLERLEME ve LOG -->
            <div style="background:#fff; border:1px solid #ccd0d4; border-radius:8px; padding:20px;">
                <h2 style="margin-top:0; font-size:16px; border-bottom:1px solid #eee; padding-bottom:12px;">Gönderim Durumu</h2>

                <div id="bulk-idle" style="text-align:center; padding:40px 20px; color:#9ca3af;">
                    <div style="font-size:48px; margin-bottom:12px;">📭</div>
                    <p>Gönderim başlatılmadı. Sol taraftan ayarları yapıp "Gönderimi Başlat" butonuna tıklayın.</p>
                </div>

                <div id="bulk-progress-area" style="display:none;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <span id="prog-label" style="font-weight:bold; font-size:14px;">Gönderiliyor...</span>
                        <span id="prog-counts" style="font-size:13px; color:#666;">0 / 0</span>
                    </div>
                    <div style="background:#e5e7eb; border-radius:999px; height:10px; margin-bottom:16px; overflow:hidden;">
                        <div id="prog-bar" style="height:100%; background:#2271b1; border-radius:999px; transition:width 0.3s; width:0%;"></div>
                    </div>

                    <div id="bulk-log" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; padding:12px; height:300px; overflow-y:auto; font-family:monospace; font-size:12px; line-height:1.8;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    const catCounts = <?php echo json_encode($counts); ?>;

    function updateCount() {
        const cat = document.getElementById('bulk-cat').value;
        document.getElementById('send-count').innerText = catCounts[cat] || 0;
    }

    async function startBulkSend() {
        const cat = document.getElementById('bulk-cat').value;
        const subject = document.getElementById('bulk-subject').value.trim();
        const body = document.getElementById('bulk-body').value.trim();

        if (!subject || !body) {
            alert('Lütfen konu ve mesaj içeriğini doldurun.');
            return;
        }

        const count = parseInt(catCounts[cat] || 0);
        if (count === 0) {
            alert('Bu grupta gönderilecek kişi yok.');
            return;
        }

        if (!confirm(`${count} kişiye mail gönderilecek. Emin misiniz?`)) return;

        document.getElementById('bulk-idle').style.display = 'none';
        document.getElementById('bulk-progress-area').style.display = 'block';
        document.getElementById('btn-start-send').disabled = true;
        document.getElementById('btn-start-send').innerText = 'Gönderiliyor...';

        const log = document.getElementById('bulk-log');
        log.innerHTML = '';

        let offset = 0;
        const batchSize = 5;
        let totalSent = 0;
        let totalFailed = 0;

        function updateBar(done, total) {
            const pct = total > 0 ? Math.round((done / total) * 100) : 0;
            document.getElementById('prog-bar').style.width = pct + '%';
            document.getElementById('prog-counts').innerText = done + ' / ' + total;
        }

        updateBar(0, count);

        async function sendBatch() {
            const fd = new FormData();
            fd.append('action', 'ybs_send_bulk_email');
            fd.append('category', cat);
            fd.append('subject', subject);
            fd.append('body', body);
            fd.append('offset', offset);
            fd.append('limit', batchSize);

            const res = await fetch(ajaxurl, { method: 'POST', body: fd });
            const data = await res.json();

            if (!data.success) {
                log.innerHTML += `<span style="color:red;">❌ Hata: ${data.data}</span>\n`;
                finalize();
                return;
            }

            data.data.results.forEach(r => {
                const color = r.ok ? '#047857' : '#b91c1c';
                const icon = r.ok ? '✅' : '❌';
                log.innerHTML += `<span style="color:${color};">${icon} ${r.email} — ${r.msg}</span>\n`;
                if (r.ok) totalSent++; else totalFailed++;
            });

            log.scrollTop = log.scrollHeight;
            offset += data.data.results.length;
            updateBar(offset, count);

            if (data.data.has_more) {
                await sendBatch();
            } else {
                finalize();
            }
        }

        function finalize() {
            document.getElementById('prog-label').innerText = '✅ Tamamlandı';
            document.getElementById('prog-bar').style.background = '#10b981';
            document.getElementById('btn-start-send').disabled = false;
            document.getElementById('btn-start-send').innerText = '📧 Gönderimi Başlat';
            log.innerHTML += `\n<strong>— Toplam: ${totalSent} başarılı, ${totalFailed} başarısız —</strong>`;
        }

        await sendBatch();
    }
    </script>
    <?php
}

add_action('wp_ajax_ybs_send_bulk_email', 'ybs_ajax_send_bulk_email');
function ybs_ajax_send_bulk_email() {
    if (!current_user_can('manage_options')) wp_send_json_error('Yetkisiz');

    global $wpdb;
    $t_res = $wpdb->prefix . 'ybs_reservations';

    $category = sanitize_text_field($_POST['category']);
    $subject  = sanitize_text_field($_POST['subject']);
    $body_raw = wp_kses_post($_POST['body']);
    $offset   = max(0, intval($_POST['offset']));
    $limit    = max(1, min(20, intval($_POST['limit'])));

    if (empty($subject) || empty($body_raw)) wp_send_json_error('Konu veya içerik boş.');

    if ($category === 'all') {
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT user_email, user_name FROM $t_res WHERE user_email NOT LIKE '%%@ybszirve.local%%' LIMIT %d OFFSET %d",
            $limit, $offset
        ));
        $total = (int)$wpdb->get_var("SELECT COUNT(DISTINCT user_email) FROM $t_res WHERE user_email NOT LIKE '%@ybszirve.local%'");
    } else {
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT user_email, user_name FROM $t_res WHERE category = %s AND user_email NOT LIKE '%%@ybszirve.local%%' LIMIT %d OFFSET %d",
            $category, $limit, $offset
        ));
        $total = (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT user_email) FROM $t_res WHERE category = %s AND user_email NOT LIKE '%%@ybszirve.local%%'", $category));
    }

    $results = [];
    foreach ($rows as $row) {
        $personalized = str_replace('{{isim}}', $row->user_name, $body_raw);
        $html_body = nl2br(esc_html($personalized));
        $mail_body = '<div style="font-family:Arial,sans-serif;font-size:14px;line-height:1.7;color:#333;max-width:600px;margin:auto;padding:20px;">'
            . $html_body
            . '<br><br><hr style="border:none;border-top:1px solid #eee;">'
            . '<p style="font-size:12px;color:#999;text-align:center;">10. Ulusal YBS Öğrenci Zirvesi — 2026.ybszirve.org.tr</p>'
            . '</div>';

        $sent = wp_mail($row->user_email, $subject, $mail_body, ['Content-Type: text/html; charset=UTF-8']);
        $results[] = ['email' => $row->user_email, 'ok' => $sent, 'msg' => $sent ? 'Gönderildi' : 'Gönderilemedi'];
    }

    wp_send_json_success([
        'results'  => $results,
        'has_more' => ($offset + count($results)) < $total,
    ]);
}

// 4. Koltuğu Komple Boşalt (Koltuktaki Herkesi Siler)
add_action('wp_ajax_ybs_admin_delete_seat_all', 'ybs_admin_delete_seat_all_func');
function ybs_admin_delete_seat_all_func() {
    if (!current_user_can('manage_options')) wp_die();
    global $wpdb;
    $seat_id = sanitize_text_field($_POST['seat_id']);
    $wpdb->delete($wpdb->prefix . 'ybs_reservations', ['seat_id' => $seat_id]);
    
    // Komple boşalttığı için çoklu satış statüsünü de sıfırlıyoruz
    $multi = get_option('ybs_multi_seats', []);
    if (is_array($multi) && in_array($seat_id, $multi)) {
        $multi = array_diff($multi, [$seat_id]);
        update_option('ybs_multi_seats', array_values($multi));
    }
    
    wp_send_json_success();
}

// =========================================================================
// ETKİNLİK ÖZETİ DASHBOARD
// =========================================================================

add_action('admin_menu', 'ybs_etkinlik_ozeti_menu');
function ybs_etkinlik_ozeti_menu() {
    add_menu_page('Etkinlik Özeti', 'Etkinlik Özeti', 'manage_options', 'ybs-etkinlik-ozeti', 'ybs_etkinlik_ozeti_page', 'dashicons-chart-area', 4);
}

add_action('wp_ajax_ybs_fazla_gelen_kayit', 'ybs_fazla_gelen_kayit_func');
function ybs_fazla_gelen_kayit_func() {
    if (!current_user_can('manage_options')) wp_send_json_error('Yetkisiz');
    global $wpdb;

    $name  = sanitize_text_field($_POST['user_name'] ?? '');
    $email = sanitize_email($_POST['user_email'] ?? '');
    $phone = sanitize_text_field($_POST['user_phone'] ?? '');
    $note  = sanitize_text_field($_POST['note'] ?? '');

    if (empty($name) || empty($email) || empty($phone)) {
        wp_send_json_error('Ad Soyad, E-Posta ve Telefon zorunludur.');
    }
    if (!is_email($email)) {
        wp_send_json_error('Geçerli bir e-posta adresi girin.');
    }
    if (!preg_match('/^05[0-9]{9}$/', $phone)) {
        wp_send_json_error('Telefon 05 ile başlayan 11 haneli formatta olmalıdır.');
    }

    $t_res = $wpdb->prefix . 'ybs_reservations';
    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $t_res WHERE user_email = %s", $email));
    if ($exists > 0) {
        wp_send_json_error('Bu e-posta sistemde zaten kayıtlı.');
    }

    $seat_id     = 'GELEN-' . rand(1000, 9999);
    $bilet_token = md5(uniqid(mt_rand(), true));

    $result = $wpdb->insert($t_res, [
        'seat_id'          => $seat_id,
        'user_name'        => $name,
        'user_email'       => $email,
        'user_phone'       => $phone,
        'category'         => 'standard',
        'note'             => !empty($note) ? $note : 'Fazla Gelen',
        'status'           => 'approved',
        'bilet_token'      => $bilet_token,
        'is_checked_in'    => 1,
        'reservation_date' => current_time('mysql'),
    ]);

    if ($result === false) {
        wp_send_json_error('Kayıt eklenemedi: ' . $wpdb->last_error);
    }

    wp_send_json_success(['seat_id' => $seat_id]);
}

// Oturum detay: kimler katılmış (AJAX)
add_action('wp_ajax_ybs_ozet_session_detail', 'ybs_ozet_session_detail_func');
function ybs_ozet_session_detail_func() {
    if (!current_user_can('manage_options')) wp_send_json_error('Yetkisiz erişim.');

    $session_id = intval($_POST['session_id'] ?? 0);
    if (!$session_id) wp_send_json_error('Geçersiz oturum ID.');

    global $wpdb;
    $t_att = $wpdb->prefix . 'ybs_attendance';
    $t_res = $wpdb->prefix . 'ybs_reservations';

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT a.user_name, a.user_email,
                COALESCE(
                    (SELECT r.user_phone FROM $t_res r
                     WHERE r.user_email = a.user_email
                       AND r.user_email NOT LIKE '%@ybszirve.local'
                       AND r.user_email NOT LIKE '%@gorevli.temp'
                     LIMIT 1),
                '') as user_phone
         FROM $t_att a
         WHERE a.session_id = %d
         ORDER BY a.user_name ASC",
        $session_id
    ), ARRAY_A);

    wp_send_json_success($results);
}

function ybs_etkinlik_ozeti_page() {
    global $wpdb;
    $t_res = $wpdb->prefix . 'ybs_reservations';
    $t_att = $wpdb->prefix . 'ybs_attendance';
    $t_ses = $wpdb->prefix . 'ybs_sessions';

    $total_sessions = (int) $wpdb->get_var("SELECT COUNT(*) FROM $t_ses");
    if ($total_sessions === 0) $total_sessions = 1;

    // --- KATILIMCI SİLME ---
    if (isset($_GET['delete_attendee']) && current_user_can('manage_options')) {
        $email_to_delete = sanitize_email($_GET['delete_attendee']);
        if (!empty($email_to_delete)) {
            $deleted_att = $wpdb->delete($t_att, ['user_email' => $email_to_delete]);
            $deleted_res = $wpdb->delete($t_res, ['user_email' => $email_to_delete]);
            echo '<div class="notice notice-warning is-dismissible"><p>✅ <strong>' . esc_html($email_to_delete) . '</strong> adresine ait tüm yoklama (' . intval($deleted_att) . ') ve bilet kayıtları silindi.</p></div>';
        }
    }

    // --- EKSİK SERTİFİKALARI ÜRET ---
    if (isset($_POST['generate_missing_certs']) && current_user_can('manage_options')) {
        $attendees_for_cert = $wpdb->get_results("SELECT user_name, user_email, user_phone, COUNT(DISTINCT session_id) as session_count FROM $t_att GROUP BY user_email");
        $generated_count = 0;
        foreach ($attendees_for_cert as $att) {
            if (($att->session_count / $total_sessions) >= 0.01) {
                $user_res = $wpdb->get_row($wpdb->prepare("SELECT id, certificate_code FROM $t_res WHERE user_email = %s LIMIT 1", $att->user_email));
                if ($user_res) {
                    if (empty($user_res->certificate_code)) {
                        $wpdb->update($t_res, ['certificate_code' => ybs_generate_cert_code()], ['id' => $user_res->id]);
                        $generated_count++;
                    }
                } else {
                    $wpdb->insert($t_res, [
                        'seat_id'          => 'KAPIDAN-' . rand(1000, 9999),
                        'user_name'        => $att->user_name,
                        'user_email'       => $att->user_email,
                        'user_phone'       => $att->user_phone,
                        'category'         => 'standard',
                        'note'             => 'Yoklamadan Geldi',
                        'status'           => 'approved',
                        'bilet_token'      => md5(uniqid(mt_rand(), true)),
                        'certificate_code' => ybs_generate_cert_code(),
                        'is_checked_in'    => 1,
                    ]);
                    $generated_count++;
                }
            }
        }
        echo '<div class="notice notice-success is-dismissible"><p>İşlem başarılı! <strong>' . $generated_count . '</strong> kişiye sertifika kodu üretildi.</p></div>';
    }

    // --- MANUEL GÖREVLİ EKLEME ---
    if (isset($_POST['manual_bulk_add']) && current_user_can('manage_options')) {
        $rawText    = sanitize_textarea_field($_POST['manual_names']);
        $lines      = explode("\n", $rawText);
        $addedCount = 0;
        $all_sessions = $wpdb->get_col("SELECT id FROM $t_ses");
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            $parts = explode("|", $line);
            $name  = trim($parts[0]);
            $email = isset($parts[1]) ? sanitize_email(trim($parts[1])) : '';
            if (empty($email)) {
                $slug  = sanitize_title($name);
                $email = (empty($slug) ? 'gorevli_' . rand(1000, 9999) : $slug) . '@gorevli.temp';
            }
            $user_res  = $wpdb->get_row($wpdb->prepare("SELECT id, certificate_code FROM $t_res WHERE user_email = %s LIMIT 1", $email));
            $cert_code = ($user_res && !empty($user_res->certificate_code)) ? $user_res->certificate_code : ybs_generate_cert_code();
            if (!$user_res) {
                $wpdb->insert($t_res, [
                    'seat_id'          => 'GOREVLI-' . rand(1000, 9999),
                    'user_name'        => $name,
                    'user_email'       => $email,
                    'user_phone'       => '-',
                    'category'         => 'staff',
                    'note'             => 'Görevli/Protokol',
                    'status'           => 'approved',
                    'bilet_token'      => md5(uniqid(mt_rand(), true)),
                    'certificate_code' => $cert_code,
                    'is_checked_in'    => 1,
                ]);
            } elseif (empty($user_res->certificate_code)) {
                $wpdb->update($t_res, ['certificate_code' => $cert_code], ['id' => $user_res->id]);
            }
            if (!empty($all_sessions)) {
                foreach ($all_sessions as $sid) {
                    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $t_att WHERE session_id = %d AND user_email = %s", $sid, $email));
                    if ($exists == 0) {
                        $wpdb->insert($t_att, ['session_id' => $sid, 'user_name' => $name, 'user_email' => $email, 'user_phone' => '-']);
                    }
                }
            }
            $addedCount++;
        }
        echo '<div class="notice notice-success is-dismissible"><p>✅ <strong>' . $addedCount . '</strong> kişi görevli olarak eklendi ve sertifikaları oluşturuldu.</p></div>';
    }

    // --- İSTATİSTİKLER ---
    $gercek_rezervasyon = (int) $wpdb->get_var("SELECT COUNT(*) FROM $t_res WHERE user_email NOT LIKE '%@ybszirve.local' AND user_email NOT LIKE '%@gorevli.temp'");
    $grup_koltuk        = (int) $wpdb->get_var("SELECT COUNT(*) FROM $t_res WHERE user_email LIKE '%@ybszirve.local'");
    $kapi_giris         = (int) $wpdb->get_var("SELECT COUNT(*) FROM $t_res WHERE is_checked_in = 1 AND user_email NOT LIKE '%@ybszirve.local' AND user_email NOT LIKE '%@gorevli.temp'");
    $oturum_katilimci   = (int) $wpdb->get_var("SELECT COUNT(DISTINCT user_email) FROM $t_att");
    $cert_uretilen      = (int) $wpdb->get_var("SELECT COUNT(*) FROM $t_res WHERE certificate_code != '' AND certificate_code IS NOT NULL AND user_email NOT LIKE '%@ybszirve.local'");
    $cert_mail          = (int) $wpdb->get_var("SELECT COUNT(*) FROM $t_res WHERE cert_emailed = 1");

    $att_counts = $wpdb->get_results("SELECT user_email, COUNT(DISTINCT session_id) as cnt FROM $t_att GROUP BY user_email");
    $cert_hakki = 0;
    foreach ($att_counts as $ac) {
        if (($ac->cnt / $total_sessions) >= 0.01) $cert_hakki++;
    }

    $session_stats = $wpdb->get_results("
        SELECT s.id, s.title, COUNT(a.id) as cnt
        FROM $t_ses s LEFT JOIN $t_att a ON a.session_id = s.id
        GROUP BY s.id ORDER BY s.id ASC
    ");

    // Drill-down: bireysel rezervasyon detayları
    $bireysel_rez_data = $wpdb->get_results("
        SELECT user_name, user_email, user_phone, category, note,
               DATE_FORMAT(reservation_date, '%d.%m.%Y %H:%i') as rez_date
        FROM $t_res
        WHERE user_email NOT LIKE '%@ybszirve.local'
          AND user_email NOT LIKE '%@gorevli.temp'
        ORDER BY reservation_date DESC
    ", ARRAY_A);

    $sessions_json = json_encode(
        array_map(fn($s) => ['id' => (int)$s->id, 'title' => $s->title, 'cnt' => (int)$s->cnt], $session_stats),
        JSON_UNESCAPED_UNICODE
    );
    $rez_json = json_encode($bireysel_rez_data, JSON_UNESCAPED_UNICODE);

    // --- KATILIMCIlar tablosu (oturum listesi dahil) ---
    $attendees = $wpdb->get_results("
        SELECT
            a.user_name,
            a.user_email,
            a.user_phone,
            COUNT(DISTINCT a.session_id) as session_count,
            GROUP_CONCAT(DISTINCT s.title ORDER BY s.id SEPARATOR ', ') as attended_sessions,
            (SELECT is_checked_in   FROM $t_res WHERE user_email = a.user_email LIMIT 1) as is_checked_in,
            (SELECT certificate_code FROM $t_res WHERE user_email = a.user_email LIMIT 1) as cert_code,
            (SELECT cert_emailed    FROM $t_res WHERE user_email = a.user_email LIMIT 1) as cert_emailed
        FROM $t_att a
        LEFT JOIN $t_ses s ON a.session_id = s.id
        GROUP BY a.user_email
        ORDER BY session_count DESC
    ");

    // --- ÇEKİLİŞ LİSTESİ: yoklama (%1) + aktif topluluk üyeleri ---
    $raffleNames     = [];
    $normalizedCheck = [];

    // 1. Yoklama tablosundan %1 barajı geçenler
    foreach ($attendees as $att) {
        if (($att->session_count / $total_sessions) >= 0.01) {
            $upper = mb_strtoupper(trim($att->user_name), 'UTF-8');
            if (!in_array($upper, $normalizedCheck)) {
                $raffleNames[]     = trim($att->user_name);
                $normalizedCheck[] = $upper;
            }
        }
    }

    // 2. Aktif topluluk üyeleri
    $aktif_uyeler = get_users([
        'role'       => 'topluluk_uyesi',
        'number'     => -1,
        'meta_query' => [
            'relation' => 'OR',
            ['key' => 'ybs_status', 'value' => 'aktif', 'compare' => '='],
            ['key' => 'ybs_status', 'compare' => 'NOT EXISTS'],
        ],
    ]);
    foreach ($aktif_uyeler as $uye) {
        $uye_name = trim($uye->display_name);
        $upper    = mb_strtoupper($uye_name, 'UTF-8');
        if (!empty($uye_name) && !in_array($upper, $normalizedCheck)) {
            $raffleNames[]     = $uye_name;
            $normalizedCheck[] = $upper;
        }
    }


    ?>
    <div class="wrap">
    <h1 style="font-size:18px; margin-bottom:6px;">Etkinlik Özeti</h1>
    <hr class="wp-header-end">

    <!-- KOMPAKT İSTATİSTİK ŞERIDI -->
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:12px 16px; margin:14px 0 12px; display:flex; flex-wrap:wrap; gap:0;">
        <?php
        $stats = [
            ['🎫', 'Bireysel Rez.', $gercek_rezervasyon, '#2563eb', 'openRezModal()'],
            ['🚌', 'Grup Koltuk',   $grup_koltuk,         '#7c3aed', ''],
            ['✅', 'Kapı Girişi',   $kapi_giris,          '#059669', ''],
            ['👥', 'Oturum Katıl.', $oturum_katilimci,    '#0891b2', 'openSessionsModal()'],
            ['🏅', 'Sert. Hakkı',  $cert_hakki,          '#d97706', ''],
            ['📜', 'Sert. Üretilen',$cert_uretilen,       '#10b981', ''],
            ['📨', 'Sert. Mail',   $cert_mail,           '#6366f1', ''],
        ];
        foreach ($stats as [$icon, $label, $val, $color, $onclick]):
            $clickable = !empty($onclick);
        ?>
        <div onclick="<?php echo esc_attr($onclick); ?>"
             style="display:flex; align-items:center; gap:6px; padding:6px 14px; border-right:1px solid #f3f4f6;<?php echo $clickable ? 'cursor:pointer;' : ''; ?>"
             <?php if ($clickable): ?>
             onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''"
             title="<?php echo $clickable ? 'Detay için tıkla' : ''; ?>"
             <?php endif; ?>>
            <span style="font-size:15px;"><?php echo $icon; ?></span>
            <div>
                <div style="font-size:18px; font-weight:700; color:<?php echo $color; ?>; line-height:1.1;"><?php echo $val; ?><?php if ($clickable): ?><span style="font-size:10px; margin-left:3px; opacity:0.6;">↗</span><?php endif; ?></div>
                <div style="font-size:10px; color:#9ca3af; white-space:nowrap;"><?php echo $label; ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- EYLEM BUTONLARI -->
    <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:14px; align-items:center;">
        <a href="?page=ybs-etkinlik-ozeti&export_excel=1" class="button" style="background:#10b981; color:#fff; border-color:#059669;">📥 Excel</a>
        <a href="?page=ybs-etkinlik-ozeti&export_cert_winners=1" class="button" style="background:#2563eb; color:#fff; border-color:#1d4ed8;">🏅 Sertifika Hakkı (Ad+E-Posta)</a>
        <form method="post" style="display:inline;">
            <button type="submit" name="generate_missing_certs" class="button" style="background:#3b82f6; color:#fff; border-color:#2563eb;"
                onclick="return confirm('Hak kazanan ancak kodu olmayan herkese sertifika üretilecek. Onaylıyor musunuz?');">🏅 Eksik Sertifika Üret</button>
        </form>
        <button type="button" class="button" id="btn-bulk-cert-mail" style="background:#7c3aed; color:#fff; border-color:#6d28d9;" onclick="sendAllCertMails()">📨 Toplu Mail</button>
        <button type="button" class="button" style="background:#0dcaf0; color:#000; border-color:#0bacce;" onclick="document.getElementById('staff-modal').style.display='flex'">👤 Görevli Ekle</button>
        <button type="button" class="button button-primary" onclick="document.getElementById('fazla-gelen-modal').style.display='flex'">➕ Fazla Gelen</button>
        <button type="button" class="button" style="background:#f59e0b; color:#fff; border-color:#d97706;" onclick="document.getElementById('ozet-cekilish-modal').style.display='flex'">🎟️ Çekiliş Havuzu</button>
        <button type="button" class="button" style="background:#0891b2; color:#fff; border-color:#0e7490;" onclick="openSessionsModal()">📅 Oturum Katılımları</button>
        <a href="<?php echo admin_url('admin.php?page=ybs-hall-manager'); ?>" class="button">🪑 Salon</a>
        <a href="<?php echo admin_url('admin.php?page=ybs-attendance'); ?>" class="button">📡 Oturumlar</a>
    </div>

    <!-- KATILIMCIöZET TABLOSU -->
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:14px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <span style="font-size:13px; font-weight:600; color:#111827;">👥 Oturum Katılımcıları &nbsp;<span style="font-weight:400; color:#9ca3af;">(<?php echo count($attendees); ?> kişi · <?php echo $total_sessions; ?> oturum · %1 barajı)</span></span>
        </div>
        <div style="overflow-x:auto;">
        <table class="wp-list-table widefat fixed striped" style="font-size:12px; min-width:900px;">
            <thead>
                <tr>
                    <th style="width:13%;">Ad Soyad</th>
                    <th style="width:15%;">E-Posta</th>
                    <th style="width:9%;">Telefon</th>
                    <th style="width:6%; text-align:center;">Kapı</th>
                    <th style="width:10%;">Katılım</th>
                    <th style="width:9%;">Durum</th>
                    <th style="width:13%;">Sertifika Kodu</th>
                    <th style="width:17%;">Katıldığı Oturumlar</th>
                    <th style="width:8%;">İşlem</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($attendees)): ?>
                <tr><td colspan="9" style="text-align:center; color:#9ca3af; padding:24px;">Henüz yoklama kaydı yok.</td></tr>
            <?php else: ?>
                <?php foreach ($attendees as $att):
                    $ratio     = $att->session_count / $total_sessions;
                    $yuzde     = round($ratio * 100);
                    $is_passed = ($ratio >= 0.01);
                ?>
                <tr>
                    <td><strong><?php echo esc_html($att->user_name); ?></strong></td>
                    <td style="word-break:break-all;"><?php echo esc_html($att->user_email); ?></td>
                    <td><?php echo esc_html($att->user_phone); ?></td>
                    <td style="text-align:center;">
                        <?php echo !empty($att->is_checked_in) ? '<span style="color:#10b981;" title="Kapıdan geçti">✅</span>' : '<span style="color:#d1d5db;">—</span>'; ?>
                    </td>
                    <td>
                        <span style="background:<?php echo $is_passed ? '#dcfce7' : '#fee2e2'; ?>; color:<?php echo $is_passed ? '#166534' : '#991b1b'; ?>; padding:2px 6px; border-radius:10px; font-size:11px; font-weight:600; white-space:nowrap;">
                            %<?php echo $yuzde; ?> (<?php echo $att->session_count . '/' . $total_sessions; ?>)
                        </span>
                    </td>
                    <td><?php echo $is_passed ? '<span style="color:#059669; font-weight:600;">✅ Hak Kazandı</span>' : '<span style="color:#dc2626; font-weight:600;">❌ Yetersiz</span>'; ?></td>
                    <td style="font-family:monospace; font-size:11px;">
                        <?php if (!empty($att->cert_code)): ?>
                            <span style="color:#1d4ed8; font-weight:600;"><?php echo esc_html($att->cert_code); ?></span>
                            <?php if (!empty($att->cert_emailed)): ?><span style="color:#10b981; font-size:10px; display:block;">✓ Mail gönderildi</span><?php endif; ?>
                        <?php else: ?>
                            <span style="color:#9ca3af;">Oluşturulmadı</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:11px; color:#555; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?php echo esc_attr($att->attended_sessions); ?>"><?php echo esc_html($att->attended_sessions); ?></td>
                    <td>
                        <div style="display:flex; flex-direction:column; gap:4px;">
                            <?php if ($is_passed && !empty($att->cert_code)): ?>
                                <button class="button action-send-mail" style="font-size:11px; padding:2px 6px; <?php echo !empty($att->cert_emailed) ? 'border-color:#10b981; color:#10b981;' : ''; ?>"
                                    data-email="<?php echo esc_attr($att->user_email); ?>"
                                    data-name="<?php echo esc_attr($att->user_name); ?>"
                                    data-code="<?php echo esc_attr($att->cert_code); ?>">
                                    <?php echo !empty($att->cert_emailed) ? 'Tekrar Gönder' : 'Mail At'; ?>
                                </button>
                            <?php endif; ?>
                            <a href="?page=ybs-etkinlik-ozeti&delete_attendee=<?php echo urlencode($att->user_email); ?>"
                               class="button" style="font-size:11px; padding:2px 6px; border-color:#ef4444; color:#ef4444; text-align:center;"
                               onclick="return confirm('Bu kişiye ait tüm yoklama ve bilet kayıtları silinecek. Emin misiniz?');">Sil</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- GÖREVLİ EKLEME MODAL -->
    <div id="staff-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:99999; justify-content:center; align-items:center;">
        <div style="background:#fff; padding:25px; border-radius:10px; width:500px; max-width:92%; box-shadow:0 20px 50px rgba(0,0,0,0.2);">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding-bottom:14px; margin-bottom:14px;">
                <h3 style="margin:0;">👤 Manuel Görevli Ekle</h3>
                <button onclick="document.getElementById('staff-modal').style.display='none'" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
            </div>
            <form method="POST">
                <p style="font-size:13px; color:#666; margin-top:0;">
                    Eklenen isimler <strong>TÜM OTURUMLARA</strong> katılmış sayılır ve sertifika hakkı kazanır.<br><br>
                    <strong>Format:</strong> <code>Ad Soyad | email@adresi.com</code> veya sadece <code>Ad Soyad</code>
                </p>
                <textarea name="manual_names" required placeholder="Ali Yılmaz | ali@gmail.com&#10;Ayşe Demir&#10;Mehmet Kaya | mehmet@hotmail.com"
                    style="width:100%; height:140px; padding:10px; font-family:monospace; font-size:13px; border:1px solid #ccc; border-radius:4px; resize:none; box-sizing:border-box;"></textarea>
                <input type="hidden" name="manual_bulk_add" value="1">
                <div style="margin-top:14px; text-align:right;">
                    <button type="button" class="button" onclick="document.getElementById('staff-modal').style.display='none'">İptal</button>
                    <button type="submit" class="button button-primary">💾 Kaydet ve Hak Ver</button>
                </div>
            </form>
        </div>
    </div>

    <!-- FAZLA GELEN KAYIT MODAL -->
    <div id="fazla-gelen-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.55); z-index:99999; justify-content:center; align-items:center;">
        <div style="background:#fff; padding:28px; border-radius:12px; width:420px; max-width:95%; box-shadow:0 20px 60px rgba(0,0,0,0.25);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
                <h3 style="margin:0; font-size:16px;">➕ Fazla Gelen Kayıt</h3>
                <button onclick="document.getElementById('fazla-gelen-modal').style.display='none'" style="background:none; border:none; font-size:22px; cursor:pointer; color:#9ca3af; line-height:1;">&times;</button>
            </div>
            <p style="font-size:12px; color:#6b7280; margin:0 0 16px 0;">Kontenjan dışı gelen kişileri kayıt altına alın. Otomatik <code>GELEN-XXXX</code> koltuk numarası atanır, kapı girişi işaretlenir.</p>
            <div id="fg-error" style="display:none; background:#fee2e2; color:#991b1b; padding:10px 12px; border-radius:6px; font-size:13px; margin-bottom:12px;"></div>
            <div id="fg-success" style="display:none; background:#dcfce7; color:#166534; padding:10px 12px; border-radius:6px; font-size:13px; margin-bottom:12px;"></div>
            <div style="display:flex; flex-direction:column; gap:11px;">
                <div><label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">Ad Soyad *</label>
                    <input type="text" id="fg-name" placeholder="Ali Yılmaz" style="width:100%; padding:8px 10px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; box-sizing:border-box;"></div>
                <div><label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">E-Posta *</label>
                    <input type="email" id="fg-email" placeholder="ali@example.com" style="width:100%; padding:8px 10px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; box-sizing:border-box;"></div>
                <div><label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">Telefon * (05XXXXXXXXX)</label>
                    <input type="text" id="fg-phone" placeholder="05xxxxxxxxx" maxlength="11" style="width:100%; padding:8px 10px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; box-sizing:border-box;"></div>
                <div><label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">Not (isteğe bağlı)</label>
                    <input type="text" id="fg-note" placeholder="Üniversite adı, grup vb." style="width:100%; padding:8px 10px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; box-sizing:border-box;"></div>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px; justify-content:flex-end;">
                <button class="button" onclick="document.getElementById('fazla-gelen-modal').style.display='none'">İptal</button>
                <button class="button button-primary" id="fg-submit-btn" onclick="submitFazlaGelen()">💾 Kaydet</button>
            </div>
        </div>
    </div>

    <!-- ÇEKİLİŞ HAVUZU MODAL -->
    <div id="ozet-cekilish-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:99999; justify-content:center; align-items:center;">
        <div style="background:#fff; padding:24px; border-radius:12px; width:500px; max-width:95%; box-shadow:0 20px 60px rgba(0,0,0,0.25);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <h3 style="margin:0; font-size:16px;">🎟️ Çekiliş Havuzu</h3>
                <button onclick="document.getElementById('ozet-cekilish-modal').style.display='none'" style="background:none; border:none; font-size:22px; cursor:pointer; color:#9ca3af; line-height:1;">&times;</button>
            </div>
            <p style="font-size:12px; color:#6b7280; margin:0 0 12px 0;">
                Toplam <strong><?php echo count($raffleNames); ?></strong> kişi
                <span style="color:#9ca3af;">· dış katılımcı ≥%1 oturum + aktif topluluk üyeleri</span>
            </p>
            <textarea id="cekilish-havuz-text" readonly style="width:100%; height:280px; padding:10px; font-family:monospace; font-size:13px; border:1px solid #e5e7eb; border-radius:6px; background:#f9fafb; resize:none; box-sizing:border-box;"><?php echo esc_textarea(implode("\n", $raffleNames)); ?></textarea>
            <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:14px;">
                <button class="button" onclick="document.getElementById('ozet-cekilish-modal').style.display='none'">Kapat</button>
                <button class="button button-primary" onclick="copyHavuz()">📋 Kopyala</button>
            </div>
        </div>
    </div>

    <!-- MODAL: Bireysel Rezervasyon Detayı -->
    <div id="rez-detail-modal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,.5); align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:10px; padding:24px; width:90%; max-width:820px; max-height:85vh; display:flex; flex-direction:column; box-shadow:0 10px 40px rgba(0,0,0,.25);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; flex-shrink:0;">
                <div>
                    <h3 style="margin:0; font-size:16px;">🎫 Bireysel Rezervasyonlar</h3>
                    <span id="rez-modal-count" style="font-size:12px; color:#9ca3af;"></span>
                </div>
                <button onclick="document.getElementById('rez-detail-modal').style.display='none'" style="background:none; border:none; font-size:20px; cursor:pointer; color:#6b7280; line-height:1;">×</button>
            </div>
            <div style="margin-bottom:10px; flex-shrink:0;">
                <input type="text" id="rez-modal-search" placeholder="İsim, e-posta veya telefon ara..." oninput="filterRezTable()"
                    style="width:100%; padding:7px 10px; border:1px solid #e5e7eb; border-radius:6px; font-size:13px; box-sizing:border-box;">
            </div>
            <div style="overflow-y:auto; flex:1;">
                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                    <thead style="position:sticky; top:0;">
                        <tr style="background:#f9fafb;">
                            <th style="padding:8px 10px; text-align:left; border-bottom:2px solid #e5e7eb; font-weight:600; white-space:nowrap;">#</th>
                            <th style="padding:8px 10px; text-align:left; border-bottom:2px solid #e5e7eb; font-weight:600; white-space:nowrap;">Ad Soyad</th>
                            <th style="padding:8px 10px; text-align:left; border-bottom:2px solid #e5e7eb; font-weight:600; white-space:nowrap;">E-Posta</th>
                            <th style="padding:8px 10px; text-align:left; border-bottom:2px solid #e5e7eb; font-weight:600; white-space:nowrap;">Telefon</th>
                            <th style="padding:8px 10px; text-align:left; border-bottom:2px solid #e5e7eb; font-weight:600; white-space:nowrap;">Kategori</th>
                            <th style="padding:8px 10px; text-align:left; border-bottom:2px solid #e5e7eb; font-weight:600; white-space:nowrap;">Tarih</th>
                        </tr>
                    </thead>
                    <tbody id="rez-modal-tbody"></tbody>
                </table>
            </div>
            <div style="margin-top:14px; text-align:right; flex-shrink:0;">
                <button class="button" onclick="document.getElementById('rez-detail-modal').style.display='none'">Kapat</button>
            </div>
        </div>
    </div>

    <!-- MODAL: Oturum Katılımları -->
    <div id="sessions-detail-modal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,.5); align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:10px; padding:24px; width:90%; max-width:740px; max-height:85vh; display:flex; flex-direction:column; box-shadow:0 10px 40px rgba(0,0,0,.25);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; flex-shrink:0;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <button id="sessions-modal-back" onclick="showSessionsList()" style="display:none; background:none; border:1px solid #e5e7eb; border-radius:6px; padding:4px 10px; cursor:pointer; font-size:13px; color:#374151;">← Geri</button>
                    <h3 id="sessions-modal-title" style="margin:0; font-size:16px;">📅 Oturum Katılımları</h3>
                </div>
                <button onclick="document.getElementById('sessions-detail-modal').style.display='none'" style="background:none; border:none; font-size:20px; cursor:pointer; color:#6b7280; line-height:1;">×</button>
            </div>
            <div id="sessions-modal-content" style="overflow-y:auto; flex:1;"></div>
            <div style="margin-top:14px; text-align:right; flex-shrink:0;">
                <button class="button" onclick="document.getElementById('sessions-detail-modal').style.display='none'">Kapat</button>
            </div>
        </div>
    </div>

    </div><!-- .wrap -->

    <script>
    const ozetRezData  = <?php echo $rez_json; ?>;
    const ozetSessions = <?php echo $sessions_json; ?>;

    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // --- Rezervasyon detay modal ---
    function openRezModal() {
        const tbody = document.getElementById('rez-modal-tbody');
        tbody.innerHTML = '';
        ozetRezData.forEach(function(r, i) {
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #f3f4f6';
            tr.innerHTML = '<td style="padding:7px 10px; color:#9ca3af; font-size:11px;">' + (i+1) + '</td>'
                + '<td style="padding:7px 10px; font-weight:500;">' + escHtml(r.user_name) + '</td>'
                + '<td style="padding:7px 10px; color:#6b7280; font-size:12px;">' + escHtml(r.user_email) + '</td>'
                + '<td style="padding:7px 10px; color:#6b7280; font-size:12px;">' + escHtml(r.user_phone) + '</td>'
                + '<td style="padding:7px 10px; font-size:12px;">' + escHtml(r.category||'') + '</td>'
                + '<td style="padding:7px 10px; font-size:11px; color:#9ca3af; white-space:nowrap;">' + escHtml(r.rez_date) + '</td>';
            tbody.appendChild(tr);
        });
        document.getElementById('rez-modal-count').textContent = ozetRezData.length + ' rezervasyon';
        document.getElementById('rez-modal-search').value = '';
        document.getElementById('rez-detail-modal').style.display = 'flex';
    }

    function filterRezTable() {
        const q = document.getElementById('rez-modal-search').value.toLowerCase();
        document.querySelectorAll('#rez-modal-tbody tr').forEach(function(tr) {
            tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    }

    // --- Oturum katılımları modal ---
    function openSessionsModal() {
        showSessionsList();
        document.getElementById('sessions-detail-modal').style.display = 'flex';
    }

    function showSessionsList() {
        const content = document.getElementById('sessions-modal-content');
        let html = '<table style="width:100%; border-collapse:collapse; font-size:13px;">';
        html += '<thead><tr style="background:#f9fafb;"><th style="padding:9px 12px; text-align:left; border-bottom:2px solid #e5e7eb;">Oturum</th><th style="padding:9px 12px; text-align:right; border-bottom:2px solid #e5e7eb;">Katılımcı</th><th style="padding:9px 12px; border-bottom:2px solid #e5e7eb;"></th></tr></thead><tbody>';
        if (ozetSessions.length === 0) {
            html += '<tr><td colspan="3" style="padding:20px; text-align:center; color:#9ca3af;">Henüz oturum yok.</td></tr>';
        } else {
            ozetSessions.forEach(function(s) {
                html += '<tr style="border-bottom:1px solid #f3f4f6; cursor:pointer;" onclick="openSessionDetail(' + s.id + ', this)" onmouseover="this.style.background=\'#f9fafb\'" onmouseout="this.style.background=\'\'"><td style="padding:9px 12px; font-weight:500;">' + escHtml(s.title) + '</td><td style="padding:9px 12px; text-align:right; font-weight:700; color:#2563eb; font-size:16px;">' + s.cnt + '</td><td style="padding:9px 12px; color:#9ca3af; font-size:12px; white-space:nowrap;">Detay →</td></tr>';
            });
        }
        html += '</tbody></table>';
        content.innerHTML = html;
        document.getElementById('sessions-modal-back').style.display = 'none';
        document.getElementById('sessions-modal-title').textContent = '📅 Oturum Katılımları';
    }

    function openSessionDetail(sessionId, rowEl) {
        const sessionTitle = rowEl.querySelector('td').textContent;
        const content = document.getElementById('sessions-modal-content');
        content.innerHTML = '<div style="text-align:center; padding:30px; color:#9ca3af;">⏳ Yükleniyor...</div>';
        document.getElementById('sessions-modal-back').style.display = 'inline-block';
        document.getElementById('sessions-modal-title').textContent = sessionTitle;
        const fd = new FormData();
        fd.append('action', 'ybs_ozet_session_detail');
        fd.append('session_id', sessionId);
        fetch(ajaxurl, { method: 'POST', body: fd }).then(function(r) { return r.json(); }).then(function(res) {
            if (!res.success) { content.innerHTML = '<p style="color:red; padding:16px;">' + escHtml(res.data) + '</p>'; return; }
            const people = res.data;
            if (people.length === 0) { content.innerHTML = '<p style="color:#9ca3af; padding:20px; text-align:center;">Bu oturuma kayıtlı katılımcı yok.</p>'; return; }
            const names = people.map(function(p) { return p.user_name; }).join('\n');
            const taId  = 'session-detail-ta';
            let html = '<div style="font-size:12px; color:#6b7280; margin-bottom:8px;">' + people.length + ' katılımcı</div>';
            html += '<textarea id="' + taId + '" readonly style="width:100%; height:320px; padding:10px; font-family:monospace; font-size:13px; border:1px solid #e5e7eb; border-radius:6px; background:#f9fafb; resize:none; box-sizing:border-box;">' + names.replace(/</g,'&lt;') + '</textarea>';
            html += '<div style="display:flex; gap:8px; justify-content:flex-end; margin-top:10px;"><button class="button button-primary" onclick="var e=document.getElementById(\'' + taId + '\');e.select();navigator.clipboard.writeText(e.value).then(function(){alert(\'✅ Kopyalandı! (\'+e.value.split(\'\\n\').filter(Boolean).length+\' kişi)\');})">📋 Kopyala</button></div>';
            content.innerHTML = html;
        }).catch(function() { content.innerHTML = '<p style="color:red; padding:16px;">Bağlantı hatası.</p>'; });
    }

    function copyHavuz() {
        var el = document.getElementById('cekilish-havuz-text');
        el.select();
        navigator.clipboard.writeText(el.value).then(function() {
            alert('✅ Çekiliş havuzu kopyalandı! (' + el.value.split('\n').filter(Boolean).length + ' kişi)');
        });
    }

    // --- Fazla Gelen ---
    function submitFazlaGelen() {
        const name = document.getElementById('fg-name').value.trim();
        const email = document.getElementById('fg-email').value.trim();
        const phone = document.getElementById('fg-phone').value.trim();
        const note  = document.getElementById('fg-note').value.trim();
        const errEl = document.getElementById('fg-error');
        const sucEl = document.getElementById('fg-success');
        const btn   = document.getElementById('fg-submit-btn');
        errEl.style.display = 'none'; sucEl.style.display = 'none';
        if (!name || !email || !phone) { errEl.textContent = 'Ad Soyad, E-Posta ve Telefon zorunludur.'; errEl.style.display = 'block'; return; }
        btn.disabled = true; btn.textContent = '⏳ Kaydediliyor...';
        const fd = new FormData();
        fd.append('action', 'ybs_fazla_gelen_kayit');
        fd.append('user_name', name); fd.append('user_email', email); fd.append('user_phone', phone); fd.append('note', note);
        fetch(ajaxurl, { method: 'POST', body: fd }).then(r => r.json()).then(res => {
            if (res.success) {
                sucEl.textContent = '✅ Kayıt oluşturuldu! Koltuk: ' + res.data.seat_id; sucEl.style.display = 'block';
                document.getElementById('fg-name').value = document.getElementById('fg-email').value = document.getElementById('fg-phone').value = document.getElementById('fg-note').value = '';
            } else { errEl.textContent = res.data; errEl.style.display = 'block'; }
            btn.disabled = false; btn.textContent = '💾 Kaydet';
        }).catch(() => { errEl.textContent = 'Sunucuyla bağlantı kurulamadı.'; errEl.style.display = 'block'; btn.disabled = false; btn.textContent = '💾 Kaydet'; });
    }

    // --- Toplu Sertifika Mail ---
    async function sendAllCertMails() {
        const btn = document.getElementById('btn-bulk-cert-mail');
        if (!confirm('Sertifika kodu olan ve daha önce mail gönderilmemiş herkese sertifika maili gönderilecek. Emin misiniz?')) return;
        btn.disabled = true; btn.innerText = '⏳ Gönderiliyor...';
        let offset = 0, totalSent = 0, totalFail = 0;
        async function sendNext() {
            const fd = new FormData();
            fd.append('action', 'ybs_send_all_cert_emails'); fd.append('offset', offset); fd.append('limit', 5);
            const res = await fetch(ajaxurl, { method: 'POST', body: fd });
            const data = await res.json();
            if (!data.success) { alert('Hata: ' + data.data); btn.disabled = false; btn.innerText = '📨 Toplu Sertifika Mail'; return; }
            totalSent += data.data.sent; totalFail += data.data.failed; offset += data.data.processed;
            btn.innerText = '⏳ ' + offset + ' gönderildi...';
            if (data.data.has_more) { await sendNext(); }
            else { btn.disabled = false; btn.innerText = '📨 Toplu Sertifika Mail'; alert('✅ Tamamlandı!\n' + totalSent + ' mail gönderildi, ' + totalFail + ' başarısız.'); location.reload(); }
        }
        await sendNext();
    }

    // --- Tekil Mail ---
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.action-send-mail').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const email = this.getAttribute('data-email');
                const name  = this.getAttribute('data-name');
                const code  = this.getAttribute('data-code');
                const orig  = this.innerText;
                this.innerText = 'Gönderiliyor...'; this.disabled = true;
                const fd = new FormData();
                fd.append('action', 'ybs_send_cert_email'); fd.append('email', email); fd.append('name', name); fd.append('cert_code', code);
                fetch(ajaxurl, { method: 'POST', body: fd }).then(r => r.json()).then(res => {
                    if (res.success) {
                        this.innerText = 'Tekrar Gönder'; this.style.borderColor = '#10b981'; this.style.color = '#10b981'; this.disabled = false;
                        if (!this.nextElementSibling || !this.nextElementSibling.innerText.includes('İletildi')) {
                            this.insertAdjacentHTML('afterend', '<div style="font-size:10px; color:#10b981; font-weight:bold;">✓ İletildi</div>');
                        }
                    } else { alert(res.data); this.innerText = orig; this.disabled = false; }
                }).catch(() => { alert('Bağlantı hatası.'); this.innerText = orig; this.disabled = false; });
            });
        });
    });
    </script>
    <?php
}
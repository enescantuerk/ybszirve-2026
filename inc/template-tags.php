<?php
/**
 * Katılımcı Kulüpler Logoları Shortcode (Tema Uyumlu)
 * Kullanım: [ybs_kulupler]
 */
function ybs_participating_clubs_shortcode() {
    
    // --------------------------------------------------------
    // KULÜP LOGOLARI LİSTESİ
    // --------------------------------------------------------
    $clubs = [
        ['name' => 'DU YBS', 'logo' => 'https://2026.ybszirve.org.tr/wp-content/themes/duybs/assets/img/duybs-black.png'],
        ['name' => 'DU YBS', 'logo' => 'https://2026.ybszirve.org.tr/wp-content/themes/duybs/assets/img/duybs-black.png'],
        ['name' => 'DU YBS', 'logo' => 'https://2026.ybszirve.org.tr/wp-content/themes/duybs/assets/img/duybs-black.png'],
        ['name' => 'DU YBS', 'logo' => 'https://2026.ybszirve.org.tr/wp-content/themes/duybs/assets/img/duybs-black.png'],
        ['name' => 'DU YBS', 'logo' => 'https://2026.ybszirve.org.tr/wp-content/themes/duybs/assets/img/duybs-black.png'],


    ];

    ob_start();
    ?>

    <section class="clubs-section">
        <div class="clubs-container">
            
            <div class="clubs-header">
                <h2 class="clubs-title">Katılımcı Kulüpler</h2>
                <div class="clubs-divider"></div>
            </div>
            
            <div class="clubs-grid">
                <?php foreach ($clubs as $club): ?>
                    <div class="club-item" title="<?php echo $club['name']; ?>">
                        <img src="<?php echo $club['logo']; ?>" alt="<?php echo $club['name']; ?>" class="club-logo">
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </section>

    <style>
        /* --- TEMA DEĞİŞKENLERİYLE UYUMLU STİL --- */
        
        .clubs-section {
            background-color: #ffffff; /* Temiz beyaz zemin */
            padding: 80px 0;
            border-top: 1px solid rgba(0,0,0,0.06); /* Hafif ayırıcı */
        }

        .clubs-container {
            max-width: 1100px; /* Sitenin genel genişliği (--nav-width) */
            margin: 0 auto;
            padding: 0 25px;
        }

        /* --- BAŞLIK ALANI --- */
        .clubs-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .clubs-title {
            font-family: 'Inter', system-ui, -apple-system, sans-serif; /* Site fontu */
            font-size: 2rem;
            font-weight: 800;
            color: var(--header-bg); /* Lacivert (#002855) */
            margin: 0 0 15px 0;
            letter-spacing: -0.03em;
            line-height: 1.1;
        }

        /* Kırmızı yerine Teal (Turkuaz) Çizgi */
        .clubs-divider {
            width: 60px;
            height: 4px;
            background-color: var(--accent-teal); /* Turkuaz (#00B5AD) */
            margin: 0 auto;
            border-radius: 2px;
        }

        /* --- LOGO IZGARASI (GRID) --- */
        .clubs-grid {
            display: grid;
            /* Ekran boyutuna göre otomatik sığan kutular */
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 40px;
            align-items: center;
            justify-items: center;
        }

        .club-item {
            width: 100%;
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
            padding: 10px;
            /* Hafif border (Opsiyonel - silinebilir) */
            border: 1px solid transparent; 
            border-radius: 8px;
        }

        .club-item:hover {
            transform: translateY(-5px); /* Hafif yukarı kalkma */
            background-color: #F8FAFC; /* Hover'da çok hafif gri arka plan */
            border-color: #E2E8F0;
        }

        .club-logo {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            /* Varsayılan: Gri ve Hafif Soluk */
            filter: grayscale(100%);
            opacity: 0.6;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94); /* Temadaki animasyon */
            /* Logoların keskin görünmesi için */
            -webkit-backface-visibility: hidden; 
        }

        /* Hover Durumu */
        .club-item:hover .club-logo {
            filter: grayscale(0%); /* Renklenir */
            opacity: 1; /* Parlar */
            transform: scale(1.05); /* Çok hafif büyür */
        }

        /* --- MOBİL UYUMLULUK --- */
        @media (max-width: 768px) {
            .clubs-section {
                padding: 50px 0;
            }
            
            .clubs-title {
                font-size: 1.75rem;
            }

            .clubs-grid {
                gap: 20px;
                /* Mobilde yan yana 2 tane sığsın */
                grid-template-columns: repeat(2, 1fr); 
            }

            .club-item {
                height: 70px;
            }
        }
    </style>

    <?php
    return ob_get_clean();
}
add_shortcode('ybs_kulupler', 'ybs_participating_clubs_shortcode');
<?php
/**
 * The main template file
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @package YBS_Zirvesi_2026
 */

get_header();
?>

<main id="primary" class="site-main">
    
<section class="hero-section">
        <div class="hero-glow glow-top-left"></div>
        <div class="hero-glow glow-bottom-right"></div>

        <div class="hero-container">
            
            <h1 class="hero-title">
                <span class="static-text">10. Yıla Özel 10 Numara Zirve</span>
                <span class="dynamic-wrapper">
                    <span class="dynamic-text" id="dynamicText">Geleceği Yönet!</span>
                </span>
            </h1>

            <p class="hero-description">
                Yönetim Bilişim Sistemleri Enstitüsü'nün himayesinde, <strong>28-29 Mart 2026</strong> tarihinde <strong>Düzce Üniversitesi</strong>'nde düzenlenecek olan 10. Ulusal Yönetim Bilişim Sistemleri Zirvesi'ni duyurmaktan mutluluk duyuyoruz.
            </p>

            <div class="hero-meta-info">
    <div class="hero-meta-item">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        28-29 Mart 2026
    </div>
    <div class="hero-meta-item">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
            <circle cx="12" cy="10" r="3"></circle>
        </svg>
        Düzce Üniversitesi
    </div>
</div>

            <div class="hero-buttons">
                <a href="/rezervasyon" class="btn-cta">Etkinliğe Kayıt Ol</a>
                <a href="/konusmacilar" class="btn-link">Konuşmacıları Gör →</a>
            </div>

        </div>
    </section>
    <section>
        <div class="partners-section">
            <div class="container">
                <p class="partners-title">PAYDAŞLARIMIZ</p>
                
                <div class="partners-wrapper">
                    <div class="partner-item">
                        <img src="https://2026.ybszirve.org.tr/dosyalar/du-logo.png" alt="Düzce Üniversitesi">
                    </div>

                    <div class="partner-item">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/ybsenstitu.png" alt="YBS Enstitü">
                    </div>
					
					<div class="partner-item">
                        <img src="https://2026.ybszirve.org.tr/dosyalar/dernek.png" alt="Kamu Bilişimcileri Derneği">
                    </div>

                    <div class="partner-item">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/zirvelogo.png" alt="Ulusal YBS Zirvesi">
                    </div>
                    <div class="partner-item">
                        <img src="https://2026.ybszirve.org.tr/dosyalar/unides.png" alt="ÜNİDES">
                    </div>
                    <div class="partner-item">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/duybs-black.png" alt="DÜYBS">
                    </div>
                </div>
            </div>
        </div>
    </section>
	
	    <?php echo do_shortcode('[ybs_program]'); ?>

    
    <section class="bento-section">
        <div class="container">
            
            <div class="section-header">
                <h2 class="section-title">YBS Zirvesi Nedir?</h2>
                <p class="section-subtitle">YBS Zirvesi; öğrenciler ile sektör profesyonellerini buluşturarak teknoloji, yönetim ve kariyer dünyasına dair gerçek deneyimleri paylaşmayı amaçlayan bir buluşmadır.</p>
            </div>

            <div class="bento-grid">
                
                <div class="bento-card card-main">
                    <div class="main-content">
                        <div class="card-badge">Zirve Hakkında</div>
                        <h3>Teknoloji ve Yönetimin<br>Kesişim Noktası</h3>
                        <p>YBS öğrencileri ile sektör profesyonellerini bir araya getiren bu organizasyon, sadece bir etkinlik değil; katılımcıların kariyer yolculuklarını şekillendiren bir vizyon platformudur.</p>
                    </div>
                </div>

                <div class="bento-card card-stat-vertical">
                    <div class="stat-header">
                        <div class="live-badge">
                            <span class="pulse-dot red"></span> 10. YIL
                        </div>
                        <h4 style="font-size: 1.2rem; line-height: 1.4;">On Yıllık Kesintisiz Teknoloji ve Yönetim Yolculuğu</h4>
                    </div>
                    
                    <div class="stat-body">
                        <div class="stat-block">
                            <span class="s-num">2014</span>
                            <span class="s-label">Başlangıç</span>
                        </div>
                        <div class="stat-divider-hor"></div>
                        <div class="stat-block">
                            <span class="s-num">2026</span>
                            <span class="s-label">Düzce</span>
                        </div>
                    </div>

                    <div class="mini-stage">
                        <div class="m-podium"><span class="dashicons dashicons-awards"></span></div>
                        <div class="m-ring"></div>
                    </div>
                </div>

                <div class="bento-card card-network-wide">
                    <div class="net-content">
                        <div class="net-header-row">
                            <div class="net-badge">
                                <span class="pulse-dot orange"></span> Ağını Genişlet
                            </div>
                            <div class="net-meta">
                                <span class="meta-tag">YBS Öğrencileri</span>
                                <span class="meta-tag">Sektör Profesyonelleri</span>
                            </div>
                        </div>
                        <h3>Devasa Networking Ağı</h3>
                        <p>Türkiye'nin dört bir yanından gelen YBS öğrencileri, akademisyenler ve iş dünyasının devleriyle tanışarak geleceğinizi şekillendirecek güçlü bağlantıları bugünden kurun.</p>
                    </div>
                    <div class="net-visual">
                        <div class="network-mockup">
                            <div class="profile-node center"><span class="dashicons dashicons-networking"></span><div class="orbit-ring"></div></div>
                            <div class="connect-line l1"></div><div class="connect-line l2"></div><div class="connect-line l3"></div>
                            <div class="profile-node p1"></div><div class="profile-node p2"></div>
                            <div class="profile-node p3"><div class="chat-bubble">İlham Al! 💡</div></div>
                        </div>
                    </div>
                </div>

                <div class="bento-card card-certificate">
                    <div class="cert-content">
                        <div class="net-header-row">
                            <div class="net-badge">
                                <span class="pulse-dot"></span> Resmi Belge
                            </div>
                            <div class="net-meta">
                                <span class="meta-tag">CV'nize Güç Katar</span>
                            </div>
                        </div>
                        <h3>Katılım Sertifikası</h3>
                        <p>Zirve boyunca edindiğiniz bilgileri ve katılımınızı belgeleyen, CV'nize güç katacak QR kodlu resmi dijital sertifika.</p>
                    </div>
                    <div class="cert-visual">
                        <div class="paper-mockup">
                            <div class="paper-header"></div>
                            <div class="paper-body">
                                <div class="paper-row"><div class="paper-lines long"></div></div>
                                <div class="paper-row"><div class="paper-lines medium"></div><div class="paper-qr"></div></div>
                            </div>
                            <div class="paper-seal">★</div><div class="check-floating">✓</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    
    
    <section id="ulasim" class="location-section">
        <div class="container">
            <div class="location-card">
                <div class="loc-content">
                    <div class="loc-header">
                        <span class="loc-badge">Etkinlik Alanı</span>
                        <h2 class="loc-title">Atatürk Kültür Merkezi <br>Cumhuriyet Konferans Salonu</h2>
                        <p class="loc-desc">Düzce Üniversitesi Konuralp Yerleşkesi'nin kalbinde, modern altyapısıyla 10. yıla yakışır bir atmosfer.</p>
                    </div>

                    <div class="loc-details-grid">
                        
                        <div class="loc-item">
                            <span class="loc-icon">📍</span>
                            <div class="loc-text-wrapper">
                                <strong>Adres</strong>
                                <span>Düzce Üniversitesi, Konuralp Yerleşkesi, Merkez/Düzce</span>
                            </div>
                        </div>

                        <div class="loc-item">
                            <span class="loc-icon">🚌</span>
                            <div class="loc-text-wrapper">
                                <strong>Ulaşım</strong>
                                <span>Şehir merkezinden ve otogardan kampüse düzenli toplu taşıma ve zirveye özel servis imkanları.</span>
                            </div>
                        </div>

                    </div>

                    <div class="loc-actions">
                        <a href="https://www.google.com/maps/place/Cumhuriyet+Konferans+Salonu/@40.9048828,31.1769302,627m/data=!3m2!1e3!4b1!4m6!3m5!1s0x409da1fc4298e5d5:0x212f09eeae468de!8m2!3d40.9048788!4d31.1795051!16s%2Fg%2F11fm78kmvc?entry=ttu&g_ep=EgoyMDI2MDIxOC4wIKXMDSoASAFQAw%3D%3D" target="_blank" class="btn-primary-outline">
                            <span>Yol Tarifi Al</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
                        </a>
                    </div>
                </div>

                <div class="loc-visual">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3013.918967917206!2d31.170666376550795!3d40.90263897136533!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x409d75076df3d6b1%3A0x633633602f232497!2sD%C3%BCzce%20%C3%9Cniversitesi%20Cumhuriyet%20Konferans%20Salonu!5e0!3m2!1str!2str!4v1709667781234!5m2!1str!2str" 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </section>
	
		<?php echo do_shortcode('[ybs_sponsorlar]'); ?>


    <section id="sss" class="faq-section">
        <div class="container">
            
            <div class="faq-layout">
                <div class="faq-header">
                    <h2 class="section-title">Merak Edilenler</h2>
                    <p class="section-subtitle">Zirve hakkında sıkça sorulan sorular ve cevapları.</p>
                    <a href="/iletisim" class="faq-contact-link">Ekibe Ulaşın →</a>
                </div>

                <div class="faq-list">
                    
                    <div class="faq-item">
                        <button class="faq-btn">
                            <span class="faq-q">Zirve ne zaman ve nerede gerçekleşecek?</span>
                            <span class="faq-toggle">+</span>
                        </button>
                        <div class="faq-content">
                            <p>Zirve, Düzce Üniversitesi yerleşkesinde gerçekleştirilecektir. Etkinlik tarihi ve salon bilgileri kayıt yaptıran katılımcılarla ayrıca paylaşılacaktır.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-btn">
                            <span class="faq-q">Etkinliğe kimler katılabilir?</span>
                            <span class="faq-toggle">+</span>
                        </button>
                        <div class="faq-content">
                            <p>Başta üniversite öğrencileri olmak üzere teknoloji, iş dünyası ve dijital dönüşüm konularına ilgi duyan herkes katılabilir. Etkinlik dış katılımcılara da açıktır.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-btn">
                            <span class="faq-q">Kayıt ücretli mi, nasıl kayıt olabilirim?</span>
                            <span class="faq-toggle">+</span>
                        </button>
                        <div class="faq-content">
                            <p>Katılım ücretsizdir. Zirveye katılmak için web sitemizde yer alan kayıt formunu doldurmanız yeterlidir.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-btn">
                            <span class="faq-q">Kayıt için son tarih nedir?</span>
                            <span class="faq-toggle">+</span>
                        </button>
                        <div class="faq-content">
                            <p>Kayıtlar kontenjan dolana kadar devam etmektedir. Erken kayıt yaptırmanız önerilir.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-btn">
                            <span class="faq-q">Kontenjan sınırı var mı?</span>
                            <span class="faq-toggle">+</span>
                        </button>
                        <div class="faq-content">
                            <p>Evet. Etkinlik salon kapasitesi nedeniyle katılım kontenjanla sınırlıdır.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-btn">
                            <span class="faq-q">Etkinlik programına nereden ulaşabilirim?</span>
                            <span class="faq-toggle">+</span>
                        </button>
                        <div class="faq-content">
                            <p>Detaylı etkinlik programı web sitemizde yayınlanacaktır. Güncellemeler sosyal medya hesaplarımız üzerinden de duyurulacaktır.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-btn">
                            <span class="faq-q">Konuşmacılar kimler?</span>
                            <span class="faq-toggle">+</span>
                        </button>
                        <div class="faq-content">
                            <p>Zirvede sektör profesyonelleri, akademisyenler ve alanında uzman konuşmacılar yer alacaktır. Konuşmacı listesi web sitemiz ve sosyal medya hesaplarımızdan paylaşılacaktır.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-btn">
                            <span class="faq-q">Zirve sonunda sertifika verilecek mi?</span>
                            <span class="faq-toggle">+</span>
                        </button>
                        <div class="faq-content">
                            <p>Evet. Gerekli katılım koşullarını sağlayan katılımcılara QR kodlu, doğrulanabilir dijital katılım sertifikası verilecektir.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-btn">
                            <span class="faq-q">Sertifika alabilmek için tüm oturumlara katılmak zorunlu mu?</span>
                            <span class="faq-toggle">+</span>
                        </button>
                        <div class="faq-content">
                            <p>Sertifika alabilmek için belirlenen minimum oturum katılım şartının sağlanması gerekmektedir.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-btn">
                            <span class="faq-q">Şehir dışından gelecekler için konaklama imkanı sağlanıyor mu?</span>
                            <span class="faq-toggle">+</span>
                        </button>
                        <div class="faq-content">
                            <p>Şehir dışından gelen katılımcılar için üniversite çevresi, şehir merkezi ve Akçakoca bölgesinde anlaşmalı konaklama ve transfer seçenekleri hakkında yönlendirme sağlanacaktır.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-btn">
                            <span class="faq-q">Etkinlik alanına nasıl ulaşım sağlayabilirim?</span>
                            <span class="faq-toggle">+</span>
                        </button>
                        <div class="faq-content">
                            <p>Düzce Üniversitesi yerleşkesine şehir içi toplu taşıma, dolmuş hatları ve özel araç ile kolayca ulaşım sağlanabilmektedir. Detaylı ulaşım bilgileri kayıt sonrası paylaşılacaktır.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-btn">
                            <span class="faq-q">Sponsorluk veya iş birliği için kimlerle iletişime geçebilirim?</span>
                            <span class="faq-toggle">+</span>
                        </button>
                        <div class="faq-content">
                            <p>Sponsorluk ve iş birliği talepleriniz için organizasyon ekibi ile iletişim sayfamız üzerinden bağlantıya geçebilirsiniz.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-btn">
                            <span class="faq-q">Zirve online olarak yayınlanacak mı?</span>
                            <span class="faq-toggle">+</span>
                        </button>
                        <div class="faq-content">
                            <p>Zirvenin belirli oturumlarının çevrim içi yayınlanması planlanmaktadır. Yayın detayları etkinlik öncesinde duyurulacaktır.</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>
    
    <?php # echo do_shortcode('[ybs_kulupler]'); ?>

</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const textElement = document.getElementById('dynamicText');
    if(textElement) {
        const phrases = [
            "Bağ Kur",       // Kitapçıktan
            "İlham Al",      // Kitapçıktan
            "Geleceği Yönet" // Kitapçıktan
        ];
        let currentIndex = 0;
        setInterval(() => {
            textElement.classList.remove('slide-up-in');
            textElement.classList.add('slide-up-out');
            setTimeout(() => {
                currentIndex = (currentIndex + 1) % phrases.length;
                textElement.textContent = phrases[currentIndex];
                textElement.classList.remove('slide-up-out');
                textElement.classList.add('slide-up-in');
            }, 500);
        }, 2000);
    }
});
</script>

<?php get_footer(); ?>
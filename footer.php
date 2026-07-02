<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package YBS_Zirvesi_2026
 */
$build_date = date('ymd'); // 260328 gibi
$build_num  = date('z') . date('H'); // Yılın günü ve saat
$app_version = "v1.1." . $build_date . "." . $build_num;
?>
<footer class="site-footer">
    <div class="container">
        
        <div class="footer-top">
            
            <div class="footer-brand-area">
                <span class="footer-logo">YBS ZİRVESİ '26</span>
                <p class="footer-desc">
                    Teknoloji, inovasyon ve kariyer odaklı Türkiye'nin en kapsamlı YBS öğrenci zirvesi.
                </p>
                
                <div class="brand-contact">
                    <a href="mailto:info@2026.ybszirve.ogr.tr" class="contact-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>
                        <span>info@2026.ybszirve.ogr.tr</span>
                    </a>
                    <div class="contact-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <span>Düzce Üniversitesi</span>
                    </div>
                </div>
            </div>
            
            <div class="footer-newsletter">
                <div class="newsletter-text">
                    <label style="color: #111827; font-weight: 700; font-size: 1.125rem; display: block; margin-bottom: 8px;">Organizatör: DÜYBS</label>
                    <p class="newsletter-sub">10. Ulusal YBS Zirvesi, Düzce Üniversitesi Yönetim Bilişim Sistemleri Topluluğu tarafından gururla organize edilmektedir. Topluluğumuzu ve projelerimizi yakından tanıyın.</p>
                </div>
                
                <div style="margin-top: 20px;">
                    <a href="https://duybs.com" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; background: #111827; color: white; border-radius: 8px; padding: 14px 20px; font-size: 1rem; font-weight: 600; text-decoration: none; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                        <span>duybs.com'u Ziyaret Et</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    </a>
                </div>
            </div>
            </div>

        <div class="footer-nav-grid">
            <div class="footer-col">
                <h4 class="col-title">Etkinlik</h4>
                <ul class="footer-links">
                    <li><a href="https://2026.ybszirve.org.tr/program/">Program Akışı</a></li>
                    <li><a href="https://2026.ybszirve.org.tr/konusmacilar/">Konuşmacılar</a></li>
                    <li><a href="https://2026.ybszirve.org.tr/ulasim/">Ulaşım & Konaklama</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4 class="col-title">Topluluk</h4>
                <ul class="footer-links">
                    <li><a href="https://2026.ybszirve.org.tr/hakkimizda/">Hakkımızda</a></li>
                    <li><a href="https://2026.ybszirve.org.tr/organizasyon-ekibi/">Organizasyon Ekibi</a></li>
                    <li><a href="https://2026.ybszirve.org.tr/sponsor-ol/">Sponsor Olun</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4 class="col-title">Yasal</h4>
                <ul class="footer-links">
                    <li><a href="https://2026.ybszirve.org.tr/gizlilik-ve-cerez-politikasi/">Gizlilik ve Çerez Politikası</a></li>
                    <li><a href="https://2026.ybszirve.org.tr/kvkk-aydinlatma-metni/">KVKK Aydınlatma</a></li>
                    <li><a href="https://2026.ybszirve.org.tr/kullanim-sartlari/">Kullanım Şartları</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4 class="col-title">Sosyal Medya</h4>
                <ul class="social-links">
                    <li><a href="https://www.instagram.com/du.ybs">Instagram (@du.ybs)</a></li>
                    <li><a href="https://www.instagram.com/ybsenstitusu/">Instagram (@ybsenstitusu)</a></li>
                    <li><a href="https://duybs.com">duybs.com</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom-strip">
            
            <div class="footer-logos">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/dulogo.png" alt="Düzce Üniversitesi" title="Düzce Üniversitesi">
                <span class="logo-sep">|</span>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/ybsenstitu.png" alt="YBS Enstitü" title="YBS Enstitü">
                <span class="logo-sep">|</span>
                <img src="https://2026.ybszirve.org.tr/dosyalar/dernek.png" alt="KBD" title="KBD">
                <span class="logo-sep">|</span>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/duybs-black.png" alt="Düzce YBS Topluluğu" title="Düzce YBS Topluluğu">
                <span class="logo-sep">|</span>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/zirvelogo.png" alt="Ulusal YBS Zirvesi" title="Ulusal YBS Zirvesi">
            </div>

            <div class="footer-credits">
                <div class="dev-team">
                    <span>Developed by <strong>DÜYBS Ar-Ge</strong></span>
                </div>
                <div class="app-version">
                    System Build: <span class="version-number"><?php echo $app_version; ?></span>
                </div>
            </div>

        </div>

    </div>
</footer>
</div>
<?php 
// Eğer bulunduğumuz sayfa "rezervasyon" DEĞİLSE bu kodları çalıştır
if ( ! is_page('rezervasyon') ) : 
?>
<div id="ybs-rez-popup-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.7); z-index: 99999; justify-content: center; align-items: center; padding: 20px; box-sizing: border-box; backdrop-filter: blur(4px); opacity: 0; transition: opacity 0.4s ease;">
    <div style="background: #ffffff; width: 100%; max-width: 420px; border-radius: 16px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); position: relative; transform: translateY(30px); transition: transform 0.4s ease;" id="ybs-rez-popup-box">
        
        <button onclick="closeRezPopup()" style="position: absolute; top: 12px; right: 12px; background: rgba(255,255,255,0.2); border: none; width: 32px; height: 32px; border-radius: 50%; font-size: 22px; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 2; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.4)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">&times;</button>
        
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); padding: 40px 20px 25px; text-align: center; position: relative;">
            <div style="font-size: 48px; line-height: 1; margin-bottom: 15px;">🎟️</div>
            <h2 style="color: #ffffff; margin: 0 0 8px 0; font-size: 22px; font-weight: 800; letter-spacing: -0.5px;">Biletler Tükenmek Üzere!</h2>
            <p style="color: #bfdbfe; margin: 0; font-size: 14px; font-weight: 500;">10. Ulusal YBS Öğrenci Zirvesi yaklaşıyor.</p>
        </div>
        
        <div style="padding: 30px 25px; text-align: center;">
            <p style="color: #475569; font-size: 14px; margin: 0 0 25px 0; line-height: 1.6;">
                Sektörün önde gelen isimleriyle tanışmak, harika atölyelere katılmak ve networking ağını genişletmek için koltuğunu hemen seç.
            </p>
            
            <a href="https://2026.ybszirve.org.tr/rezervasyon/" style="display: block; width: 100%; background: #3b82f6; color: #ffffff; text-decoration: none; padding: 16px; border-radius: 10px; font-size: 15px; font-weight: bold; box-sizing: border-box; transition: all 0.2s; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);" onmouseover="this.style.background='#2563eb'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='#3b82f6'; this.style.transform='translateY(0)';">
                Koltuk Seç & Rezervasyon Yap
            </a>
            
            <button onclick="closeRezPopup()" style="background: none; border: none; color: #94a3b8; font-size: 13px; margin-top: 15px; cursor: pointer; text-decoration: underline; font-weight: 600;">Belki daha sonra</button>
        </div>
    </div>
</div>

<script>
    function ybs_getCookie(name) {
        const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    function ybs_setCookie(name, value, days) {
        const expires = new Date(Date.now() + days * 864e5).toUTCString();
        document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + expires + '; path=/; SameSite=Lax';
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (!ybs_getCookie('ybs_rez_popup_closed')) {
            setTimeout(function() {
                const overlay = document.getElementById('ybs-rez-popup-overlay');
                const box = document.getElementById('ybs-rez-popup-box');
                if (overlay && box) {
                    overlay.style.display = 'flex';
                    setTimeout(() => {
                        overlay.style.opacity = '1';
                        box.style.transform = 'translateY(0)';
                    }, 50);
                }
            }, 3500);
        }
    });

    function closeRezPopup() {
        const overlay = document.getElementById('ybs-rez-popup-overlay');
        const box = document.getElementById('ybs-rez-popup-box');
        overlay.style.opacity = '0';
        box.style.transform = 'translateY(30px)';
        setTimeout(() => { overlay.style.display = 'none'; }, 400);
        ybs_setCookie('ybs_rez_popup_closed', '1', 15);
    }
</script>
<?php endif; ?>
<?php wp_footer(); ?>

</body>
</html>
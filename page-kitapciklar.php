<?php
/* Template Name: Kitapçıklar Sayfası */

get_header();

// -------------------------------------------------------------------------
// PDF LİNKLERİNİ BURAYA GİRİNİZ
// -------------------------------------------------------------------------
$speaker_pdf     = 'https://2026.ybszirve.org.tr/dosyalar/Kitapciklar/Konu%C5%9Fmac%C4%B1%20Bilgilendirme%20ve%20Rehber%20Kitap%C3%A7%C4%B1%C4%9F%C4%B1.pdf'; 
$participant_pdf = 'https://2026.ybszirve.org.tr/dosyalar/Kitapciklar/Kat%C4%B1l%C4%B1mc%C4%B1%20Bilgilendirme%20ve%20Rehber%20Kitap%C3%A7%C4%B1%C4%9F%C4%B1.pdf'; 
$sponsor_pdf     = 'https://2026.ybszirve.org.tr/dosyalar/Kitapciklar/sponsorluk_kitapcigi.pdf'; 
$otel_pdf        = 'https://2026.ybszirve.org.tr/dosyalar/Kitapciklar/OTEL.pdf'; 
// -------------------------------------------------------------------------
?>

<div class="page-container" style="padding-top: 140px; padding-bottom: 80px;">
    
    <div class="section-header">
        <h2 class="section-title">Dijital Kitapçıklar</h2>
        <p class="section-subtitle">Zirve detaylarına, program akışına ve sponsorluk fırsatlarına dijital dosyalarımızdan ulaşabilirsiniz.</p>
    </div>

    <div class="booklet-controls">
        <button id="btn-speaker" class="booklet-btn active" onclick="loadBooklet('speaker', this)">
            <span class="btn-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"></path><path d="M19 10v2a7 7 0 0 1-14 0v-2"></path><line x1="12" y1="19" x2="12" y2="22"></line></svg>
            </span>
            <span class="btn-text">Konuşmacı Rehberi</span>
        </button>
        
        <button id="btn-participant" class="booklet-btn" onclick="loadBooklet('participant', this)">
            <span class="btn-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </span>
            <span class="btn-text">Katılımcı Rehberi</span>
        </button>

        <button id="btn-sponsor" class="booklet-btn" onclick="loadBooklet('sponsor', this)">
            <span class="btn-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            </span>
            <span class="btn-text">Sponsorluk Dosyası</span>
        </button>
        
        <button id="btn-otel" class="booklet-btn" onclick="loadBooklet('otel', this)">
            <span class="btn-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"></path><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path><path d="M9 21v-4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v4"></path><path d="M9 7h6"></path><path d="M9 11h6"></path></svg>
            </span>
            <span class="btn-text">Otel Rehber Dosyası</span>
        </button>
    </div>

    <div class="booklet-viewer-wrapper">
        <div id="loading-spinner" class="loading-spinner">Yükleniyor...</div>
        <iframe id="pdf-frame" src="" width="100%" height="100%" frameborder="0"></iframe>
    </div>

    <div class="download-area">
        <a id="download-link" href="#" class="download-btn" onclick="forceDownload(event)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px; vertical-align: middle;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            <span id="dl-text">Orijinal Dosyayı İndir (PDF)</span>
        </a>
    </div>

</div>

<script>
    const pdfs = {
        speaker: '<?php echo $speaker_pdf; ?>',
        participant: '<?php echo $participant_pdf; ?>',
        sponsor: '<?php echo $sponsor_pdf; ?>',
        otel: '<?php echo $otel_pdf; ?>'
    };

    let currentPdfUrl = '';
    let currentPdfType = '';

    function loadBooklet(type, btnElement) {
        document.querySelectorAll('.booklet-btn').forEach(btn => btn.classList.remove('active'));
        btnElement.classList.add('active');

        const iframe = document.getElementById('pdf-frame');
        const spinner = document.getElementById('loading-spinner');

        spinner.style.opacity = '1';
        iframe.style.opacity = '0.5';

        currentPdfUrl = pdfs[type];
        currentPdfType = type;
        
        // --- GOOGLE DOCS SINIRI İPTAL EDİLDİ ---
        // Doğrudan tarayıcının native PDF okuyucusunu kullanıyoruz (#view=FitH sayfayı ekrana sığdırır)
        iframe.src = currentPdfUrl + '#view=FitH';

        // İframe yüklendiğinde yükleniyor yazısını gizle
        iframe.onload = function() {
            spinner.style.opacity = '0';
            iframe.style.opacity = '1';
        };
        
        // Native iframe bazen onload tetiklemezse diye 1.5 saniye sonra kesin aç
        setTimeout(() => {
            spinner.style.opacity = '0';
            iframe.style.opacity = '1';
        }, 1500);

        // URL'yi değiştir (Deep Linking)
        const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?belge=' + type;
        window.history.pushState({path:newUrl}, '', newUrl);
    }

    // --- ZORLA İNDİRME FONKSİYONU ---
    function forceDownload(e) {
        e.preventDefault();
        if (!currentPdfUrl) return;

        const dlText = document.getElementById('dl-text');
        dlText.innerText = "İndiriliyor, Lütfen Bekleyin...";

        fetch(currentPdfUrl)
            .then(response => response.blob())
            .then(blob => {
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = "YBS_Zirvesi_" + currentPdfType + ".pdf";
                
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                dlText.innerText = "Orijinal Dosyayı İndir (PDF)";
            })
            .catch(err => {
                console.error(err);
                // Eğer CORS/Sunucu engeline takılırsa alternatif indirme yöntemi
                window.open(currentPdfUrl, '_blank');
                dlText.innerText = "Orijinal Dosyayı İndir (PDF)";
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const belge = urlParams.get('belge');
        
        if (belge === 'sponsor') {
            document.getElementById('btn-sponsor').click();
        } else if (belge === 'participant') {
            document.getElementById('btn-participant').click();
        } else if (belge === 'otel') {
            document.getElementById('btn-otel').click();
        } else {
            document.getElementById('btn-speaker').click();
        }
    });
</script>

<style>
    .section-header { text-align: center; max-width: 700px; margin: 0 auto 40px auto; }
    .section-title { font-size: 2.5rem; font-weight: 800; color: var(--header-bg); margin-bottom: 10px; }
    .section-subtitle { font-size: 1.1rem; color: var(--text-grey); }

    .booklet-controls { display: flex; justify-content: center; gap: 15px; margin-bottom: 30px; flex-wrap: wrap; }
    
    .booklet-btn {
        display: flex; align-items: center; gap: 10px; padding: 14px 24px;
        border: 2px solid #E2E8F0; background: #fff; border-radius: 8px;
        cursor: pointer; transition: all 0.3s ease; font-size: 0.95rem;
        font-weight: 700; color: var(--text-dark); min-width: 200px; justify-content: center;
    }
    .booklet-btn:hover { border-color: var(--accent-teal); transform: translateY(-2px); }
    .booklet-btn.active { background-color: var(--header-bg); color: #fff; border-color: var(--header-bg); box-shadow: 0 4px 15px rgba(0, 40, 85, 0.15); }
    .booklet-btn svg { transition: stroke 0.3s ease; }
    
    .booklet-viewer-wrapper {
        position: relative; width: 100%; max-width: 1000px;
        height: 80vh; min-height: 600px; margin: 0 auto;
        background: #f1f5f9; border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; border: 1px solid #E2E8F0;
    }

    iframe { border: none; transition: opacity 0.3s ease; display: block; background: #e5e7eb; }

    .loading-spinner {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
        color: var(--header-bg); font-weight: 700; pointer-events: none; transition: opacity 0.3s; z-index: 0;
    }

    .download-area { text-align: center; margin-top: 25px; }
    .download-btn {
        display: inline-block; font-size: 0.95rem; color: var(--text-grey);
        text-decoration: none; font-weight: 600; border-bottom: 1px dashed var(--text-grey); transition: all 0.2s; padding-bottom: 2px; cursor: pointer;
    }
    .download-btn:hover { color: var(--accent-teal); border-color: var(--accent-teal); }

    @media (max-width: 768px) {
        .booklet-viewer-wrapper { height: 65vh; }
        .booklet-btn { width: 100%; }
        .section-title { font-size: 2rem; }
    }
</style>

<?php get_footer(); ?>
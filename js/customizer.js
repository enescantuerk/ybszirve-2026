document.addEventListener('DOMContentLoaded', function() {
    console.log('1. JS Yüklendi ve Hazır.');

    const header = document.getElementById('masthead');
    const scrollThreshold = 50;

    if (!header) {
        console.error('HATA: "masthead" IDli header bulunamadı! header.php dosyanı kontrol et.');
        return;
    } else {
        console.log('2. Header bulundu.');
    }

    function handleScroll() {
        console.log('Scroll yapılıyor: ' + window.scrollY); // Bunu console'da görmelisin
        
        if (window.scrollY > scrollThreshold) {
            if (!header.classList.contains('is-scrolled')) {
                console.log('-> Class Eklendi: is-scrolled');
                header.classList.add('is-scrolled');
            }
        } else {
            if (header.classList.contains('is-scrolled')) {
                console.log('-> Class Kaldırıldı');
                header.classList.remove('is-scrolled');
            }
        }
    }

    window.addEventListener('scroll', handleScroll, { passive: true });
    
    // Mobil Menü Kodu
    const menuToggle = document.querySelector('.menu-toggle');
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            header.classList.toggle('toggled');
            console.log('Menü tıklandı');
        });
    } else {
        console.warn('Uyarı: .menu-toggle butonu bulunamadı (Masaüstündeysen normal)');
    }
});
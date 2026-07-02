/* ==========================================================================
   1. GLOBAL FONKSİYONLAR (HTML onclick için dışarıda olmalı)
   ========================================================================== */


/* ==========================================================================
   2. DOM YÜKLENDİKTEN SONRA ÇALIŞACAKLAR
   ========================================================================== */
document.addEventListener('DOMContentLoaded', function() {

    // --- A. HEADER SCROLL & MOBİL MENÜ ---
    const header = document.getElementById('masthead');
    const menuToggle = document.querySelector('.menu-toggle');
    
    if (header) {
        // Scroll İşlemi
        window.addEventListener('scroll', function() {
            if (window.scrollY > 30) {
                header.classList.add('is-scrolled');
            } else {
                header.classList.remove('is-scrolled');
            }
        }, { passive: true });

        // Mobil Menü İşlemleri
        if (menuToggle) {
            // Menü Aç/Kapa
            function toggleMenu() {
                const isOpen = header.classList.toggle('toggled');
                menuToggle.setAttribute('aria-expanded', isOpen);
            }

            menuToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleMenu();
            });

            // Mobil Alt Menü (Dropdown) Tıklama
            const dropdowns = document.querySelectorAll('.menu-item-has-children > a');
            dropdowns.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (window.innerWidth <= 900) {
                        e.preventDefault(); // Sayfa yenilenmesini engelle
                        e.stopPropagation();
                        
                        const submenu = this.nextElementSibling;
                        if (submenu) {
                            // Diğer açık menüleri kapat (Opsiyonel)
                            document.querySelectorAll('.sub-menu').forEach(el => {
                                if (el !== submenu) el.style.display = 'none';
                            });
                            
                            // Tıklananı aç/kapa
                            submenu.style.display = (submenu.style.display === 'block' ? 'none' : 'block');
                        }
                    }
                });
            });

            // Menü Linkine Tıklayınca Menüyü Kapat
            const allLinks = document.querySelectorAll('.main-navigation a');
            allLinks.forEach(link => {
                link.addEventListener('click', function() {
                    // Sadece alt menü tetikleyicisi değilse kapat
                    if (!this.parentElement.classList.contains('menu-item-has-children') || window.innerWidth > 900) {
                        if (header.classList.contains('toggled')) {
                            setTimeout(toggleMenu, 300);
                        }
                    }
                });
            });

            // Dışarı tıklayınca kapat
            document.addEventListener('click', function(event) {
                if (window.innerWidth > 900 && !header.contains(event.target) && header.classList.contains('toggled')) {
                    toggleMenu();
                }
            });
        }
    }

    // --- B. DİNAMİK METİN (SLIDER) ---


    // --- C. FAQ ACCORDION ---
    const acc = document.getElementsByClassName("faq-btn");
    for (let i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function() {
            this.classList.toggle("active");
            const panel = this.nextElementSibling;
            if (panel.style.maxHeight) {
                panel.style.maxHeight = null;
            } else {
                panel.style.maxHeight = panel.scrollHeight + "px";
            }
        });
    }

});
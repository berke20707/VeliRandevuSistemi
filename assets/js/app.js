// =========================================
// 1. PARTICLES.JS (UZAY BOŞLUĞU EFEKTİ)
// =========================================
document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById('particles-js')) {
        particlesJS("particles-js", {
            "particles": {
                "number": { "value": 70, "density": { "enable": true, "value_area": 800 } },
                "color": { "value": "#5BC0BE" },
                "shape": { "type": "circle" },
                "opacity": { "value": 0.5, "random": true },
                "size": { "value": 3, "random": true },
                "line_linked": {
                    "enable": true,
                    "distance": 150,
                    "color": "#5BC0BE",
                    "opacity": 0.2,
                    "width": 1
                },
                "move": {
                    "enable": true,
                    "speed": 1.5,
                    "direction": "none",
                    "random": true,
                    "straight": false,
                    "out_mode": "out",
                    "bounce": false
                }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": { "enable": true, "mode": "grab" },
                    "onclick": { "enable": true, "mode": "push" },
                    "resize": true
                },
                "modes": {
                    "grab": { "distance": 180, "line_linked": { "opacity": 0.8 } },
                    "push": { "particles_nb": 3 }
                }
            },
            "retina_detect": true
        });
    }
});

// =========================================
// 2. ŞİFRE GÖSTER / GİZLE 
// =========================================
function togglePassword() {
    const passwordInput = document.getElementById("sifreInput");
    const eyeIcon = document.getElementById("eyeIcon");

    if (passwordInput && eyeIcon) {
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            eyeIcon.classList.remove("fa-eye");
            eyeIcon.classList.add("fa-eye-slash");
            eyeIcon.style.color = "#5BC0BE";
        } else {
            passwordInput.type = "password";
            eyeIcon.classList.remove("fa-eye-slash");
            eyeIcon.classList.add("fa-eye");
            eyeIcon.style.color = "#A0B2C6";
        }
    }
}

// =========================================
// 3. DASHBOARD 3D TILT (KART EĞİLME) EFEKTİ
// =========================================
document.addEventListener('DOMContentLoaded', function () {
    if (typeof VanillaTilt !== 'undefined') {
        VanillaTilt.init(document.querySelectorAll(".tilt-card"), {
            max: 15,
            speed: 400,
            glare: true,
            "max-glare": 0.2,
            scale: 1.02
        });
    }
});

// =========================================
// 4. TELEFON NUMARASI OTOMATİK FORMATLAYICI
// =========================================
document.addEventListener('input', function (e) {
    if (e.target && e.target.name === 'telefon') {
        let x = e.target.value.replace(/\D/g, '').match(/(\d{0,4})(\d{0,3})(\d{0,2})(\d{0,2})/);
        e.target.value = !x[2] ? x[1] : x[1] + ' ' + x[2] + (x[3] ? ' ' + x[3] : '') + (x[4] ? ' ' + x[4] : '');
    }
});



// =========================================
// 6. DARK / LIGHT TEMA LİSTENER
// =========================================
// Temayı zorla yükle (Sayfa açılışında parlama önlemek için <head> kısmında da kontrol edilmelidir)
if (localStorage.getItem('themeMode') === 'light') {
    document.body.classList.add('light-mode');
}

function toggleThemeMode() {
    if (document.body.classList.contains('light-mode')) {
        document.body.classList.remove('light-mode');
        localStorage.setItem('themeMode', 'dark');
        updateThemeIcon('dark');
    } else {
        document.body.classList.add('light-mode');
        localStorage.setItem('themeMode', 'light');
        updateThemeIcon('light');
    }
}

function updateThemeIcon(mode) {
    const icon = document.getElementById('theme-icon-indicator');
    if(icon) {
        if(mode === 'light') {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
            icon.style.color = '#f6c23e'; 
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
            icon.style.color = '#A0B2C6';
        }
    }
}

document.addEventListener("DOMContentLoaded", function () {
    updateThemeIcon(localStorage.getItem('themeMode') === 'light' ? 'light' : 'dark');
});
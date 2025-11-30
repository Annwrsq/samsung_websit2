// script.js - Анімації для сайту Samsung

document.addEventListener('DOMContentLoaded', function() {
    console.log('Сайт Samsung завантажено!');
    
    // Перевіряємо чи це головна сторінка з телефоном
    const isHomePage = document.querySelector('.phone-hero') !== null;
    
    if (isHomePage) {
        console.log('Головна сторінка - ініціалізація анімацій телефона');
        initPhoneAnimations();
        createAnimationControls();
        addInteractivity();
    } else {
        console.log('Інша сторінка - базові функції');
        // Можна додати базові анімації для інших сторінок
        addBasicAnimations();
    }
});

// ===== АНІМАЦІЇ ТЕЛЕФОНА (ТІЛЬКИ ДЛЯ ГОЛОВНОЇ) =====
function initPhoneAnimations() {
    const phoneDevice = document.querySelector('.phone-device');
    if (!phoneDevice) return;
    
    console.log('Анімація Samsung Galaxy S25 ініціалізована!');
    
    // Ефект паралаксу при скролі
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const rate = scrolled * -0.3;
        phoneDevice.style.transform = `translateY(${rate}px) rotateX(5deg) rotateY(-5deg)`;
    });
    
    // Анімація іконок додатків
    animateAppIcons();
}

function animateAppIcons() {
    const appIcons = document.querySelectorAll('.app-icon');
    
    setInterval(() => {
        appIcons.forEach((icon, index) => {
            setTimeout(() => {
                icon.style.animation = 'none';
                setTimeout(() => {
                    icon.style.animation = 'appPulse 3s ease-in-out infinite';
                }, 50);
            }, index * 300);
        });
    }, 6000);
}

// ===== ПАНЕЛЬ КЕРУВАННЯ АНІМАЦІЯМИ (ТІЛЬКИ ДЛЯ ГОЛОВНОЇ) =====
function createAnimationControls() {
    const controlsHTML = `
        <div class="animation-controls">
            <h3>🎮 Керування анімаціями</h3>
            <div class="controls-buttons">
                <button class="anim-btn" onclick="startAllAnimations()">▶️ Запустити всі</button>
                <button class="anim-btn" onclick="stopAllAnimations()">⏹️ Зупинити всі</button>
                <button class="anim-btn" onclick="animatePhone()">📱 Анімувати телефон</button>
                <button class="anim-btn" onclick="animateImages()">🖼️ Анімувати зображення</button>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', controlsHTML);
}

// ===== ІНТЕРАКТИВНІСТЬ (ТІЛЬКИ ДЛЯ ГОЛОВНОЇ) =====
function addInteractivity() {
    // Ефект при наведенні на телефон
    const phoneDevice = document.querySelector('.phone-device');
    if (phoneDevice) {
        phoneDevice.addEventListener('mouseenter', function() {
            this.style.animation = 'phoneFloat 2s ease-in-out infinite';
        });
        
        phoneDevice.addEventListener('mouseleave', function() {
            this.style.animation = 'phoneFloat 4s ease-in-out infinite';
        });
    }
    
    // Ефект для кнопки CTA
    const ctaButton = document.querySelector('.cta-button');
    if (ctaButton) {
        ctaButton.addEventListener('click', function() {
            this.style.animation = 'none';
            this.style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                this.style.animation = 'buttonPulse 2.5s ease-in-out infinite';
                this.style.transform = 'scale(1)';
                alert('Galaxy S25 - Інновації майбутнього вже сьогодні! 🚀');
            }, 150);
        });
    }
    
    // Ефекти при наведенні на іконки додатків
    const appIcons = document.querySelectorAll('.app-icon');
    appIcons.forEach(icon => {
        icon.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.15)';
            this.style.animation = 'none';
        });
        
        icon.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.animation = 'appPulse 3s ease-in-out infinite';
        });
    });
}

// ===== БАЗОВІ АНІМАЦІЇ ДЛЯ ВСІХ СТОРІНОК =====
function addBasicAnimations() {
    // Додаємо ефекти при наведенні на зображення для всіх сторінок
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        img.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Ефекти для карток
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
}

// ===== ФУНКЦІЇ АНІМАЦІЙ ДЛЯ КНОПОК =====

// Запуск всіх анімацій
function startAllAnimations() {
    console.log('🔥 Запуск ВСІХ анімацій!');
    const isHomePage = document.querySelector('.phone-hero') !== null;
    
    if (isHomePage) {
        animatePhone();
        animateImages();
        animateFeatures();
    } else {
        animateAllImages();
    }
}

// Зупинка всіх анімацій
function stopAllAnimations() {
    console.log('🛑 Зупинка всіх анімацій');
    const animatedElements = document.querySelectorAll('*');
    animatedElements.forEach(element => {
        element.style.animation = 'none';
    });
    
    // Повертаємо анімацію телефона якщо він є
    const phoneDevice = document.querySelector('.phone-device');
    if (phoneDevice) {
        setTimeout(() => {
            phoneDevice.style.animation = 'phoneFloat 4s ease-in-out infinite';
        }, 100);
    }
}

// Анімація телефона (тільки для головної)
function animatePhone() {
    const phone = document.querySelector('.phone-device');
    if (!phone) return;
    
    console.log('📱 Анімація телефона');
    phone.style.animation = 'phoneFloat 1s ease-in-out';
    setTimeout(() => {
        phone.style.animation = 'phoneFloat 4s ease-in-out infinite';
    }, 1000);
    
    // Інтенсивна анімація іконок
    const apps = document.querySelectorAll('.app-icon');
    apps.forEach((app, index) => {
        setTimeout(() => {
            app.style.animation = 'appPulse 0.8s ease-in-out 3';
        }, index * 150);
    });
}

// Анімація зображень
function animateImages() {
    console.log('🖼️ Анімація зображень');
    const images = document.querySelectorAll('img');
    images.forEach((img, index) => {
        img.style.animation = 'none';
        setTimeout(() => {
            img.style.animation = `bounceIn 0.8s ease ${index * 0.2}s both`;
        }, 50);
    });
}

// Анімація всіх зображень (для інших сторінок)
function animateAllImages() {
    const images = document.querySelectorAll('img');
    images.forEach((img, index) => {
        img.style.animation = 'none';
        setTimeout(() => {
            img.style.animation = `fadeInUp 0.6s ease ${index * 0.1}s both`;
        }, 50);
    });
}

// Анімація особливостей
function animateFeatures() {
    console.log('⭐ Анімація особливостей');
    const features = document.querySelectorAll('.feature');
    features.forEach((feature, index) => {
        feature.style.animation = 'none';
        setTimeout(() => {
            feature.style.animation = `featureSlide 0.6s ease ${index * 0.15}s both`;
        }, 50);
    });
}

// Глобальні функції для використання
window.startAllAnimations = startAllAnimations;
window.stopAllAnimations = stopAllAnimations;
window.animatePhone = animatePhone;
window.animateImages = animateImages;

console.log('Samsung Animations готові до використання!');
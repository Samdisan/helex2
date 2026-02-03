// HELIX SYSTEM CORE v3.2 [PATH FIX + DEBUG]

// Визначення правильного шляху до папки assets
// Якщо ми в папці chapter2 або chapter1, треба додати "../"
const isSubFolder = window.location.pathname.includes('/chapter2/') || window.location.pathname.includes('/chapter1/');
const pathPrefix = isSubFolder ? '../' : '';

// Audio Engine Configuration
const SFX = {
    click: new Audio(pathPrefix + 'assets/audio/click.mp3'),
    hover: new Audio(pathPrefix + 'assets/audio/hover.mp3'),
    error: new Audio(pathPrefix + 'assets/audio/error.mp3'),
    glitch: new Audio(pathPrefix + 'assets/audio/glitch.mp3'),
    bgm: new Audio(pathPrefix + 'assets/audio/ambience.mp3')
};

SFX.bgm.loop = true;
SFX.bgm.volume = 0.3;
SFX.hover.volume = 0.2;
SFX.click.volume = 0.5;

function stopAllSounds() {
    SFX.bgm.pause(); SFX.bgm.currentTime = 0;
    SFX.glitch.pause(); SFX.error.pause();
}

function playSound(name) {
    const audioAllowed = localStorage.getItem('helix_audio_enabled') === 'true';
    if (!audioAllowed) return;

    try {
        if (SFX[name]) {
            if (name !== 'bgm') SFX[name].currentTime = 0;
            const promise = SFX[name].play();
            if (promise !== undefined) {
                promise.catch(error => {
                    // Autoplay prevented handled silently
                });
            }
        }
    } catch (e) { console.warn("Audio missing or error:", name); }
}

// --- INITIALIZATION ---
document.addEventListener('DOMContentLoaded', () => {
    // Check if there is a start screen overlay visible
    const startScreen = document.getElementById('start-screen');
    const isStartVisible = startScreen && getComputedStyle(startScreen).display !== 'none';

    // If no start screen (e.g. personnel page), check memory and play bgm
    if (!isStartVisible) {
        if (localStorage.getItem('helix_audio_enabled') === 'true') {
            playSound('bgm');
        }
    }

    attachInterfaceSounds();
});

// GLOBAL FUNCTIONS
window.initAudioContext = function() {
    localStorage.setItem('helix_audio_enabled', 'true');
    playSound('bgm');
    playSound('click');
    attachInterfaceSounds();
};

window.disableAudioContext = function() {
    localStorage.setItem('helix_audio_enabled', 'false');
    stopAllSounds();
};

window.triggerErrorSound = function() { playSound('error'); };

function attachInterfaceSounds() {
    const els = document.querySelectorAll('a, button, .action-btn, .person-card, .btn, .btn-test, .option-btn, .close-btn');
    els.forEach(el => {
        // Remove old listeners to prevent duplicates
        el.removeEventListener('mouseenter', hoverHandler);
        el.removeEventListener('click', clickHandler);
        // Add new
        el.addEventListener('mouseenter', hoverHandler);
        el.addEventListener('click', clickHandler);
    });
}

const hoverHandler = () => playSound('hover');
const clickHandler = () => playSound('click');

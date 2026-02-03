const CACHE_NAME = 'helix-system-v4'; // Змінюй цифру, щоб оновити кеш у гравців
const ASSETS_TO_CACHE = [
  './',
  './index.html',
  './style.css',
  './system.js',
  './manifest.json',
  './chapter2/hub.html',
  './chapter2/profile.html',
  './chapter2/terminal.html',
  './chapter2/personnel.html',
  './chapter2/lore.html'
];

// 1. Встановлення (Кешування)
self.addEventListener('install', (evt) => {
  self.skipWaiting(); // Примусово активувати новий воркер
  evt.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[SW] Caching Assets');
      // Використовуємо map, щоб помилка в одному файлі не зупинила інші
      return Promise.all(
        ASSETS_TO_CACHE.map(url => {
          return cache.add(url).catch(err => console.error('[SW] Could not cache:', url));
        })
      );
    })
  );
});

// 2. Активація (Очистка старого кешу)
self.addEventListener('activate', (evt) => {
  evt.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(keys.map((key) => {
        if (key !== CACHE_NAME) return caches.delete(key);
      }));
    })
  );
  self.clients.claim();
});

// 3. Перехоплення запитів (Робота офлайн)
self.addEventListener('fetch', (evt) => {
  // Ігноруємо POST запити (логін, адмінка), бо вони потребують інтернету
  if (evt.request.method !== 'GET') return;

  evt.respondWith(
    caches.match(evt.request).then((cacheRes) => {
      // Якщо є в кеші — віддаємо кеш, якщо ні — ліземо в інтернет
      return cacheRes || fetch(evt.request).catch(() => {
        // Якщо немає інтернету і немає в кеші — можна повернути сторінку-заглушку (опціонально)
      });
    })
  );
});

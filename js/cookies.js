function acceptAllCookies() {
  localStorage.setItem('cookies_accepted', 'all');
  document.getElementById('cookie-consent').style.display = 'none';
}

function openCookiePanel() {
  const bar = document.getElementById('cookie-consent');
  const panel = document.getElementById('cookie-panel');
  if (bar && panel) {
    bar.classList.remove('active');
    panel.classList.add('active');
    panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
  } else {
    alert('Panel de cookies no disponible en esta vista.');
  }
}

function closeCookiePanel() {
  document.getElementById('cookie-panel').classList.remove('active');
  document.getElementById('cookie-consent').classList.add('active');
}

function saveCookiePreferences() {
  const analytics = document.getElementById('analytics-cookies').checked;
  const marketing = document.getElementById('marketing-cookies').checked;
  localStorage.setItem('cookie_prefs', JSON.stringify({ analytics, marketing }));
  alert('Preferencias guardadas.');
  closeCookiePanel();
}

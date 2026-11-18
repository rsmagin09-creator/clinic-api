// Общая функция отправки запросов к API Laravel и вспомогательные функции интерфейса
const TOKEN_KEY = 'clinic_token';

const auth = {
  get token() { return localStorage.getItem(TOKEN_KEY); },
  set(token) { localStorage.setItem(TOKEN_KEY, token); },
  clear() { localStorage.removeItem(TOKEN_KEY); localStorage.removeItem('clinic_user'); },
  get user() { try { return JSON.parse(localStorage.getItem('clinic_user')); } catch { return null; } },
  setUser(u) { localStorage.setItem('clinic_user', JSON.stringify(u)); },
};

/**
 * Отправка запроса. Возвращает { ok, status, data, message, errors }.
 * - заголовки Accept, Content-Type и Authorization: Bearer <token>
 * - 401: удаляет токен и перенаправляет на страницу входа
 * - 422: объект errors для вывода в форме; 409: message с причиной нарушения бизнес-правила
 * - ошибки сети и 500 — понятное сообщение
 */
async function api(method, path, body = null, { redirectOn401 = true } = {}) {
  const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json' };
  if (auth.token) headers['Authorization'] = 'Bearer ' + auth.token;

  let response;
  try {
    response = await fetch(API_BASE + path, { method, headers, body: body ? JSON.stringify(body) : null });
  } catch (e) {
    return { ok: false, status: 0, message: 'Сервер недоступен. Проверьте подключение и что API запущен.' };
  }

  const data = response.status === 204 ? null : await response.json().catch(() => null);
  const result = { ok: response.ok, status: response.status, data, message: data?.message, errors: data?.errors };

  if (response.status === 401 && redirectOn401) {
    auth.clear();
    location.href = 'login.html';
  }
  if (response.status >= 500) result.message = 'Ошибка сервера. Попробуйте позже.';
  return result;
}

const esc = s => String(s ?? '').replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
const ruDate = d => d ? d.split('-').reverse().join('.') : '';
const qs = obj => new URLSearchParams(Object.entries(obj).filter(([, v]) => v !== '' && v != null)).toString();

// Ошибки валидации (422) — рядом с полями формы
function showErrors(form, errors = {}, message = '') {
  form.querySelectorAll('.field-error').forEach(e => e.textContent = '');
  form.querySelectorAll('input, select, textarea').forEach(i => i.classList.remove('invalid'));
  for (const [field, msgs] of Object.entries(errors || {})) {
    const name = field.split('.')[0];
    const input = form.querySelector(`[name="${name}"]`);
    const box = form.querySelector(`[data-error="${name}"]`);
    if (input) input.classList.add('invalid');
    if (box) box.textContent = msgs.join(' ');
  }
  const alert = form.querySelector('.alert');
  if (alert) { alert.textContent = message || ''; alert.hidden = !message; alert.className = 'alert'; }
}

// Предварительная проверка обязательных полей на клиенте
function checkRequired(form) {
  const errors = {};
  form.querySelectorAll('[required]').forEach(i => {
    if (!String(i.value).trim()) errors[i.name] = ['Обязательное поле'];
  });
  if (Object.keys(errors).length) { showErrors(form, errors, 'Заполните обязательные поля'); return false; }
  return true;
}

// Всплывающее сообщение
function toast(text, type = 'ok') {
  let box = document.getElementById('toast');
  if (!box) { box = document.createElement('div'); box.id = 'toast'; document.body.appendChild(box); }
  box.className = 'toast ' + type; box.textContent = text; box.hidden = false;
  clearTimeout(box._t); box._t = setTimeout(() => box.hidden = true, 4000);
}

// Пагинация на основе блока meta из ответа Laravel
function renderPager(el, meta, onPage) {
  if (!meta || meta.last_page <= 1) { el.innerHTML = meta ? `<span class="muted">Всего: ${meta.total}</span>` : ''; return; }
  let html = `<button ${meta.current_page === 1 ? 'disabled' : ''} data-p="${meta.current_page - 1}">‹ Назад</button>`;
  for (let p = 1; p <= meta.last_page; p++) html += `<button class="${p === meta.current_page ? 'active' : ''}" data-p="${p}">${p}</button>`;
  html += `<button ${meta.current_page === meta.last_page ? 'disabled' : ''} data-p="${meta.current_page + 1}">Вперёд ›</button>`;
  html += `<span class="muted">Всего: ${meta.total}, стр. ${meta.current_page} из ${meta.last_page}</span>`;
  el.innerHTML = html;
  el.querySelectorAll('button[data-p]').forEach(b => b.onclick = () => onPage(+b.dataset.p));
}

// Шапка: меню по роли, имя и роль пользователя, выход
async function renderHeader({ requireAuth = false, roles = null } = {}) {
  const box = document.getElementById('user-box');
  const nav = document.querySelector('header nav');
  let user = null;
  if (auth.token) {
    const res = await api('GET', '/user');
    if (res.ok) { user = res.data.data; auth.setUser(user); }
  }
  if (!user && requireAuth) { location.href = 'login.html'; return null; }

  const links = [['index.html', 'Врачи'], ['prices.html', 'Цены']];
  if (user?.role === 'patient') links.push(['appointments.html', 'Мои записи'], ['medbook.html', 'Медкнижка']);
  if (user?.role === 'doctor') links.push(['appointments.html', 'Мои приёмы']);
  if (user?.role === 'admin') links.push(['appointments.html', 'Все записи'], ['admin.html', 'Администрирование']);
  const here = location.pathname.split('/').pop() || 'index.html';
  nav.innerHTML = links.map(([href, t]) => `<a href="${href}" class="${href === here ? 'current' : ''}">${t}</a>`).join('');

  if (!user) {
    box.innerHTML = '<a href="login.html">Войти</a> · <a href="register.html">Регистрация</a>';
  } else {
    box.innerHTML = `<span class="who">${esc(user.name)}</span> <span class="badge role-${user.role}">${esc(user.role_label)}</span>
      <button id="logout" class="btn-link">Выйти</button>`;
    document.getElementById('logout').onclick = async () => {
      await api('POST', '/logout', null, { redirectOn401: false });
      auth.clear(); location.href = 'login.html';
    };
  }
  // элементы, недоступные роли, скрываются (сервер всё равно проверяет права — 403)
  document.querySelectorAll('[data-role]').forEach(el => {
    el.hidden = !user || !el.dataset.role.split(',').includes(user.role);
  });
  if (roles && (!user || !roles.includes(user.role))) {
    document.querySelector('main').innerHTML = '<div class="card"><div class="alert">Недостаточно прав для просмотра этой страницы</div></div>';
    return null;
  }
  return user;
}

// при вводе убираем подсветку ошибки у поля
document.addEventListener('input', e => {
  const el = e.target; if (!el.name) return;
  el.classList.remove('invalid');
  const box = el.form?.querySelector(`[data-error="${el.name}"]`); if (box) box.textContent = '';
});

// сумма в тенге: 12500 → «12 500 ₸»
function tenge(v) { return v == null ? '—' : Number(v).toLocaleString('ru-RU').replace(/\u00a0/g, ' ') + ' ₸'; }

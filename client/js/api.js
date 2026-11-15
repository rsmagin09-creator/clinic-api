// Общая функция отправки запросов к API Laravel
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
 * - добавляет заголовки Accept, Content-Type и Authorization: Bearer <token>
 * - 401: удаляет токен и перенаправляет на страницу входа (кроме самого входа)
 * - 422: возвращает объект errors для вывода в форме
 * - ошибки сети и 500 — понятное сообщение
 */
async function api(method, path, body = null, { redirectOn401 = true } = {}) {
  const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json' };
  if (auth.token) headers['Authorization'] = 'Bearer ' + auth.token;

  let response;
  try {
    response = await fetch(API_BASE + path, {
      method,
      headers,
      body: body ? JSON.stringify(body) : null,
    });
  } catch (e) {
    return { ok: false, status: 0, message: 'Сервер недоступен. Проверьте подключение и что API запущен.' };
  }

  const data = response.status === 204 ? null : await response.json().catch(() => null);
  const result = { ok: response.ok, status: response.status, data, message: data?.message, errors: data?.errors };

  if (response.status === 401 && redirectOn401) {
    auth.clear();
    location.href = 'login.html';
  }
  if (response.status >= 500) {
    result.message = 'Ошибка сервера. Попробуйте позже.';
  }
  return result;
}

// Вывод ошибок валидации (422) рядом с полями формы
function showErrors(form, errors = {}, message = '') {
  form.querySelectorAll('.field-error').forEach(e => e.textContent = '');
  form.querySelectorAll('input, select').forEach(i => i.classList.remove('invalid'));
  for (const [field, msgs] of Object.entries(errors || {})) {
    const input = form.querySelector(`[name="${field}"]`);
    const box = form.querySelector(`[data-error="${field}"]`);
    if (input) input.classList.add('invalid');
    if (box) box.textContent = msgs.join(' ');
  }
  const alert = form.querySelector('.alert');
  if (alert) { alert.textContent = message || ''; alert.hidden = !message; }
}

// Шапка с именем и ролью текущего пользователя и кнопкой выхода
async function renderHeader() {
  const box = document.getElementById('user-box');
  if (!auth.token) {
    box.innerHTML = '<a href="login.html">Войти</a> · <a href="register.html">Регистрация</a>';
    return null;
  }
  const res = await api('GET', '/user');
  if (!res.ok) return null;
  const u = res.data.data;
  auth.setUser(u);
  box.innerHTML = `<span class="who">${u.name}</span> <span class="badge role-${u.role}">${u.role_label}</span>
    <button id="logout" class="btn-link">Выйти</button>`;
  document.getElementById('logout').onclick = async () => {
    await api('POST', '/logout', null, { redirectOn401: false });
    auth.clear();
    location.href = 'login.html';
  };
  return u;
}

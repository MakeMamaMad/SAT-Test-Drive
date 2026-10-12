document.querySelectorAll('.select-model').forEach(btn => btn.addEventListener('click', () => {
  document.querySelector('#model').value = btn.dataset.model;
  document.querySelector('#form').scrollIntoView({behavior:'smooth'});
}));
document.querySelector('#application').addEventListener('submit', async event => {
  event.preventDefault();
  const form = event.currentTarget;
  const notice = document.querySelector('#form-message');
  if (location.hostname.endsWith('github.io')) {
    notice.textContent = 'Это демонстрация. Настоящая запись заработает на REG.RU после подключения PHP и MySQL.';
    return;
  }
  const submit = form.querySelector('button[type=submit]');
  const payload = {
    model: form.elements.model.value,
    name: form.elements.name.value.trim(),
    phone: form.elements.phone.value.trim(),
    email: form.elements.email.value.trim(),
    consent: form.querySelector('.consent input').checked
  };
  submit.disabled = true;
  notice.textContent = 'Сохраняем заявку…';
  try {
    const response = await fetch('api/register.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(payload)
    });
    const data = await response.json();
    if (!response.ok) throw new Error(data.error || 'Ошибка регистрации');
    location.assign('queue.html?token=' + encodeURIComponent(data.token));
  } catch (error) {
    notice.textContent = error.message || 'Регистрация временно недоступна.';
    submit.disabled = false;
  }
});

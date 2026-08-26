(() => {
  const searchButton = document.querySelector('.sgd-search-toggle');
  const search = document.querySelector('#sgd-search');
  const menuButton = document.querySelector('.sgd-menu-toggle');
  const menu = document.querySelector('#sgd-nav');
  const setOpen = (button, panel, open) => {
    if (!button || !panel) return;
    button.setAttribute('aria-expanded', String(open));
    panel.classList.toggle('is-open', open);
    if (panel.id === 'sgd-search') panel.setAttribute('aria-hidden', String(!open));
  };
  searchButton?.addEventListener('click', () => {
    const open = searchButton.getAttribute('aria-expanded') !== 'true';
    setOpen(searchButton, search, open);
    if (open) search.querySelector('input')?.focus();
  });
  menuButton?.addEventListener('click', () => setOpen(menuButton, menu, menuButton.getAttribute('aria-expanded') !== 'true'));
  document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;
    setOpen(searchButton, search, false); setOpen(menuButton, menu, false);
  });
})();

(() => {
  const body=document.body, menuButton=document.querySelector('.sgd-menu-toggle'), closeButton=document.querySelector('.sgd-overlay-close'), overlay=document.querySelector('#sgd-overlay'), searchButton=document.querySelector('.sgd-search-toggle'), search=document.querySelector('#sgd-mobile-search');
  const openMenu=open=>{if(!menuButton||!overlay)return;menuButton.setAttribute('aria-expanded',String(open));overlay.setAttribute('aria-hidden',String(!open));overlay.classList.toggle('is-open',open);body.classList.toggle('sgd-menu-open',open);if(open)closeButton?.focus();else menuButton.focus();};
  const openSearch=open=>{if(!searchButton||!search)return;searchButton.setAttribute('aria-expanded',String(open));search.setAttribute('aria-hidden',String(!open));search.classList.toggle('is-open',open);if(open)search.querySelector('input')?.focus();};
  menuButton?.addEventListener('click',()=>openMenu(menuButton.getAttribute('aria-expanded')!=='true'));
  closeButton?.addEventListener('click',()=>openMenu(false));
  searchButton?.addEventListener('click',()=>openSearch(searchButton.getAttribute('aria-expanded')!=='true'));
  overlay?.addEventListener('click',event=>{if(event.target===overlay)openMenu(false);});
  document.addEventListener('keydown',event=>{if(event.key==='Escape'){openMenu(false);openSearch(false);}});
})();

export function bindSiteNavigation(root = document) {
  root.querySelectorAll('[data-site-menu-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const nav = button.closest('[data-site-navbar]');
      const open = nav.classList.toggle('menu-open');
      button.setAttribute('aria-expanded', String(open));
    });
  });

  root.querySelectorAll('[data-scroll-block]').forEach((link) => {
    link.addEventListener('click', (event) => {
      const target = root.querySelector(`[data-block-id="${link.dataset.scrollBlock}"]`);
      if (!target) return;

      event.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      closeSiteMenus(root);
    });
  });
}

export function closeSiteMenus(root = document) {
  root.querySelectorAll('[data-site-navbar].menu-open').forEach((nav) => {
    nav.classList.remove('menu-open');
    nav.querySelector('[data-site-menu-toggle]')?.setAttribute('aria-expanded', 'false');
  });
}
